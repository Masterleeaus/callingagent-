# Extract notes (library-voice-laravel)

What you have here:
- `public/js/voice.js`: a large set of **voice command → UI action** mappings using the `annyang` speech-recognition library.
- `resources/views/layouts/app.blade.php`: includes `js/voice.js`.

Why it’s useful for WorkSuite voice control:
- This is a real example of “voice command grammar” driving app navigation and form-filling.

Caveats:
- Assumes specific DOM IDs (e.g. `checkadmin`, `title`, `author`, etc.) and hard-coded paths.

Integration idea:
- Treat `voice.js` as a pattern:
  1) define a WorkSuite-specific command list (e.g. “create quote”, “open jobs”, “search customers <name>”)
  2) dispatch commands to a single JS router that either navigates, clicks UI buttons, or calls your WorkSuite module endpoint for AI parsing.
