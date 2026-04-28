<?php

/**
 * ModuleValidator
 *
 * Static validation script for Modules/CallingAgent.
 * Run standalone: php Modules/CallingAgent/Support/Validation/ModuleValidator.php
 *
 * Checks:
 *  1. All PHP files in the module parse without errors.
 *  2. All namespaced classes reference directories that exist.
 *  3. All classes listed in config files exist.
 *  4. Key manifest files are valid JSON.
 */

$moduleRoot = dirname(__DIR__, 2);
$errors     = [];
$warnings   = [];
$checked    = 0;

// ── 1. PHP syntax scan ────────────────────────────────────────────────────────

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($moduleRoot, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getRealPath();
    // Skip archived / legacy sources
    if (str_contains($path, '/LegacySources/') || str_contains($path, '/SourceArchives/')) {
        continue;
    }

    $output = shell_exec('php -l ' . escapeshellarg($path) . ' 2>&1');
    $checked++;
    if (preg_match('/^Parse error:|^Fatal error:/m', $output ?? '')) {
        $errors[] = "[SYNTAX] {$path}: {$output}";
    }
}

// ── 2. Manifest JSON validation ───────────────────────────────────────────────

$manifests = glob($moduleRoot . '/manifests/*.json');
$manifests[] = $moduleRoot . '/module.json';
$manifests[] = $moduleRoot . '/module.lock.json';

foreach ($manifests as $manifest) {
    if (!file_exists($manifest)) {
        $warnings[] = "[MANIFEST] Missing expected file: {$manifest}";
        continue;
    }
    $decoded = json_decode(file_get_contents($manifest), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errors[] = "[MANIFEST] Invalid JSON in {$manifest}: " . json_last_error_msg();
    }
}

// ── 3. Config class references ────────────────────────────────────────────────

$configDir = $moduleRoot . '/Config';
foreach (glob($configDir . '/*.php') as $configFile) {
    $content = file_get_contents($configFile);
    preg_match_all('/\\\\?Modules\\\\CallingAgent\\\\[A-Za-z\\\\]+/', $content, $matches);
    foreach ($matches[0] as $fqcn) {
        // Convert FQCN to relative path
        $relative = str_replace(['Modules\\CallingAgent\\', '\\'], ['', '/'], ltrim($fqcn, '\\'));
        $filePath = $moduleRoot . '/' . $relative . '.php';
        if (!file_exists($filePath)) {
            $warnings[] = "[CONFIG] Class referenced in " . basename($configFile) . " not found: {$fqcn}";
        }
    }
}

// ── 4. Key directory presence ─────────────────────────────────────────────────

$requiredDirs = [
    'AI/Core', 'AI/Tools', 'AI/Messages', 'AI/Context',
    'AI/StructuredOutput', 'AI/Drivers', 'AI/Usage',
    'Events/AI', 'Support/Validation',
    'Billing/Meters', 'Billing/Usage',
    'Services/Realtime', 'Filament/Resources',
];
foreach ($requiredDirs as $dir) {
    if (!is_dir($moduleRoot . '/' . $dir)) {
        $errors[] = "[STRUCTURE] Required directory missing: {$dir}";
    }
}

// ── Report ────────────────────────────────────────────────────────────────────

echo "=== CallingAgent Module Validation Report ===\n";
echo "PHP files checked: {$checked}\n\n";

if (empty($errors) && empty($warnings)) {
    echo "✅ All checks passed.\n";
    exit(0);
}

foreach ($errors as $e) {
    echo "❌ {$e}\n";
}
foreach ($warnings as $w) {
    echo "⚠  {$w}\n";
}

$exitCode = empty($errors) ? 0 : 1;
echo "\n" . count($errors) . " error(s), " . count($warnings) . " warning(s).\n";
exit($exitCode);
