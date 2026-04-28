<?php
namespace Modules\TitanChatbot\AI\DataModels;

class VoiceResponseModel extends BaseDataModel
{
    public string $text         = '';
    public string $ssml         = '';
    public float  $duration     = 0.0;
    public bool   $endSession   = false;
    public bool   $transferCall = false;
    public string $transferTo   = '';
    public array  $metadata     = [];

    public function isValid(): bool
    {
        return !empty($this->text) || !empty($this->ssml);
    }

    public function getSpeakableText(): string
    {
        return $this->ssml ?: $this->text;
    }
}
