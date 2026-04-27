# Extract notes (tts-laravel-voicerss)

What you have here:
- `app/Http/Controllers/TTSController.php`: server-side Text-to-Speech via the VoiceRSS API.
- `routes/web.php`: example routes (`POST /speak`).
- `resources/views/welcome.blade.php`: simple form + audio player.
- `.env.example`: shows `VOICERSS_KEY`, `VOICERSS_LANG`, etc.

Why it’s useful for a WorkSuite voice module:
- Gives you a clean Laravel pattern for generating and caching MP3 files (by hash) and returning a public URL.

Integration idea:
- In WorkSuite, extract controller logic into a service class (e.g. `VoiceTtsService`) and expose it via your module routes.
- Swap VoiceRSS for Titan Zero/Titan AI later if desired—this controller is a good “shape” for the implementation.
