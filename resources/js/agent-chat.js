// floating AI chat widget - page pe teeno mein se ek hi hota hai (panel.blade.php), jo hai usay wire karta hai
document.querySelectorAll('[data-agent-widget]').forEach((widget) => {
    const chatUrl = widget.dataset.chatUrl;
    const historyUrl = widget.dataset.historyUrl;
    const messageList = widget.querySelector('[data-agent-message-list]');
    const emptyHint = widget.querySelector('[data-agent-empty-hint]');
    const form = widget.querySelector('[data-agent-form]');
    const input = widget.querySelector('[data-agent-input]');
    const micButton = widget.querySelector('[data-agent-mic-button]');
    const voiceToggle = widget.querySelector('[data-agent-voice-toggle]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // default off - koi on kare tab hi assistant bolta hai
    let voiceOutputOn = false;

    function appendBubble(role, content) {
        emptyHint?.remove();

        const row = document.createElement('div');
        row.className = role === 'user' ? 'flex justify-end' : 'flex justify-start';

        const bubble = document.createElement('div');
        bubble.className = [
            'max-w-[85%] whitespace-pre-line rounded-2xl px-3.5 py-2 text-sm',
            role === 'user' ? 'bg-leaf-500 text-white' : 'bg-cream text-soil',
        ].join(' ');
        bubble.textContent = content;

        row.appendChild(bubble);
        messageList.appendChild(row);
        messageList.scrollTop = messageList.scrollHeight;

        return bubble;
    }

    function speak(text) {
        if (! voiceOutputOn || typeof speechSynthesis === 'undefined') return;

        // speech engines emoji ko ajeeb awaaz bana dete hain, pehle hata do
        const spokenText = text.replace(/[\u{1F300}-\u{1FAFF}\u{2600}-\u{27BF}]/gu, '');
        speechSynthesis.cancel();
        speechSynthesis.speak(new SpeechSynthesisUtterance(spokenText));
    }

    async function loadHistory() {
        try {
            const response = await fetch(historyUrl, { headers: { Accept: 'application/json' } });
            const data = await response.json();
            (data.messages ?? []).forEach((message) => appendBubble(message.role, message.content));
        } catch (error) {
            // history na aaye to khali hi reh jaye, user ko pareshan karne ki zaroorat nahi
        }
    }

    async function sendMessage(messageText) {
        appendBubble('user', messageText);
        const pendingBubble = appendBubble('assistant', 'Typing...');

        try {
            const response = await fetch(chatUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ message: messageText }),
            });

            const data = await response.json();

            // 429 = 20 messages per minute limit (AppServiceProvider) - server ka message dikhao, bol ke mat sunao
            if (response.status === 429) {
                pendingBubble.textContent = data.reply;
                return;
            }

            const reply = data.reply || "Sorry, I didn't catch that.";
            pendingBubble.textContent = reply;
            speak(reply);
        } catch (error) {
            pendingBubble.textContent = "Sorry, I'm having a technical problem right now. Please try again in a moment.";
        } finally {
            messageList.scrollTop = messageList.scrollHeight;
        }
    }

    form?.addEventListener('submit', (event) => {
        event.preventDefault();
        const messageText = input.value.trim();
        if (! messageText) return;

        input.value = '';
        sendMessage(messageText);
    });

    // page ke doosre buttons (jaise "Need it within the hour?") chat ko message likh ke kholte hain - send customer khud dabata hai
    window.addEventListener('open-agent-chat', (event) => {
        input.value = event.detail?.message ?? '';
        setTimeout(() => input.focus(), 50);
    });

    voiceToggle?.addEventListener('click', () => {
        voiceOutputOn = ! voiceOutputOn;
        voiceToggle.querySelector('iconify-icon')?.setAttribute('icon', voiceOutputOn ? 'tabler:volume' : 'tabler:volume-off');
        if (! voiceOutputOn && typeof speechSynthesis !== 'undefined') speechSynthesis.cancel();
    });

    // har tap pe ek poora jumla, hamesha sunta nahi rehta - continuous mode background shor pakar leta aur beech mein kaat deta
    const SpeechRecognitionClass = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (SpeechRecognitionClass && micButton) {
        micButton.addEventListener('click', () => {
            // har tap pe naya instance - purana reuse karne se pehli baar ke baad kaam band ho jata hai
            const recognizer = new SpeechRecognitionClass();
            recognizer.continuous = false;
            recognizer.interimResults = false;

            micButton.classList.add('bg-tomato-500', 'text-white');

            recognizer.addEventListener('result', (event) => {
                const transcript = event.results[0][0].transcript;
                input.value = transcript;
                sendMessage(transcript);
            });

            recognizer.addEventListener('end', () => micButton.classList.remove('bg-tomato-500', 'text-white'));
            recognizer.addEventListener('error', () => micButton.classList.remove('bg-tomato-500', 'text-white'));

            recognizer.start();
        });
    } else if (micButton) {
        micButton.disabled = true;
        micButton.title = "Your browser can't do voice input";
    }

    loadHistory();
});
