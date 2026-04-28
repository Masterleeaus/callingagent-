<?php

namespace Modules\TitanChatbot\AI\Agents;

/**
 * Voice-optimised agent — extends TitanAgent via ConversationAgent.
 * Post-processes the reply for TTS output after delegating to parent::respond().
 */
class VoiceAgent extends ConversationAgent
{
    public string $system_prompt = 'You are a voice assistant. Respond with short, clear sentences only. No bullet points, no markdown, no numbered lists. Every response must be speakable and under 30 words when possible.';

    public bool $streaming = false;

    /** @var callable|null */
    private $streamingHook = null;

    public function respond(string $utterance, array $context = []): string
    {
        $reply = parent::respond($utterance, $context);

        return $this->sanitiseForTts($reply);
    }

    public function getStreamingHook(): ?callable
    {
        return $this->streamingHook;
    }

    public function setStreamingHook(callable $hook): self
    {
        $this->streamingHook = $hook;

        return $this;
    }

    private function sanitiseForTts(string $text): string
    {
        // Strip markdown and list markers
        $text = preg_replace('/[#*_`~>]+/', '', $text);
        $text = preg_replace('/^\s*[-\d]+[.)]\s+/m', '', $text);
        $text = preg_replace('/\s{2,}/', ' ', $text);

        return trim($text);
    }
}
