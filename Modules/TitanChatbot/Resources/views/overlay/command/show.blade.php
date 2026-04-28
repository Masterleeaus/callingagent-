@extends('chatbot::overlay.layout', ['title' => $chatbot->title])
@section('content')
<div class="grid">
    <div class="card"><div class="label">Total Conversations</div><div class="kpi">{{ $snapshot['total_conversations'] }}</div></div>
    <div class="card"><div class="label">Open</div><div class="kpi">{{ $snapshot['open_conversations'] }}</div></div>
    <div class="card"><div class="label">Assigned</div><div class="kpi">{{ $snapshot['assigned_conversations'] }}</div></div>
    <div class="card"><div class="label">Unassigned</div><div class="kpi">{{ $snapshot['unassigned_conversations'] }}</div></div>
    <div class="card"><div class="label">Channels</div><div class="kpi">{{ $snapshot['channel_count'] }}</div></div>
    <div class="card"><div class="label">Avg First Response</div><div class="kpi">{{ $snapshot['avg_first_response_seconds'] ? $snapshot['avg_first_response_seconds'].'s' : '—' }}</div></div>
</div>

<div class="card">
    <strong>Channel configuration</strong>
    <div class="muted">Save credentials or metadata per channel and fetch inbound webhook URLs for each bridge.</div>
</div>
@include('chatbot::overlay.command.partials.channel-cards', ['chatbot' => $chatbot, 'availableChannels' => $availableChannels])

<div class="card">
    <strong>Recent conversations</strong>
    <table class="table">
        <thead><tr><th>ID</th><th>Channel</th><th>Customer</th><th>Assigned</th><th>Status</th><th>Last Activity</th></tr></thead>
        <tbody>
        @forelse($conversations as $conversation)
            <tr>
                <td>{{ $conversation->id }}</td>
                <td><span class="badge">{{ ucfirst($conversation->chatbot_channel ?? optional($conversation->chatbotChannel)->channel ?? 'web') }}</span></td>
                <td>{{ $conversation->customer?->name ?? $conversation->conversation_name ?? '—' }}</td>
                <td>{{ $conversation->assignedAgent?->name ?? 'Unassigned' }}</td>
                <td>{{ $conversation->statusLabel() }}</td>
                <td>{{ optional($conversation->last_activity_at)?->diffForHumans() ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="6">No conversations yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:16px">{{ $conversations->links() }}</div>
</div>
@endsection
