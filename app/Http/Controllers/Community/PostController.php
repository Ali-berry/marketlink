<?php

namespace App\Http\Controllers\Community;

use App\Enums\CommunityPostStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Community\StorePostRequest;
use App\Models\CommunityPost;
use App\Models\CommunityPostLike;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // koi bhi logged in user post kar sakta hai, post pending se shuru hoti hai
    public function store(StorePostRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        CommunityPost::create([
            'author_id' => $request->user()->id,
            'body' => $validated['body'],
            'image_path' => $request->hasFile('image') ? $request->file('image')->store('community-posts', 'public') : null,
            'status' => CommunityPostStatus::Pending->value,
        ]);

        return redirect()->route('community.index')
            ->with('success', 'Thanks for posting. Your post will show in the feed once an admin approves it.');
    }

    // already liked post pe dobara click = unlike
    public function toggleLike(Request $request, CommunityPost $post): RedirectResponse
    {
        abort_unless($post->isApproved(), 404);

        $viewer = $request->user();

        $existingLike = CommunityPostLike::where('post_id', $post->id)
            ->where('user_id', $viewer->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            CommunityPostLike::create(['post_id' => $post->id, 'user_id' => $viewer->id]);
        }

        return back();
    }
}
