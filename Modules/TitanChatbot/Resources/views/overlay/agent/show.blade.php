@extends('chatbot::overlay.layout', ['title' => 'Conversation #' . $conversation->id])
@section('content')
<div class="card">
    <strong>{{ $conversation->chatbot?->title }}</strong>
    <div class="muted">{{ $conversation->customer?->name ?? 'Unknown customer' }} · {{ $conversation->statusLabel() }} · Assigned to {{ $conversation->assignedAgent?->name ?? 'nobody' }}</div>
</div>
<div class="card messages">
    @foreach($messages as $message)
        <div class="msg">
            <strong>{{ $message->role }}</strong>
            @if($message->is_internal_note)<span class="badge">Internal note</span>@endif
            <div>{{ $message->message }}</div>
            <small>{{ optional($message->created_at)->diffForHumans() }}</small>
        </div>
    @endforeach
    <div style="margin-top:16px">{{ $messages->links() }}</div>
</div>
<div class="row">
    <form method="post" action="{{ route('dashboard.chatbot.overlay.agent.reply', $conversation) }}" enctype="multipart/form-data" class="card stack">
        @csrf
        <strong>Reply</strong>
        <textarea name="message"></textarea>
        <input class="input" type="file" name="attachment">
        <button class="btn" type="submit">Send reply</button>
    </form>
    <div class="stack">
        <form method="post" action="{{ route('dashboard.chatbot.overlay.agent.claim', $conversation) }}" class="card">@csrf<button class="btn alt" type="submit">Claim conversation</button></form>
        <form method="post" action="{{ route('dashboard.chatbot.overlay.agent.note', $conversation) }}" class="card stack">@csrf<strong>Add internal note</strong><textarea name="message"></textarea><button class="btn alt" type="submit">Save note</button></form>
        <form method="post" action="{{ route('dashboard.chatbot.overlay.agent.transfer', $conversation) }}" class="card stack">@csrf<strong>Transfer</strong><input class="input" type="number" name="agent_id" placeholder="Target agent id"><textarea name="reason" placeholder="Reason"></textarea><button class="btn alt" type="submit">Transfer</button></form>
        <form method="post" action="{{ route('dashboard.chatbot.overlay.agent.close', $conversation) }}" class="card stack">@csrf<input class="input" type="text" name="reason" placeholder="Close reason (optional)"><button class="btn warn" type="submit">Close conversation</button></form>
    </div>
</div>
@endsection
