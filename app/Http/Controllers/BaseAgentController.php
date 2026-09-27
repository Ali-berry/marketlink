<?php

namespace App\Http\Controllers;

use App\Enums\AgentMessageRole;
use App\Enums\UserRole;
use App\Helpers\MarkdownStripper;
use App\Models\AgentConversation;
use App\Models\User;
use App\Services\Agent\GroqClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Groq se baat aur tool-call loop sirf yahan. Customer, farmer aur admin agents ki shape same hai,
// sirf tools aur prompt alag
abstract class BaseAgentController extends Controller
{
    // confused model tools hi call karta na reh jaye
    private const MAX_TOOL_ROUNDS = 5;

    public function __construct(private readonly GroqClient $groq)
    {
    }

    abstract protected function userType(): UserRole;

    abstract protected function toolSchemasFor(User $actor): array;

    abstract protected function executeTool(User $actor, string $toolName, array $arguments): array;

    abstract protected function systemPrompt(User $actor): string;

    // sirf customer agent override karta hai - farmer / admin ke actions mein guess karna risky hai
    protected function attemptEmergencyFallback(User $actor, string $userMessage): ?string
    {
        return null;
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate(['message' => ['required', 'string', 'max:2000']]);
        $actor = $request->user();
        $userType = $this->userType();

        AgentConversation::create([
            'user_id' => $actor->id,
            'user_type' => $userType->value,
            'role' => AgentMessageRole::User->value,
            'content' => $validated['message'],
        ]);

        $conversationHistory = AgentConversation::forUser($actor, $userType)->orderBy('created_at')->get();

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt($actor)],
            ...$conversationHistory->map(fn (AgentConversation $turn) => [
                'role' => $turn->role->value,
                'content' => $turn->content,
            ])->all(),
        ];

        try {
            $reply = $this->converseUntilFinalReply($messages, $actor);
        } catch (\Throwable $exception) {
            report($exception);

            // dono Groq keys fail - generic message se pehle aakhri koshish, sirf customer ka simple order parser
            $reply = $this->attemptEmergencyFallback($actor, $validated['message'])
                ?? "Sorry, I'm having a technical problem right now. Please try again in a moment.";
        }

        // prompt mein Markdown mana hai phir bhi model kabhi bhej deta hai, save / show se pehle saaf karo
        $reply = MarkdownStripper::strip($reply);

        AgentConversation::create([
            'user_id' => $actor->id,
            'user_type' => $userType->value,
            'role' => AgentMessageRole::Assistant->value,
            'content' => $reply,
        ]);

        return response()->json(['reply' => $reply]);
    }

    public function history(Request $request): JsonResponse
    {
        $actor = $request->user();

        $messages = AgentConversation::forUser($actor, $this->userType())
            ->orderBy('created_at')
            ->get(['role', 'content'])
            ->map(fn (AgentConversation $turn) => ['role' => $turn->role->value, 'content' => $turn->content]);

        return response()->json(['messages' => $messages]);
    }

    // Groq ko tool results dete raho jab tak plain text reply na aaye (ya round limit)
    private function converseUntilFinalReply(array $messages, User $actor): string
    {
        $toolSchemas = $this->toolSchemasFor($actor);

        for ($round = 0; $round < self::MAX_TOOL_ROUNDS; $round++) {
            try {
                $response = $this->groq->chat($messages, $toolSchemas);
            } catch (RequestException $exception) {
                // galat tool arguments pe Groq poori request 400 se fail karta hai - model ko bata ke isi round
                // dobara try karwao, poori baat chhodne ki zaroorat nahi
                if ($exception->response->status() === 400) {
                    $messages[] = [
                        'role' => 'user',
                        'content' => 'That last tool call had invalid or missing parameters. Check the required fields and their types, then try again.',
                    ];

                    continue;
                }

                throw $exception;
            }

            $assistantMessage = $response['choices'][0]['message'] ?? [];

            if (empty($assistantMessage['tool_calls'])) {
                return trim((string) ($assistantMessage['content'] ?? '')) ?: 'Sorry, I\'m not sure how to answer that.';
            }

            $messages[] = $assistantMessage;

            foreach ($assistantMessage['tool_calls'] as $toolCall) {
                $toolName = $toolCall['function']['name'];
                $arguments = json_decode($toolCall['function']['arguments'] ?? '{}', true) ?? [];

                $result = $this->executeTool($actor, $toolName, $arguments);

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall['id'],
                    'content' => json_encode($result),
                ];
            }
        }

        return 'Sorry, this is taking a bit longer than expected - could you try rephrasing that?';
    }

    // teeno agents ke tone / formatting rules ek hi jagah
    protected function commonAgentRules(): string
    {
        return <<<'PROMPT'
            - You're replying inside a plain-text chat bubble, not a Markdown renderer - it shows literal
              asterisks and hash signs as characters, not formatting. Never write **bold**, *italics*, # headings
              or Markdown tables. Plain sentences and simple "- " bullet points only.
            - Never show raw database ids to the user - use names, order numbers or short descriptions instead.
            - Reply in the language of the user's latest message - English if they wrote English, Roman Urdu if they
              wrote Roman Urdu. Older messages in the chat don't decide this, only the newest one.
            - All prices are in US dollars. Always write them with $ exactly as the tool gives them (e.g. $2.50). Never
              use ₹, Rs, PKR or any other currency, even when replying in Roman Urdu.
            - Keep replies short, natural and human - not robotic or overly formal.
            - Only do things within your own scope. If asked to do something another agent handles, say so plainly instead of guessing.
            - Before calling any tool that changes data (placing or cancelling an order, sending a message, adding a favourite,
              changing stock or availability, accepting/declining/readying/completing an order, approving or suspending a farmer,
              hiding or unhiding content, approving/rejecting/pinning/unpinning/deleting a community post), first show a short
              summary of exactly what you are about to do and ask "Should I go ahead?". Only call the tool after the user clearly
              says yes (for example "yes", "haan", "go ahead", "confirm"). Anything else means don't do it. Tools that only
              read data never need this - call them straight away.
            - If a tool result has "options" (more than one thing matched a name), list those options and ask the user which
              one they mean. Never pick one yourself.
            - If a tool says a reason is required (declining an order, suspending a farmer, hiding content, rejecting a post),
              ask the user for the reason, then show the summary and ask "Should I go ahead?" again.
            PROMPT;
    }
}
