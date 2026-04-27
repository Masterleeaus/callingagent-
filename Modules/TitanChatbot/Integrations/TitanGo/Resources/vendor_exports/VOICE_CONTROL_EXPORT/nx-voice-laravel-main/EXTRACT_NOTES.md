# Extract notes (nx-voice-laravel)

What you have here:
- `app/Services/VoiceService.php`: Laravel service wrapping an external voice/IVR API:
  - `createIvrCall()`
  - `createTextToVoiceCall()`
- `config/voice.php`: env-backed config (base URL, API key, voice_id, vid).
- `app/Models/IvrCallHistory.php` + `ivr_call_history.sql`: call-history persistence.
- `app/Console/Commands/FetchVoiceSummary.php`: example polling job to fetch call results by `trans_id` (needs cleanup).

Why it’s useful for WorkSuite voice control:
- It’s the “voice calling” side (dial-out/IVR) pattern: call initiation + call history + later status reconciliation.

Caveats:
- `FetchVoiceSummary` references models/classes (`Order`, `ProductSale`) that are not included here; treat it as a template.

Integration idea:
- In your WorkSuite module, keep the API wrapper in `System/Services/VoiceCallingService`.
- Map `ivr_call_history` into a module-prefixed table (e.g. `titanvoice_ivr_call_history`).
- Wire status polling into scheduler/queue and surface results in a dashboard widget.
