<?php
namespace Modules\TitanChatbot\AI\Memory\Drivers;

use Modules\TitanChatbot\AI\Memory\MemoryDriverInterface;

class FileMemoryDriver implements MemoryDriverInterface
{
    private string $basePath;

    public function __construct(string $basePath = '')
    {
        $this->basePath = $basePath ?: sys_get_temp_dir() . '/titan_chatbot_memory';
        if (!is_dir($this->basePath)) {
            @mkdir($this->basePath, 0700, true);
        }
    }

    private function path(string $sessionId): string
    {
        return $this->basePath . '/' . sha1($sessionId) . '.json';
    }

    public function get(string $sessionId): array
    {
        $file = $this->path($sessionId);
        if (!file_exists($file)) {
            return [];
        }
        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }

    public function push(string $sessionId, string $role, string $content): void
    {
        $messages   = $this->get($sessionId);
        $messages[] = ['role' => $role, 'content' => $content];
        file_put_contents($this->path($sessionId), json_encode($messages));
    }

    public function forget(string $sessionId): void
    {
        $file = $this->path($sessionId);
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
