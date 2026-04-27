# Pass 12 Extraction Report

This pass preserves the full existing CallingAgent module and all builder/UI files, then adds the next core layer:

- Caller profile memory and cross-call recall
- Conversation embedding memory store
- Structured outcome extraction pipeline
- Reception persona resolver and persona library
- Transfer decision tree and routing service
- Missed-call recovery pipeline
- Calendar provider federation: Google, Outlook, CalDAV
- SIP bridge contracts/service
- Provider failover manager
- Full-duplex realtime helpers: barge-in detection, silence heuristics, response pre-roll cache
- Calling Agent Builder UI page, Blade layout, JS, CSS, Filament page, API preview controller
- Intelligence, builder, UI, routes, database, automation, and workflow manifests updated

UI preservation notes:

- Existing `Resources/views`, `Resources/assets`, `Filament`, and legacy builder/source UI paths were not deleted.
- New builder files were added under `Resources/views/builder`, `Resources/views/layouts`, `Resources/assets/js`, `Resources/assets/css`, and `Filament/Pages`.
