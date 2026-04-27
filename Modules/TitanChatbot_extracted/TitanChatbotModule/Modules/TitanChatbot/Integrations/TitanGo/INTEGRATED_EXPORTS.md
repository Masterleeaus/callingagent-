# Integrated GitHub Voice Control Exports

This module includes a `Resources/vendor_exports/VOICE_CONTROL_EXPORT/` folder containing the original code exports you provided.

Titan Go uses the following *patterns* (not AI logic) from these exports:

- Browser SpeechRecognition (STT) + browser speechSynthesis (TTS) UX pattern.
- Deterministic phrase -> action mapping pattern.
- Optional server-side TTS stub (placeholder, replace with real provider in Pass 4).

Titan Go intentionally does **not** include comms, inboxes, campaigns, IVR, or provider-calling AI logic.
All decisions and executions must be delegated to Titan Zero via `TITANGO_TITANZERO_ACTION_URL`.
