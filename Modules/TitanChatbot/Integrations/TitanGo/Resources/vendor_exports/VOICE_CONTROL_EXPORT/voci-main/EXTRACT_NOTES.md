# Extract notes (voci)

What you have here:
- `public/js/text_to_speech.js`: TTS via `speechSynthesis` with configurable rate/voice.
- `public/js/stop_speech.js`: stop/cancel speaking.
- `public/js/controllers/*_speech_controller.js`: speech recognition listeners via `webkitSpeechRecognition` and basic command handling.
- `resources/views/study/prepare.blade.php`: populates an **available voices** dropdown using `speechSynthesis.getVoices()`.
- `app/Http/Controllers/StudyController.php`: session flags for “voice mode”, voice rate/style.

Why it’s useful for WorkSuite voice control:
- Shows how to offer a “select voice / rate” UI and persist it.

Integration idea:
- Reuse the voice dropdown + rate pattern as user preferences for “Titan Go / Voice Control”.
