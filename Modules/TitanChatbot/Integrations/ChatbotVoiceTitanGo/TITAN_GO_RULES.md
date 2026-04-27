# Titan Go Developer Rules (Canonical)

Titan Go is the **voice IO + session layer**. Titan Zero is the **only brain**.

## Titan Go MUST
- Accept **voice (optional)**, **text (always)**, and **buttons**.
- Forward user input to Titan Zero **verbatim**:
  - `POST /api/v1/titan-zero/input`
  - Always include: `session_id`, `source: "titan-go"`, `mode`, `input_type`, `text`
  - Include `button_id` only when `input_type="button"`
- Render Titan Zero output **verbatim**:
  - Display + speak `messages[].text` **in order**
  - No paraphrase, no shortening, no merging
- Manage sessions + silence:
  - After X silence → ask: “Anything else you need?”
  - After Y silence → “Okay, I’ll stop listening.” + show “Tap to resume”
  - Timers must be configurable and context-aware

## Titan Go MUST NEVER
- Infer intent
- Decide confirmation
- Execute actions
- Interpret risk (only display UX affordances)
- Block text input
- Force microphone permission
- Trap users in voice or Go Mode
- Block Pro navigation

## Buttons (Hybrid Restricted)
- Titan Go renders a fixed set:
  - `YES`, `NO`, `REPEAT`, `CANCEL`, `OPEN_PRO`
- Titan Zero controls which are valid per state
- Titan Go must disable/hide invalid buttons when Titan Zero provides hints
- Button presses are explicit signals, not free-text confirmations

## Modes
- `mode` is UI-derived (`go` or `pro`)
- `context` defaults to `unknown` in MVP
