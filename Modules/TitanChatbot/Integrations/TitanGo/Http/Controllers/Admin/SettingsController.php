<?php

namespace Modules\TitanGo\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('titango::admin.settings', [
            'speechLang' => config('titango.speech_lang', 'en-AU'),
        ]);
    }

    public function update(Request $request)
    {
        // Placeholder: in MVP we just tell user to set env vars / company_settings.
        return redirect()->back()->with('status', 'Titan Go settings saved. (MVP: configure TITANGO_* env vars and company_settings titango_enabled=1)');
    }
}
