@extends('chatbot::overlay.layout', ['title' => $chatbot->title . ' Analytics'])
@section('content')
<div class="grid">
    <div class="card"><div class="label">Open</div><div class="kpi">{{ $snapshot['open_conversations'] }}</div></div>
    <div class="card"><div class="label">Closed</div><div class="kpi">{{ $snapshot['closed_conversations'] }}</div></div>
    <div class="card"><div class="label">Page Visits</div><div class="kpi">{{ $snapshot['page_visits'] }}</div></div>
    <div class="card"><div class="label">Voice Channels</div><div class="kpi">{{ $snapshot['voice_channels'] }}</div></div>
</div>
<div class="card">
    <strong>Analytics payload</strong>
    <pre style="white-space:pre-wrap">{{ json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
</div>
@endsection
