# Extract notes (church-information-system)

What you have here:
- `public/assets/pages/js/speechsynthesis/*.js`: multiple page-specific scripts using browser speech synthesis (and some recognition) to read out content.

Why it’s useful for WorkSuite voice control:
- Examples of speaking longer, structured content and binding speech controls to UI buttons.

Integration idea:
- Lift common helper functions (voice init, start/stop, pause/resume) into a shared WorkSuite JS utility (e.g. `titanvoice.js`).
