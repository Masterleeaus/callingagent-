<?php

/**
 * ManifestValidator
 *
 * Validates all manifest JSON files in Modules/CallingAgent/manifests/ against
 * the actual files present on disk.
 *
 * Run standalone: php Modules/CallingAgent/Support/Validation/ManifestValidator.php
 */

$moduleRoot   = dirname(__DIR__, 2);
$manifestsDir = $moduleRoot . '/manifests';
$errors       = [];
$warnings     = [];

$manifestFiles = glob($manifestsDir . '/*.json') ?: [];
$manifestFiles[] = $moduleRoot . '/module.json';
$manifestFiles[] = $moduleRoot . '/module.lock.json';
$manifestFiles[] = $moduleRoot . '/PASS12_FILE_MANIFEST.json';

foreach ($manifestFiles as $manifestFile) {
    if (!file_exists($manifestFile)) {
        $warnings[] = "Missing manifest file: " . basename($manifestFile);
        continue;
    }

    $data = json_decode(file_get_contents($manifestFile), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errors[] = "Invalid JSON in " . basename($manifestFile) . ": " . json_last_error_msg();
        continue;
    }

    $name = basename($manifestFile);

    // Validate 'items' array entries that reference files
    $items = $data['items'] ?? $data['added'] ?? $data['modified'] ?? [];
    if (is_array($items)) {
        foreach ($items as $item) {
            if (is_string($item)) {
                $filePath = $moduleRoot . '/' . ltrim($item, '/');
                if (!file_exists($filePath)) {
                    $warnings[] = "[{$name}] Referenced file not found: {$item}";
                }
            }
        }
    }

    // Validate module.json has required fields
    if (basename($manifestFile) === 'module.json') {
        foreach (['name', 'version', 'providers'] as $required) {
            if (!isset($data[$required])) {
                $warnings[] = "[module.json] Missing field: {$required}";
            }
        }
        // Validate provider class paths
        foreach ($data['providers'] ?? [] as $provider) {
            // Convert PSR-4 FQCN to relative path from module root
            // e.g. "Modules\CallingAgent\Providers\Foo" → "Providers/Foo.php"
            $withoutPrefix = preg_replace('/^Modules[\/\\\\]CallingAgent[\/\\\\]/', '', str_replace('\\', '/', $provider));
            $filePath      = $moduleRoot . '/' . $withoutPrefix . '.php';
            if (!file_exists($filePath)) {
                $warnings[] = "[module.json] Provider class file not found: {$provider} (looked for: {$filePath})";
            }
        }
    }
}

// ── Count total PHP files vs manifest file_count ──────────────────────────────

$phpCount = 0;
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($moduleRoot, FilesystemIterator::SKIP_DOTS));
foreach ($iter as $file) {
    if ($file->getExtension() === 'php'
        && !str_contains($file->getRealPath(), '/LegacySources/')
        && !str_contains($file->getRealPath(), '/SourceArchives/')) {
        $phpCount++;
    }
}

// ── Report ────────────────────────────────────────────────────────────────────

echo "=== CallingAgent Manifest Validation Report ===\n";
echo "Manifests checked: " . count($manifestFiles) . "\n";
echo "PHP files on disk: {$phpCount}\n\n";

foreach ($errors as $e) {
    echo "❌ {$e}\n";
}
foreach ($warnings as $w) {
    echo "⚠  {$w}\n";
}

if (empty($errors) && empty($warnings)) {
    echo "✅ All manifests valid.\n";
}

echo "\n" . count($errors) . " error(s), " . count($warnings) . " warning(s).\n";
exit(empty($errors) ? 0 : 1);
