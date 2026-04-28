<?php

namespace Modules\TitanChatbot\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Tests for the AI-native module structure (knowledge pack, booking agent, AI manifests).
 */
class AiNativeStructureTest extends TestCase
{
    private string $moduleRoot;

    protected function setUp(): void
    {
        $this->moduleRoot = dirname(__DIR__, 2); // Modules/TitanChatbot/
    }

    // Knowledge Pack Tests

    public function test_knowledge_directory_exists(): void
    {
        $this->assertDirectoryExists($this->moduleRoot . '/Knowledge');
    }

    public function test_knowledge_readme_exists(): void
    {
        $this->assertFileExists($this->moduleRoot . '/Knowledge/README.md');
        $this->assertGreaterThan(100, filesize($this->moduleRoot . '/Knowledge/README.md'));
    }

    public function test_knowledge_subdirectories_exist(): void
    {
        $dirs = ['guides', 'policies', 'pricing', 'faqs', 'contracts', 'compliance', 'sops', 'checklists', 'examples'];
        foreach ($dirs as $dir) {
            $this->assertDirectoryExists(
                $this->moduleRoot . '/Knowledge/' . $dir,
                "Knowledge/{$dir} directory should exist"
            );
        }
    }

    public function test_knowledge_files_are_non_empty(): void
    {
        $files = [
            'guides/cleaning-estimating-guide.md',
            'policies/service-policy.md',
            'pricing/rate-card.md',
            'faqs/cleaning-faqs.md',
            'sops/operator-sop.md',
            'checklists/quality-checklist.md',
            'compliance/compliance-notes.md',
            'examples/sample-booking-conversation.md',
        ];
        foreach ($files as $file) {
            $path = $this->moduleRoot . '/Knowledge/' . $file;
            $this->assertFileExists($path, "Knowledge/{$file} should exist");
            $this->assertGreaterThan(200, filesize($path), "Knowledge/{$file} should be substantial");
        }
    }

    // BookingAgent Tests

    public function test_booking_agent_directory_exists(): void
    {
        $this->assertDirectoryExists($this->moduleRoot . '/Agents/BookingAgent');
    }

    public function test_booking_agent_manifest_is_valid_json(): void
    {
        $path = $this->moduleRoot . '/Agents/BookingAgent/agent.manifest.json';
        $this->assertFileExists($path);
        $data = json_decode(file_get_contents($path), true);
        $this->assertIsArray($data, 'agent.manifest.json must be valid JSON');
        $this->assertArrayHasKey('agent_id', $data);
        $this->assertArrayHasKey('tools', $data);
        $this->assertArrayHasKey('prompts', $data);
    }

    public function test_booking_agent_prompts_exist_and_are_non_empty(): void
    {
        $prompts = ['system.md', 'answering.md', 'tool-use.md'];
        foreach ($prompts as $prompt) {
            $path = $this->moduleRoot . '/Agents/BookingAgent/prompts/' . $prompt;
            $this->assertFileExists($path, "BookingAgent prompt {$prompt} should exist");
            $this->assertGreaterThan(50, filesize($path), "BookingAgent prompt {$prompt} should be non-trivial");
        }
    }

    public function test_booking_agent_tool_schemas_are_valid_json(): void
    {
        $toolDir = $this->moduleRoot . '/Agents/BookingAgent/tools/';
        $this->assertDirectoryExists($toolDir);
        $files = glob($toolDir . '*.tool.json');
        $this->assertNotEmpty($files, 'Should have at least one tool JSON file');

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $data    = json_decode($content, true);
            $this->assertIsArray($data, basename($file) . ' must be valid JSON');
            $this->assertArrayHasKey('name', $data, basename($file) . ' must have a name');
            $this->assertArrayHasKey('parameters', $data, basename($file) . ' must have parameters');
        }
    }

    public function test_booking_agent_has_all_required_tools(): void
    {
        $toolDir      = $this->moduleRoot . '/Agents/BookingAgent/tools/';
        $expectedTools = [
            'calculate-quote.tool.json',
            'check-service-area.tool.json',
            'lookup-available-slots.tool.json',
            'create-booking-request.tool.json',
            'lookup-customer.tool.json',
            'create-escalation.tool.json',
            'search-knowledge.tool.json',
        ];
        foreach ($expectedTools as $tool) {
            $this->assertFileExists($toolDir . $tool, "Tool schema {$tool} should exist");
        }
    }

    public function test_booking_agent_eval_suite_is_valid_json(): void
    {
        $path = $this->moduleRoot . '/Agents/BookingAgent/evaluations/eval-suite.json';
        $this->assertFileExists($path);
        $data = json_decode(file_get_contents($path), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('cases', $data);
        $this->assertNotEmpty($data['cases']);
    }

    public function test_booking_agent_training_manifest_is_valid_json(): void
    {
        $path = $this->moduleRoot . '/Agents/BookingAgent/training/dataset-manifest.json';
        $this->assertFileExists($path);
        $data = json_decode(file_get_contents($path), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('datasets', $data);
    }

    // AI Native Files Tests

    public function test_ai_indexing_manifest_is_valid_json(): void
    {
        $path = $this->moduleRoot . '/AI/Indexing/indexing.manifest.json';
        $this->assertFileExists($path);
        $data = json_decode(file_get_contents($path), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('indexing', $data);
        $this->assertArrayHasKey('index_targets', $data['indexing']);
    }

    public function test_ai_citation_schema_is_valid_json(): void
    {
        $path = $this->moduleRoot . '/AI/Citations/citation.schema.json';
        $this->assertFileExists($path);
        $data = json_decode(file_get_contents($path), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('citation', $data);
    }

    public function test_ai_retrieval_policy_is_valid_json(): void
    {
        $path = $this->moduleRoot . '/AI/Retrieval/retrieval.policy.json';
        $this->assertFileExists($path);
        $data = json_decode(file_get_contents($path), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('retrieval', $data);
    }

    public function test_ai_guardrails_is_valid_json(): void
    {
        $path = $this->moduleRoot . '/AI/Guardrails/guardrails.json';
        $this->assertFileExists($path);
        $data = json_decode(file_get_contents($path), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('input_guardrails', $data);
        $this->assertArrayHasKey('output_guardrails', $data);
        $this->assertArrayHasKey('escalation_triggers', $data);
    }

    public function test_ai_action_map_is_valid_json(): void
    {
        $path = $this->moduleRoot . '/AI/Actions/action-map.json';
        $this->assertFileExists($path);
        $data = json_decode(file_get_contents($path), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('intents', $data);
        $this->assertNotEmpty($data['intents']);
    }

    public function test_ai_telemetry_manifest_is_valid_json(): void
    {
        $path = $this->moduleRoot . '/AI/Telemetry/telemetry.manifest.json';
        $this->assertFileExists($path);
        $data = json_decode(file_get_contents($path), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('metrics', $data);
        $this->assertNotEmpty($data['metrics']);
    }

    public function test_ai_readme_exists(): void
    {
        $path = $this->moduleRoot . '/AI/README.md';
        $this->assertFileExists($path);
        $this->assertGreaterThan(200, filesize($path));
    }

    // Docs Tests

    public function test_docs_directory_exists(): void
    {
        $this->assertDirectoryExists($this->moduleRoot . '/Docs');
    }

    public function test_docs_blueprint_notes_exist(): void
    {
        $path = $this->moduleRoot . '/Docs/BLUEPRINT_NOTES.md';
        $this->assertFileExists($path);
        $this->assertGreaterThan(100, filesize($path));
    }

    public function test_docs_ai_native_guide_exists(): void
    {
        $path = $this->moduleRoot . '/Docs/AI_NATIVE_MODULE_GUIDE.md';
        $this->assertFileExists($path);
        $this->assertGreaterThan(100, filesize($path));
    }

    public function test_acceptance_md_exists(): void
    {
        $path = $this->moduleRoot . '/ACCEPTANCE.md';
        $this->assertFileExists($path);
    }

    // Tool schema structure tests

    public function test_all_tool_schemas_have_required_fields(): void
    {
        $toolDir = $this->moduleRoot . '/Agents/BookingAgent/tools/';
        $files   = glob($toolDir . '*.tool.json');

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            $this->assertArrayHasKey('name', $data, basename($file) . ' needs name');
            $this->assertArrayHasKey('description', $data, basename($file) . ' needs description');
            $this->assertArrayHasKey('parameters', $data, basename($file) . ' needs parameters');
            $this->assertSame('object', $data['parameters']['type'] ?? '', basename($file) . ' parameters.type must be object');
        }
    }

    public function test_eval_suite_cases_have_required_fields(): void
    {
        $path = $this->moduleRoot . '/Agents/BookingAgent/evaluations/eval-suite.json';
        $data = json_decode(file_get_contents($path), true);

        foreach ($data['cases'] as $case) {
            $this->assertArrayHasKey('id', $case, 'Each eval case needs an id');
            $this->assertArrayHasKey('category', $case, 'Each eval case needs a category');
            $this->assertArrayHasKey('input', $case, 'Each eval case needs an input');
            $this->assertArrayHasKey('pass_criteria', $case, 'Each eval case needs pass_criteria');
        }
    }

    public function test_agent_manifest_tools_have_matching_schema_files(): void
    {
        $manifestPath = $this->moduleRoot . '/Agents/BookingAgent/agent.manifest.json';
        $manifest     = json_decode(file_get_contents($manifestPath), true);

        foreach ($manifest['tools'] as $tool) {
            $schemaPath = $this->moduleRoot . '/' . $tool['schema'];
            $this->assertFileExists(
                $schemaPath,
                "Tool schema file {$tool['schema']} referenced in manifest must exist"
            );
        }
    }
}
