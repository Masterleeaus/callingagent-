@extends('calling-agent::layouts.builder')

@section('content')
<div class="calling-agent-builder" data-calling-agent-builder>
    <aside class="calling-agent-builder__sidebar">
        <h2>Calling Agent Builder</h2>
        <button data-builder-tab="persona">Persona</button>
        <button data-builder-tab="channels">Channels</button>
        <button data-builder-tab="routing">Routing</button>
        <button data-builder-tab="calendar">Calendar</button>
        <button data-builder-tab="aftercare">Aftercare</button>
    </aside>
    <main class="calling-agent-builder__canvas">
        <section data-builder-panel="persona">
            <h3>Reception Persona</h3>
            <p>Configure industry tone, first message, compliance rules, and escalation style.</p>
        </section>
        <section data-builder-panel="channels" hidden>
            <h3>Channels</h3>
            <p>Enable Twilio Voice, SMS, WhatsApp, Messenger, Telegram, and web voice widgets.</p>
        </section>
        <section data-builder-panel="routing" hidden>
            <h3>Transfer Routing</h3>
            <p>Build front desk, sales, support, booking, VIP, and urgent handoff decision paths.</p>
        </section>
        <section data-builder-panel="calendar" hidden>
            <h3>Calendar Federation</h3>
            <p>Connect Google, Outlook, or CalDAV availability and booking providers.</p>
        </section>
        <section data-builder-panel="aftercare" hidden>
            <h3>Missed Call Recovery</h3>
            <p>Automate missed-call SMS, callback scheduling, voicemail summaries, and follow-up tasks.</p>
        </section>
    </main>
</div>
@endsection
