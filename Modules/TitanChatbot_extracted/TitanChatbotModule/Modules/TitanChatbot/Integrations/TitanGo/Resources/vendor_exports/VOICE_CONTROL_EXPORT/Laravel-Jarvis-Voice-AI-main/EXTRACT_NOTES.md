# Extract notes (Laravel-Jarvis-Voice-AI)

What you have here:
- `resources/views/tasks.blade.php`: contains browser-based:
  - **Speech-to-text** via `SpeechRecognition / webkitSpeechRecognition`
  - **Text-to-speech** via `SpeechSynthesisUtterance`
  - A mic button UI and an async `fetch()` call (`route('ai.command')`) as the backend command handler.

Why it’s useful for WorkSuite voice control:
- A minimal, clean UI flow: Click mic → get transcript → send to backend → speak response.

Integration idea:
- Extract the JS block into a WorkSuite Blade partial (e.g. `resources/views/partials/voice_control.blade.php`).
- Replace the backend target with your WorkSuite module route.
