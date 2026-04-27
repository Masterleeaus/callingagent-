<div class="grid">
@foreach($availableChannels as $channel)
    @php($record = $chatbot->channels->firstWhere('channel', $channel))
    <div class="card stack">
        <div>
            <strong>{{ ucfirst($channel) }}</strong>
            <div class="muted">{{ $record ? 'Configured' : 'Not configured yet' }}</div>
        </div>
        <div class="muted">Webhook: <code>{{ route($channel === 'generic' ? 'api.chatbot.webhook' : 'api.chatbot.webhook.' . $channel, $channel === 'generic' ? ['chatbot' => $chatbot, 'channel' => $channel] : $chatbot) }}</code></div>
        <form method="post" action="{{ route('dashboard.chatbot.overlay.command.channels.save', $chatbot) }}" class="stack">
            @csrf
            <input type="hidden" name="channel" value="{{ $channel }}">
            <input class="input" type="text" name="credentials[token]" placeholder="Token / Access key" value="{{ $record?->credential('token') }}">
            <input class="input" type="text" name="credentials[secret]" placeholder="Secret / Verify token" value="{{ $record?->credential('secret') }}">
            <input class="input" type="text" name="payload[label]" placeholder="Display label" value="{{ $record?->payloadValue('label') }}">
            <button class="btn" type="submit">Save {{ ucfirst($channel) }}</button>
        </form>
    </div>
@endforeach
</div>
