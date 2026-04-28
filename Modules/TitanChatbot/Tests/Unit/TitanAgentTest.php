<?php
namespace Modules\TitanChatbot\Tests\Unit;
use PHPUnit\Framework\TestCase;
use Modules\TitanChatbot\AI\Core\TitanAgent;

class TitanAgentTest extends TestCase
{
    public function test_can_set_instructions_fluently()
    {
        $agent = new class extends TitanAgent {
            protected string $instructions = 'Default instructions.';
        };

        $result = $agent->withInstructions('New instructions.');
        $this->assertSame('New instructions.', $result->instructions());
    }

    public function test_for_session_returns_same_instance_type()
    {
        $agent  = new class extends TitanAgent {};
        $result = $agent->forSession('sess-abc');
        $this->assertInstanceOf(get_class($agent), $result);
    }

    public function test_discover_tools_returns_empty_when_disabled()
    {
        $agent = new class extends TitanAgent {
            protected bool $toolsEnabled = false;
        };
        $this->assertEmpty($agent->discoverTools());
    }

    public function test_instructions_returns_instructions_string()
    {
        $agent = new class extends TitanAgent {
            protected string $instructions = 'Test instruction.';
        };
        $this->assertSame('Test instruction.', $agent->instructions());
    }
}
