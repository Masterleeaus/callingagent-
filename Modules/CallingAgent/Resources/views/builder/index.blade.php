@extends('calling-agent::layouts.builder')

@section('content')
<div class="calling-agent-builder" data-calling-agent-builder data-builder-save-url="{{ route('calling-agent.builder.save', ['agent' => '__AGENT_ID__']) }}" data-builder-load-url="{{ route('calling-agent.builder.load', ['agent' => '__AGENT_ID__']) }}" data-builder-assign-url="{{ route('calling-agent.builder.assign-number', ['agent' => '__AGENT_ID__']) }}">

    {{-- Agent selector bar --}}
    <div class="calling-agent-builder__agent-bar" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:0.75rem 1.5rem;display:flex;align-items:center;gap:1rem;">
        <label for="ca-agent-select" style="font-weight:600;font-size:.875rem;">Agent:</label>
        <select id="ca-agent-select" data-builder-agent-select style="padding:.375rem .75rem;border:1px solid #cbd5e1;border-radius:.375rem;font-size:.875rem;">
            <option value="">— select agent —</option>
            @foreach($agents ?? [] as $agent)
                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
            @endforeach
        </select>
        <span id="ca-save-status" style="font-size:.8rem;color:#64748b;margin-left:auto;"></span>
    </div>

    <div style="display:flex;height:calc(100vh - 100px);">
    <aside class="calling-agent-builder__sidebar" style="width:200px;background:#1e293b;padding:1rem;flex-shrink:0;">
        <h2 style="color:#f1f5f9;font-size:1rem;font-weight:700;margin-bottom:1rem;">Builder</h2>
        <nav style="display:flex;flex-direction:column;gap:.25rem;">
            <button data-builder-tab="persona" class="ca-tab-btn ca-tab-active">Persona</button>
            <button data-builder-tab="channels" class="ca-tab-btn">Channels</button>
            <button data-builder-tab="routing" class="ca-tab-btn">Routing</button>
            <button data-builder-tab="calendar" class="ca-tab-btn">Calendar</button>
            <button data-builder-tab="aftercare" class="ca-tab-btn">Aftercare</button>
        </nav>
        <div style="margin-top:2rem;">
            <button id="ca-save-btn" type="button" style="width:100%;background:#3b82f6;color:#fff;border:none;border-radius:.375rem;padding:.5rem;cursor:pointer;font-size:.875rem;font-weight:600;">Save</button>
        </div>
        <div style="margin-top:.5rem;">
            <button id="ca-test-call-btn" type="button" style="width:100%;background:#059669;color:#fff;border:none;border-radius:.375rem;padding:.5rem;cursor:pointer;font-size:.875rem;font-weight:600;" title="Place a test outbound call to verify the agent">☎ Test Call</button>
        </div>
    </aside>

    <main class="calling-agent-builder__canvas" style="flex:1;overflow-y:auto;padding:1.5rem;">

        {{-- ── Persona Panel ── --}}
        <section data-builder-panel="persona">
            <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:1rem;">Reception Persona</h3>
            <div style="display:grid;gap:1rem;max-width:600px;">
                <div>
                    <label class="ca-label">Agent Name</label>
                    <input type="text" name="name" data-builder-field="name" class="ca-input" placeholder="e.g. Front Desk AI" />
                </div>
                <div>
                    <label class="ca-label">Industry Persona</label>
                    <select name="settings[persona]" data-builder-field="settings.persona" class="ca-input">
                        <option value="">— select —</option>
                        <option value="front_desk">Front Desk</option>
                        <option value="clinic">Clinic / Medical</option>
                        <option value="legal">Legal</option>
                        <option value="hotel">Hotel</option>
                        <option value="saas">SaaS / Tech</option>
                    </select>
                </div>
                <div>
                    <label class="ca-label">First Message / Greeting</label>
                    <textarea name="first_message" data-builder-field="first_message" rows="3" class="ca-input" placeholder="Hello, you have reached..."></textarea>
                </div>
                <div>
                    <label class="ca-label">System Instructions</label>
                    <textarea name="instructions" data-builder-field="instructions" rows="5" class="ca-input" placeholder="You are a professional receptionist..."></textarea>
                </div>
                <div>
                    <label class="ca-label">Language</label>
                    <select name="language" data-builder-field="language" class="ca-input">
                        <option value="en">English</option>
                        <option value="es">Spanish</option>
                        <option value="fr">French</option>
                        <option value="de">German</option>
                        <option value="pt">Portuguese</option>
                    </select>
                </div>
                <div>
                    <label class="ca-label">Business Hours (JSON)</label>
                    <textarea name="settings[business_hours]" data-builder-field="settings.business_hours" rows="3" class="ca-input" placeholder='{"mon":"09:00-17:00","fri":"09:00-17:00"}'></textarea>
                </div>
            </div>
        </section>

        {{-- ── Channels Panel ── --}}
        <section data-builder-panel="channels" hidden>
            <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:1rem;">Channels</h3>
            <div style="display:grid;gap:1rem;max-width:600px;">
                <div>
                    <label class="ca-label">Assigned Phone Number</label>
                    <div style="display:flex;gap:.5rem;">
                        <input type="text" id="ca-phone-number" class="ca-input" placeholder="+15005550001" style="flex:1;" />
                        <button type="button" id="ca-assign-number-btn" style="background:#10b981;color:#fff;border:none;border-radius:.375rem;padding:.5rem 1rem;cursor:pointer;font-size:.875rem;">Assign</button>
                    </div>
                </div>
                <div>
                    <label class="ca-label">Twilio Webhook URLs (set these in Twilio console)</label>
                    <div id="ca-webhook-urls" style="font-family:monospace;font-size:.8rem;background:#f1f5f9;border-radius:.375rem;padding:.75rem;white-space:pre-line;color:#475569;">Load an agent to see URLs…</div>
                </div>
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <input type="checkbox" name="settings[realtime_bridge]" data-builder-field="settings.realtime_bridge" id="ca-realtime" />
                    <label for="ca-realtime" class="ca-label" style="margin:0;">Enable Real-time ElevenLabs Bridge</label>
                </div>
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <input type="checkbox" name="settings[voicemail_enabled]" data-builder-field="settings.voicemail_enabled" id="ca-voicemail" />
                    <label for="ca-voicemail" class="ca-label" style="margin:0;">Enable Voicemail</label>
                </div>
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <input type="checkbox" name="settings[recording_enabled]" data-builder-field="settings.recording_enabled" id="ca-recording" />
                    <label for="ca-recording" class="ca-label" style="margin:0;">Enable Call Recording</label>
                </div>
                <div>
                    <label class="ca-label">ElevenLabs Agent ID</label>
                    <input type="text" name="elevenlabs_agent_id" data-builder-field="elevenlabs_agent_id" class="ca-input" placeholder="el_agent_..." />
                </div>
                <div>
                    <label class="ca-label">ElevenLabs Voice ID</label>
                    <input type="text" name="elevenlabs_voice_id" data-builder-field="elevenlabs_voice_id" class="ca-input" placeholder="voice_..." />
                </div>
            </div>
        </section>

        {{-- ── Routing Panel ── --}}
        <section data-builder-panel="routing" hidden>
            <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:1rem;">Transfer Routing</h3>
            <div style="display:grid;gap:1rem;max-width:600px;">
                <p style="font-size:.875rem;color:#64748b;">Configure destination phone numbers or SIP URIs for each routing target.</p>
                @foreach(['front_desk' => 'Front Desk', 'booking_team' => 'Booking Team', 'sales_team' => 'Sales Team', 'support_team' => 'Support Team', 'urgent_handoff' => 'Urgent Handoff', 'vip' => 'VIP Route', 'fallback' => 'Fallback'] as $key => $label)
                <div>
                    <label class="ca-label">{{ $label }}</label>
                    <input type="text" name="settings[transfer_routing][routes][{{ $key }}]" data-builder-field="settings.transfer_routing.routes.{{ $key }}" class="ca-input" placeholder="+1555..." />
                </div>
                @endforeach
            </div>
        </section>

        {{-- ── Calendar Panel ── --}}
        <section data-builder-panel="calendar" hidden>
            <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:1rem;">Calendar Federation</h3>
            <div style="display:grid;gap:1rem;max-width:600px;">
                <p style="font-size:.875rem;color:#64748b;">Connect Google, Outlook, or CalDAV availability and booking providers.</p>
                <div>
                    <label class="ca-label">Calendar Provider</label>
                    <select name="settings[calendar_provider]" data-builder-field="settings.calendar_provider" class="ca-input">
                        <option value="">— none —</option>
                        <option value="google">Google Calendar</option>
                        <option value="outlook">Outlook / Microsoft</option>
                        <option value="caldav">CalDAV</option>
                    </select>
                </div>
                <div>
                    <label class="ca-label">Calendar ID / Resource URI</label>
                    <input type="text" name="settings[calendar_id]" data-builder-field="settings.calendar_id" class="ca-input" />
                </div>
            </div>
        </section>

        {{-- ── Aftercare Panel ── --}}
        <section data-builder-panel="aftercare" hidden>
            <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:1rem;">Missed Call Recovery</h3>
            <div style="display:grid;gap:1rem;max-width:600px;">
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <input type="checkbox" name="settings[missed_call_sms]" data-builder-field="settings.missed_call_sms" id="ca-missed-sms" />
                    <label for="ca-missed-sms" class="ca-label" style="margin:0;">Send SMS on Missed Call</label>
                </div>
                <div>
                    <label class="ca-label">Missed Call SMS Template</label>
                    <textarea name="settings[missed_call_sms_template]" data-builder-field="settings.missed_call_sms_template" rows="2" class="ca-input" placeholder="Hi {name}, sorry we missed your call. We'll call back shortly."></textarea>
                </div>
                <div>
                    <label class="ca-label">Recovery Delay (minutes)</label>
                    <input type="number" name="settings[missed_call_delay_minutes]" data-builder-field="settings.missed_call_delay_minutes" class="ca-input" min="1" max="1440" value="5" />
                </div>
            </div>
        </section>

    </main>
    </div>
</div>

<style>
.ca-tab-btn {
    width: 100%; text-align: left; background: transparent; border: none;
    color: #94a3b8; padding: .4rem .75rem; border-radius: .25rem;
    cursor: pointer; font-size: .875rem; transition: background .15s;
}
.ca-tab-btn:hover, .ca-tab-btn.ca-tab-active { background: #334155; color: #f1f5f9; }
.ca-label { display: block; font-size: .8rem; font-weight: 600; color: #475569; margin-bottom: .25rem; }
.ca-input {
    width: 100%; padding: .5rem .75rem; border: 1px solid #cbd5e1; border-radius: .375rem;
    font-size: .875rem; background: #fff; box-sizing: border-box;
}
.ca-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,.2); }
</style>
@endsection
