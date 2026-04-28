<?php
namespace Modules\TitanChatbot\Tests\Unit;
use PHPUnit\Framework\TestCase;
use Modules\TitanChatbot\AI\Tools\SchemaGenerator;
use Modules\TitanChatbot\AI\Attributes\Tool;
use Modules\TitanChatbot\AI\Attributes\Desc;

class SchemaGeneratorTest extends TestCase
{
    private SchemaGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new SchemaGenerator();
    }

    public function test_generates_schema_from_annotated_method()
    {
        $agent = new class {
            #[Tool(name: 'search_knowledge', description: 'Search the knowledge base')]
            public function searchKnowledge(
                #[Desc('The query string')] string $query,
                #[Desc('Maximum results', required: false)] int $limit = 5
            ): string {
                return '';
            }
        };

        $schemas = $this->generator->generateFromAgent($agent);

        $this->assertCount(1, $schemas);
        $schema = $schemas[0];

        $this->assertSame('function', $schema['type']);
        $this->assertSame('search_knowledge', $schema['function']['name']);
        $this->assertSame('Search the knowledge base', $schema['function']['description']);
        $this->assertArrayHasKey('query', $schema['function']['parameters']['properties']);
        $this->assertSame('string', $schema['function']['parameters']['properties']['query']['type']);
        $this->assertContains('query', $schema['function']['parameters']['required']);
    }

    public function test_generates_schema_from_multiple_annotated_methods()
    {
        $agent = new class {
            #[Tool(name: 'tool_one', description: 'First tool')]
            public function toolOne(string $a): string { return ''; }

            #[Tool(name: 'tool_two', description: 'Second tool')]
            public function toolTwo(int $n): int { return 0; }

            public function notATool(): string { return ''; }
        };

        $schemas = $this->generator->generateFromAgent($agent);
        $this->assertCount(2, $schemas);
    }

    public function test_maps_php_types_to_json_types()
    {
        $agent = new class {
            #[Tool(name: 'mixed_params', description: 'Mixed param types')]
            public function mixedParams(
                string $s, int $i, float $f, bool $b, array $a
            ): string { return ''; }
        };

        $schemas = $this->generator->generateFromAgent($agent);
        $props   = $schemas[0]['function']['parameters']['properties'];

        $this->assertSame('string',  $props['s']['type']);
        $this->assertSame('number',  $props['i']['type']);
        $this->assertSame('number',  $props['f']['type']);
        $this->assertSame('boolean', $props['b']['type']);
        $this->assertSame('array',   $props['a']['type']);
    }

    public function test_returns_empty_for_unannotated_class()
    {
        $agent = new class {
            public function plainMethod(): string { return ''; }
        };

        $schemas = $this->generator->generateFromAgent($agent);
        $this->assertEmpty($schemas);
    }

    public function test_required_list_excludes_optional_params()
    {
        $agent = new class {
            #[Tool(name: 'opt_test', description: 'Optional param test')]
            public function optTest(
                string $required,
                string $optional = 'default'
            ): string { return ''; }
        };

        $schemas  = $this->generator->generateFromAgent($agent);
        $required = $schemas[0]['function']['parameters']['required'];

        $this->assertContains('required', $required);
        $this->assertNotContains('optional', $required);
    }
}
