<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Workflow\Tools;

class RecordFindTool extends BaseExternalTool
{
    public function key(): string
    {
        return 'record.find';
    }
}
