<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Context;

use Modules\CallingAgent\AI\Messages\AssistantMessage;
use Modules\CallingAgent\AI\Messages\MessageCollection;
use Modules\CallingAgent\AI\Messages\SystemMessage;
use Modules\CallingAgent\AI\Messages\ToolResultMessage;
use Modules\CallingAgent\AI\Messages\UserMessage;

final class DatabaseChatHistory implements ChatHistoryInterface
{
    public function __construct(
        private string $sessionId,
        private string $table = 'calling_agent_transcripts',
    ) {}

    public function addMessage(SystemMessage|UserMessage|AssistantMessage|ToolResultMessage $msg): void
    {
        if (! class_exists(\Illuminate\Support\Facades\DB::class)) {
            return;
        }

        try {
            $arr = $msg->toArray();
            \Illuminate\Support\Facades\DB::table($this->table)->insert([
                'session_id' => $this->sessionId,
                'role' => $arr['role'],
                'content' => is_array($arr['content'] ?? null)
                    ? json_encode($arr['content'], JSON_UNESCAPED_UNICODE)
                    : (string) ($arr['content'] ?? ''),
                'tool_call_id' => $arr['tool_call_id'] ?? null,
                'tool_calls' => isset($arr['tool_calls']) ? json_encode($arr['tool_calls']) : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // Silently skip if table is absent or DB is unavailable
        }
    }

    public function getMessages(): array
    {
        if (! class_exists(\Illuminate\Support\Facades\DB::class)) {
            return [];
        }

        try {
            $rows = \Illuminate\Support\Facades\DB::table($this->table)
                ->where('session_id', $this->sessionId)
                ->orderBy('id')
                ->get();

            return $rows->map(fn ($row) => $this->rowToMessage($row))->filter()->values()->all();
        } catch (\Throwable) {
            return [];
        }
    }

    public function count(): int
    {
        if (! class_exists(\Illuminate\Support\Facades\DB::class)) {
            return 0;
        }

        try {
            return (int) \Illuminate\Support\Facades\DB::table($this->table)
                ->where('session_id', $this->sessionId)
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    public function clear(): void
    {
        if (! class_exists(\Illuminate\Support\Facades\DB::class)) {
            return;
        }

        try {
            \Illuminate\Support\Facades\DB::table($this->table)
                ->where('session_id', $this->sessionId)
                ->delete();
        } catch (\Throwable) {
            // Silently skip
        }
    }

    public function truncate(int $limit): void
    {
        if (! class_exists(\Illuminate\Support\Facades\DB::class)) {
            return;
        }

        try {
            $total = $this->count();

            if ($total <= $limit) {
                return;
            }

            // Count system messages (preserve them)
            $systemCount = (int) \Illuminate\Support\Facades\DB::table($this->table)
                ->where('session_id', $this->sessionId)
                ->where('role', 'system')
                ->count();

            $keepNonSystem = max(0, $limit - $systemCount);

            // Get IDs of non-system rows ordered oldest first
            $nonSystemIds = \Illuminate\Support\Facades\DB::table($this->table)
                ->where('session_id', $this->sessionId)
                ->where('role', '!=', 'system')
                ->orderBy('id')
                ->pluck('id');

            $deleteIds = $nonSystemIds->slice(0, max(0, $nonSystemIds->count() - $keepNonSystem))->values()->all();

            if (! empty($deleteIds)) {
                \Illuminate\Support\Facades\DB::table($this->table)
                    ->whereIn('id', $deleteIds)
                    ->delete();
            }
        } catch (\Throwable) {
            // Silently skip
        }
    }

    public function toMessageCollection(): MessageCollection
    {
        $collection = MessageCollection::make();

        foreach ($this->getMessages() as $msg) {
            $collection = $collection->add($msg);
        }

        return $collection;
    }

    private function rowToMessage(object $row): SystemMessage|UserMessage|AssistantMessage|ToolResultMessage|null
    {
        $role = $row->role ?? '';
        $content = $row->content ?? '';

        return match ($role) {
            'system' => new SystemMessage($content),
            'user' => new UserMessage($content),
            'assistant' => new AssistantMessage(
                $content,
                isset($row->tool_calls) && $row->tool_calls
                    ? (json_decode($row->tool_calls, true) ?? [])
                    : [],
            ),
            'tool' => new ToolResultMessage($row->tool_call_id ?? '', $content),
            default => null,
        };
    }
}
