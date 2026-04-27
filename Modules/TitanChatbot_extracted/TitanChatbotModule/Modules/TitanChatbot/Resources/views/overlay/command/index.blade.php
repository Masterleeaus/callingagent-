@extends('chatbot::overlay.layout', ['title' => 'Titan Command'])
@section('content')
<div class="card">
    <strong>Workspace</strong>
    <div class="muted">Workspace/tenant key: {{ $workspaceId ?? 'n/a' }}</div>
</div>
<div class="grid">
    <div class="card"><div class="label">Chatbots</div><div class="kpi">{{ $chatbots->total() }}</div></div>
    <div class="card"><div class="label">Channels Available</div><div class="kpi">{{ $availableChannels->count() }}</div></div>
    <div class="card"><div class="label">Open Conversations</div><div class="kpi">{{ $chatbots->sum('open_conversations_count') }}</div></div>
    <div class="card"><div class="label">Assigned Queue</div><div class="kpi">{{ $chatbots->sum('assigned_conversations_count') }}</div></div>
</div>
<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th><th>Title</th><th>Total</th><th>Open</th><th>Assigned</th><th>Unassigned</th><th>Channels</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($chatbots as $chatbot)
                <tr>
                    <td>{{ $chatbot->id }}</td>
                    <td>
                        <strong>{{ $chatbot->title }}</strong>
                        <div class="muted">Workspace {{ $chatbot->workspaceKey() ?? 'n/a' }}</div>
                    </td>
                    <td>{{ $chatbot->conversations_count }}</td>
                    <td>{{ $chatbot->open_conversations_count }}</td>
                    <td>{{ $chatbot->assigned_conversations_count }}</td>
                    <td>{{ $chatbot->unassigned_conversations_count }}</td>
                    <td>{{ $chatbot->channels_count }}</td>
                    <td class="actions">
                        <a class="btn alt" href="{{ route('dashboard.chatbot.overlay.command.show', $chatbot) }}">Open</a>
                        <a class="btn alt" href="{{ route('dashboard.chatbot.overlay.command.analytics', $chatbot) }}">Analytics</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">No chatbots found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:16px">{{ $chatbots->links() }}</div>
</div>
@endsection
