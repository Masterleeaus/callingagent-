<?php
namespace Modules\CallingAgent\Services\Realtime;

final class AudioFrameBuffer
{
    private array $frames = [];
    public function push(array $frame): void { $this->frames[] = $frame; }
    public function flush(): array { $frames = $this->frames; $this->frames = []; return $frames; }
    public function count(): int { return count($this->frames); }
}
