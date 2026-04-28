@extends('chatbot::overlay.layout', ['title' => 'Customer Conversation #' . $conversation->id])
@section('content')
<div class="card">
    <strong>{{ $conversation->chatbot?->title }}</strong>
    <div class="muted">Assigned to {{ $conversation->assignedAgent?->name ?? 'support queue' }}</div>
</div>
<div class="card messages">
    @foreach($messages as $message)
        <div class="msg">
            <strong>{{ $message->role }}</strong>
            <div>{{ $message->message }}</div>
            <small>{{ optional($message->created_at)->diffForHumans() }}</small>
        </div>
    @endforeach
    <div style="margin-top:16px">{{ $messages->links() }}</div>
</div>
<div class="row">
    <form method="post" action="{{ route('dashboard.chatbot.overlay.customer.message', $conversation) }}" enctype="multipart/form-data" class="card stack">
        @csrf
        <strong>Reply</strong>
        <textarea name="message"></textarea>
        <input class="input" type="file" name="attachment">
        <button class="btn" type="submit">Send message</button>
    </form>
    <div class="stack">
        <form method="post" action="{{ route('dashboard.chatbot.overlay.customer.feedback', $conversation) }}" class="card stack">@csrf<strong>Feedback</strong><input class="input" type="number" name="rating" min="1" max="5" placeholder="Rating 1-5"><textarea name="feedback" placeholder="Feedback"></textarea><button class="btn alt" type="submit">Save feedback</button></form>
        <form method="post" action="{{ route('dashboard.chatbot.overlay.customer.reopen', $conversation) }}" class="card">@csrf<button class="btn alt" type="submit">Reopen conversation</button></form>
        <a class="btn alt" href="{{ route('dashboard.chatbot.overlay.customer.export', $conversation) }}">Export conversation</a>
    </div>
</div>
@endsection
