{{-- Titan Go iframe ui (additive). Voice optional. No reasoning. --}}
@php
    $mode = request('mode', 'go'); // go|pro
    $context = request('context', 'unknown'); // driving|jobsite|office|unknown
    $silence = config('chatbot-voice.titan_go.silence.' . $context, config('chatbot-voice.titan_go.silence.unknown'));
    if ($mode === 'pro' && config('chatbot-voice.titan_go.pro_mode_uses_office_timers', true)) {
        $silence = config('chatbot-voice.titan_go.silence.office', $silence);
    }
@endphp

@vite('app/Extensions/ChatbotVoice/resources/assets/scss/external-chatbot-voice.scss')
@vite('app/Extensions/ChatbotVoice/resources/assets/js/titan-go.js')

<div
    id="titan-go-root"
    class="lqd-ext-chatbot-voice titan-go"
    data-uuid="{{ $chatbot->uuid }}"
    data-session-id="{{ \Illuminate\Support\Str::uuid() }}"
    data-mode="{{ $mode }}"
    data-context="{{ $context }}"
    data-silence-prompt-ms="{{ (int)($silence['prompt_ms'] ?? 120000) }}"
    data-silence-end-ms="{{ (int)($silence['end_ms'] ?? 240000) }}"
    data-pro-url="{{ config('chatbot-voice.titan_go.pro_url', '/dashboard') }}"
    data-beep="{{ config('chatbot-voice.titan_go.beep', false) ? '1' : '0' }}"
    data-hold-to-talk="{{ config('chatbot-voice.titan_go.hold_to_talk', false) ? '1' : '0' }}"
    data-restrict-buttons="{{ config('chatbot-voice.titan_go.restrict_buttons_until_hint', true) ? '1' : '0' }}"
    data-streaming-enabled="{{ config('chatbot-voice.titan_go.streaming.enabled', false) ? '1' : '0' }}"
>
    @if (!$chatbot['active'])
        <p>@lang('This chatbot is not active.')</p>
    @else
        <div class="lqd-ext-chatbot-voice-window">
            <div class="lqd-ext-chatbot-voice-window-head">
                <strong>Titan Go</strong>
                <button type="button" id="titan-go-stop-btn">@lang('Stop')</button>
            </div>

            <div id="titan-go-messages" class="lqd-ext-chatbot-voice-window-body"></div>

            <div class="lqd-ext-chatbot-voice-window-foot">
                <div id="titan-go-transcript" class="lqd-ext-chatbot-voice-transcript"></div>

                <div class="lqd-ext-chatbot-voice-controls">
                    <button type="button" id="titan-go-mic-btn" aria-label="Start voice input">@lang('Tap to talk')</button>

                    <button type="button" id="titan-go-retry-btn" aria-label="Retry voice input" style="display:none;">@lang('Retry')</button>

                    <input
                        type="text"
                        id="titan-go-text-input" aria-label="Text input"
                        placeholder="@lang('Type here…')"
                        autocomplete="off"
                    />

                    <div class="titan-go-quick-btns">
                        <button type="button" data-titan-go-btn="YES" aria-label="YES">Yes</button>
                        <button type="button" data-titan-go-btn="NO" aria-label="NO">No</button>
                        <button type="button" data-titan-go-btn="REPEAT" aria-label="REPEAT">Repeat</button>
                        <button type="button" data-titan-go-btn="CANCEL" aria-label="CANCEL">Cancel</button>
                        <button type="button" data-titan-go-btn="OPEN_PRO" aria-label="OPEN_PRO">Open in Pro</button>
                    </div>
                </div>
            </div>
        
            <div id="titan-go-resume-container"></div>
        </div>
    @endif
</div>
