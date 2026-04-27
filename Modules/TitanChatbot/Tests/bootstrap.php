<?php
/**
 * TitanChatbot test bootstrap – standalone autoloader (no full Laravel app required).
 */

// ── PSR-4 autoloader for this module ────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $prefix  = 'Modules\\TitanChatbot\\';
    $baseDir = __DIR__ . '/../';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ── Global helper stubs ───────────────────────────────────────────────────────
if (!function_exists('app')) {
    function app(string $class = null)
    {
        if ($class === null) { return null; }
        if (class_exists($class)) { return new $class(); }
        return null;
    }
}
if (!function_exists('config')) {
    function config(string $key = null, $default = null) { return $default; }
}
if (!function_exists('now')) {
    function now()
    {
        return new class {
            public function toIso8601String(): string { return date('c'); }
            public function toDateString(): string    { return date('Y-m-d'); }
            public function __toString(): string      { return date('c'); }
        };
    }
}
if (!function_exists('report')) {
    function report(\Throwable $e): void {}
}

// ── Minimal Illuminate facade stubs ──────────────────────────────────────────
// Cache
if (!class_exists('Illuminate\\Support\\Facades\\Cache')) {
    class TitanChatbotCacheStub {
        private static array $store = [];
        public static function get(string $key, mixed $default = null): mixed { return static::$store[$key] ?? $default; }
        public static function put(string $key, mixed $value, int $ttl = 3600): void { static::$store[$key] = $value; }
        public static function increment(string $key, int $by = 1): int { static::$store[$key] = (int)(static::$store[$key] ?? 0) + $by; return static::$store[$key]; }
        public static function forget(string $key): void { unset(static::$store[$key]); }
        public static function flush(): void { static::$store = []; }
    }
    class_alias('TitanChatbotCacheStub', 'Illuminate\\Support\\Facades\\Cache');
}
// Log
if (!class_exists('Illuminate\\Support\\Facades\\Log')) {
    class TitanChatbotLogStub {
        public static function info(string $m, array $c = []): void {}
        public static function warning(string $m, array $c = []): void {}
        public static function error(string $m, array $c = []): void {}
        public static function debug(string $m, array $c = []): void {}
        public static function channel(string $n): static { return new static(); }
    }
    class_alias('TitanChatbotLogStub', 'Illuminate\\Support\\Facades\\Log');
}
// Http
if (!class_exists('Illuminate\\Support\\Facades\\Http')) {
    class TitanChatbotHttpStub {
        public static function withToken(string $t): static { return new static(); }
        public function post(string $u, array $d = []): object {
            return new class { public function failed(): bool { return true; } public function status(): int { return 503; } public function json(string $k = null, mixed $def = null): mixed { return $def; } };
        }
    }
    class_alias('TitanChatbotHttpStub', 'Illuminate\\Support\\Facades\\Http');
}
// DB
if (!class_exists('Illuminate\\Support\\Facades\\DB')) {
    class TitanChatbotDbStub {
        public static function select(string $s, array $b = []): array { return []; }
        public static function table(string $t): static { return new static(); }
        public function insert(array $d): bool { return true; }
    }
    class_alias('TitanChatbotDbStub', 'Illuminate\\Support\\Facades\\DB');
}
// Schema
if (!class_exists('Illuminate\\Support\\Facades\\Schema')) {
    class TitanChatbotSchemaStub {
        public static function hasTable(string $t): bool { return false; }
    }
    class_alias('TitanChatbotSchemaStub', 'Illuminate\\Support\\Facades\\Schema');
}
// Event
if (!class_exists('Illuminate\\Support\\Facades\\Event')) {
    class TitanChatbotEventStub {
        public static function dispatch(object $e): void {}
    }
    class_alias('TitanChatbotEventStub', 'Illuminate\\Support\\Facades\\Event');
}

// ── Illuminate\Database\Eloquent\Model stub ───────────────────────────────────
if (!class_exists('Illuminate\\Database\\Eloquent\\Model')) {
    class TitanChatbotEloquentModel {
        protected array $attributes = [];
        public function __construct(array $attrs = []) { $this->attributes = $attrs; }
        public function getAttribute(string $k): mixed { return $this->attributes[$k] ?? null; }
        public static function find(mixed $id): ?static { return null; }
        public static function firstOrCreate(array $s, array $a = []): static { return new static(array_merge($s,$a)); }
        public function fill(array $a): static { $this->attributes = array_merge($this->attributes, $a); return $this; }
        public function save(): bool { return true; }
        public function load(string ...$r): static { return $this; }
    }
    class_alias('TitanChatbotEloquentModel', 'Illuminate\\Database\\Eloquent\\Model');
}

// ── Illuminate\Database\Eloquent\Scope stub ───────────────────────────────────
if (!interface_exists('Illuminate\\Database\\Eloquent\\Scope')) {
    interface TitanChatbotEloquentScope {}
    class_alias('TitanChatbotEloquentScope', 'Illuminate\\Database\\Eloquent\\Scope');
}

// ── Illuminate\Routing\Controller stub ───────────────────────────────────────
if (!class_exists('Illuminate\\Routing\\Controller')) {
    class TitanChatbotControllerStub {}
    class_alias('TitanChatbotControllerStub', 'Illuminate\\Routing\\Controller');
}

// ── Illuminate\Foundation\Support\Providers\EventServiceProvider stub ─────────
if (!class_exists('Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider')) {
    class TitanChatbotESPStub {
        public function __construct() {}
        public function register(): void {}
        public function boot(): void {}
    }
    class_alias('TitanChatbotESPStub', 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider');
}

// ── Illuminate\Support\ServiceProvider stub ────────────────────────────────────
if (!class_exists('Illuminate\\Support\\ServiceProvider')) {
    class TitanChatbotSPStub {
        public function __construct($app = null) {}
        public function register(): void {}
        public function boot(): void {}
        protected function mergeConfigFrom(string $p, string $k): void {}
        protected function loadMigrationsFrom(string $p): void {}
        protected function loadViewsFrom(string $p, string $n): void {}
        protected function loadTranslationsFrom(string $p, string $n): void {}
        protected function loadRoutesFrom(string $p): void {}
    }
    class_alias('TitanChatbotSPStub', 'Illuminate\\Support\\ServiceProvider');
}
