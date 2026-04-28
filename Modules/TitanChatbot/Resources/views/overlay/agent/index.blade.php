@extends('chatbot::overlay.layout', ['title' => 'Agent Inbox'])
@section('content')
<div class="card">
    <form method="get" class="row3">
        <input class="input" type="text" name="search" placeholder="Search customer, phone, or subject" value="{{ $filters['search'] ?? '' }}">
        <select class="select" name="channel">
            <option value="">All channels</option>
            @foreach($channels as $channel)
                <option value="{{ $channel }}" @selected(($filters['channel'] ?? null) === $channel)>{{ ucfirst($channel) }}</option>
            @endforeach
        </select>
        <div class="actions">
            <label><input type="checkbox" name="mine" value="1" @checked($filters['mine'] ?? false)> Mine</label>
            <label><input type="checkbox" name="unassigned" value="1" @checked($filters['unassigned'] ?? false)> Unassigned</label>
            <label><input type="checkbox" name="closed" value="1" @checked($filters['closed'] ?? false)> Closed</label>
            <button class="btn" type="submit">Filter</button>
        </div>
    </form>
</div>
<div class="card">
    <table class="table">
        <thead><tr><th>ID</th><th>Conversation</th><th>Last Message</th><th>Assigned</th><th>Status</th><th>Open</th></tr></thead>
        <tbody>
            @forelse($conversations as $conversation)
                <tr>
                    <td>{{ $conversation->id }}</td>
                    <td>
                        <strong>{{ $conversation->customer?->name ?? $conversation->conversation_name ?? '—' }}</strong>
                        <div class="muted">{{ $conversation->chatbot?->title }} · {{ ucfirst($conversation->chatbot_channel ?? 'web') }}</div>
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($conversation->lastMessage?->message ?? '—', 80) }}</td>
                    <td>{{ $conversation->assignedAgent?->name ?? 'Unassigned' }}</td>
                    <td>{{ $conversation->statusLabel() }}</td>
                    <td><a class="btn alt" href="{{ route('dashboard.chatbot.overlay.agent.show', $conversation) }}">View</a></td>
                </tr>
            @empty
                <tr><td colspan="6">No conversations match these filters.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:16px">{{ $conversations->links() }}</div>
</div>
@endsection
