<?php

namespace Modules\TitanChatbot\Contracts;

interface ChannelDriver
{
    public function handle(array $payload): string;
}
