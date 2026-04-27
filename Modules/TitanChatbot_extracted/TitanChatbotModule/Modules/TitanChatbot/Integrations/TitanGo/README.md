# TitanGo (Titan Pro Module)

Voice control add-on for Titan Pro. TitanGo captures voice (browser STT), previews an action, requires confirmation, and dispatches a structured action request to Titan Zero.

## Key rules
- TitanGo contains **no AI decision logic**.
- TitanGo never calls providers directly.
- TitanGo only dispatches **known action keys** to Titan Zero.

## Configure
Add to `.env`:

- `TITANGO_FORCE_ENABLED=true` (dev only)
- `TITANGO_TITANZERO_ACTION_URL=https://your-domain/dashboard/user/titanzero/actions/dispatch`
- `TITANGO_SPEECH_LANG=en-AU`

Enable for a company (MVP):
- `company_settings` row: `key=titango_enabled`, `value=1`

## Routes
- User: `/dashboard/user/titango/voice`
- Admin: `/dashboard/admin/settings/titango`


## Assets
Run `php artisan module:publish TitanGo` to publish `Resources/assets` to public so `module_asset('titango:js/titango.js')` works.
