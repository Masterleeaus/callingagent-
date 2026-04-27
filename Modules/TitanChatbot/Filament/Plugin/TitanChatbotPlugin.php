<?php
namespace Modules\TitanChatbot\Filament\Plugin;

class TitanChatbotPlugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'titan-chatbot';
    }
}
