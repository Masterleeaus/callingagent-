<?php
namespace Modules\TitanChatbot\AI\Memory\Drivers;

use Modules\TitanChatbot\AI\Memory\MemoryDriverInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseMemoryDriver implements MemoryDriverInterface
{
    private const TABLE = 'titan_chatbot_conversation_memory';

    public function get(string $sessionId): array
    {
        if (!$this->tableExists()) {
            return [];
        }
        $rows = DB::table(self::TABLE)
            ->where('session_id', $sessionId)
            ->orderBy('id')
            ->select(['role', 'content'])
            ->get();
        return array_map(fn($r) => ['role' => $r->role ?? $r['role'], 'content' => $r->content ?? $r['content']], iterator_to_array($rows));
    }

    public function push(string $sessionId, string $role, string $content): void
    {
        if (!$this->tableExists()) {
            return;
        }
        DB::table(self::TABLE)->insert([
            'session_id' => $sessionId,
            'role'       => $role,
            'content'    => $content,
            'created_at' => now(),
        ]);
    }

    public function forget(string $sessionId): void
    {
        if (!$this->tableExists()) {
            return;
        }
        DB::table(self::TABLE)->where('session_id', $sessionId)->delete();
    }

    private function tableExists(): bool
    {
        try {
            return class_exists(Schema::class) && Schema::hasTable(self::TABLE);
        } catch (\Throwable) {
            return false;
        }
    }
}
