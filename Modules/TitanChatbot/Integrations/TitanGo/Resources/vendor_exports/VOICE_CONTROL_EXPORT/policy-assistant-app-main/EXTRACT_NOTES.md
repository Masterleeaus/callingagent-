# Extract notes (policy-assistant-app)

What you have here:
- `public/js/AudioRecorder/*`: a complete, browser-based **microphone recorder** implementation using Web Audio + WebAudioRecorder.
- Supports encoding to MP3/OGG/WAV (encoders included).

Why it’s useful for WorkSuite voice control:
- If you want higher-quality / longer STT than built-in `SpeechRecognition`, you can record audio and send it to your backend (then run Whisper/other STT inside your AI layer).

Integration idea:
- Use this to implement “hold-to-record” or “record message” UX, then POST the blob to a WorkSuite module endpoint.
