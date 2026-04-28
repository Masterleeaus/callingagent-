@extends('chatbot::overlay.layout', ['title' => 'Customer Conversations'])
@section('content')
<div class="row">
    <div class="card">
        <strong>Start a new conversation</strong>
        <form method="post" action="{{ route('dashboard.chatbot.overlay.customer.create') }}" class="stack" style="margin-top:12px">
            @csrf
            <select class="select" name="chatbot_id">
                @foreach($chatbots as $chatbot)
                    <option value="{{ $chatbot->id }}">{{ $chatbot->title }}</option>
                @endforeach
            </select>
            <input class="input" type="text" name="subject" placeholder="Subject">
            <textarea name="message" placeholder="How can we help?"></textarea>
            <button class="btn" type="submit">Create conversation</button>
        </form>
    </div>
    <div class="card">
        <strong>Your queue</strong>
        <div class="kpi">{{ $conversations->total() }}</div>
        <div class="muted">Persistent portal threads linked to your customer record.</div>
    </div>
</div>
<div class="card">
    <table class="table">
        <thead><tr><th>ID</th><th>Chatbot</th><th>Assigned Agent</th><th>Last Message</th><th>Status</th><th>Open</th></tr></thead>
        <tbody>
            @forelse($conversations as $conversation)
                <tr>
                    <td>{{ $conversation->id }}</td>
                    <td>{{ $conversation->chatbot?->title }}</td>
                    <td>{{ $conversation->assignedAgent?->name ?? 'Unassigned' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($conversation->lastMessage?->message ?? '—', 80) }}</td>
                    <td>{{ $conversation->statusLabel() }}</td>
                    <td><a class="btn alt" href="{{ route('dashboard.chatbot.overlay.customer.show', $conversation) }}">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="6">No conversations yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:16px">{{ $conversations->links() }}</div>
</div>
@endsection
