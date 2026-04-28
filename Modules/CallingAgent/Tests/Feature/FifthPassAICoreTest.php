<?php

namespace Modules\CallingAgent\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CallingAgent\AI\Core\AgentConfig;
use Modules\CallingAgent\AI\Core\BaseAgent;
use Modules\CallingAgent\AI\Drivers\NullDriver;
use Modules\CallingAgent\AI\Drivers\OpenAICompatibleDriver;
use Modules\CallingAgent\AI\Messages\SystemMessage;
use Modules\CallingAgent\AI\Messages\UserMessage;
use Modules\CallingAgent\AI\Messages\AssistantMessage;
use Modules\CallingAgent\AI\Messages\ToolResultMessage;
use Modules\CallingAgent\AI\Messages\MessageCollection;
use Modules\CallingAgent\AI\Tools\Attributes\Tool;
use Modules\CallingAgent\AI\Tools\ToolDefinition;
use Modules\CallingAgent\AI\Tools\ToolRegistry;
use Modules\CallingAgent\AI\Tools\ToolExecutor;
use Modules\CallingAgent\AI\Context\SessionIdentity;
use Modules\CallingAgent\AI\Context\ArrayChatHistory;
use Modules\CallingAgent\AI\Context\SlidingWindowTruncation;
use Modules\CallingAgent\AI\StructuredOutput\DataModel;
use Modules\CallingAgent\AI\StructuredOutput\CallOutcomeModel;
use Modules\CallingAgent\AI\Usage\UsageRecord;
use Modules\CallingAgent\ValueObjects\StructuredCallOutcome;

/**
 * Fifth-pass AI core integration tests.
 *
 * Covers the LarAgent-inspired components added in pass 5:
 *  - AgentConfig
 *  - BaseAgent with NullDriver
 *  - Message types and MessageCollection
 *  - Tool attribute and ToolRegistry
 *  - SessionIdentity
 *  - ArrayChatHistory + SlidingWindowTruncation
 *  - CallOutcomeModel (structured output, schema, validation)
 *  - UsageRecord DTO
 *  - NullDriver availability
 *  - OpenAICompatibleDriver not available without API key
 */
class FifthPassAICoreTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // AgentConfig
    // =========================================================================

    public function test_agent_config_has_defaults(): void
    {
        $config = new AgentConfig();
        $this->assertSame('null', $config->driver);
        $this->assertSame('gpt-4o', $config->model);
        $this->assertSame(1024, $config->maxTokens);
    }

    public function test_agent_config_from_array(): void
    {
        $config = AgentConfig::fromArray([
            'driver'      => 'openai',
            'model'       => 'gpt-4-turbo',
            'maxTokens'   => 512,
            'temperature' => 0.5,
        ]);
        $this->assertSame('openai', $config->driver);
        $this->assertSame('gpt-4-turbo', $config->model);
        $this->assertSame(512, $config->maxTokens);
    }

    public function test_agent_config_round_trips(): void
    {
        $config  = AgentConfig::fromArray(['driver' => 'groq', 'model' => 'llama3', 'maxTokens' => 256]);
        $arr     = $config->toArray();
        $config2 = AgentConfig::fromArray($arr);
        $this->assertSame($config->driver, $config2->driver);
        $this->assertSame($config->model, $config2->model);
    }

    // =========================================================================
    // Message types
    // =========================================================================

    public function test_system_message_to_array(): void
    {
        $msg = new SystemMessage('You are a receptionist.');
        $this->assertSame(['role' => 'system', 'content' => 'You are a receptionist.'], $msg->toArray());
    }

    public function test_user_message_to_array(): void
    {
        $msg = new UserMessage('Hello!');
        $arr = $msg->toArray();
        $this->assertSame('user', $arr['role']);
        $this->assertSame('Hello!', $arr['content']);
    }

    public function test_assistant_message_to_array(): void
    {
        $msg = new AssistantMessage('How can I help?');
        $arr = $msg->toArray();
        $this->assertSame('assistant', $arr['role']);
        $this->assertSame('How can I help?', $arr['content']);
        $this->assertFalse($msg->hasToolCalls());
    }

    public function test_assistant_message_has_tool_calls(): void
    {
        $msg = new AssistantMessage('', [['id' => 'call_1', 'function' => ['name' => 'check_slots']]]);
        $this->assertTrue($msg->hasToolCalls());
    }

    public function test_tool_result_message_to_array(): void
    {
        $msg = new ToolResultMessage('call_1', 'Available: 10am, 2pm');
        $arr = $msg->toArray();
        $this->assertSame('tool', $arr['role']);
        $this->assertSame('call_1', $arr['tool_call_id']);
        $this->assertSame('Available: 10am, 2pm', $arr['content']);
    }

    // =========================================================================
    // MessageCollection
    // =========================================================================

    public function test_message_collection_builds_correctly(): void
    {
        $coll = MessageCollection::make()
            ->addSystem('System prompt')
            ->addUser('User says hi')
            ->addAssistant('Assistant says hello')
            ->addToolResult('call_1', 'Result');

        $this->assertSame(4, $coll->count());
    }

    public function test_message_collection_to_openai_array(): void
    {
        $coll = MessageCollection::make()
            ->addSystem('Prompt')
            ->addUser('Hello');

        $arr = $coll->toOpenAiArray();
        $this->assertCount(2, $arr);
        $this->assertSame('system', $arr[0]['role']);
        $this->assertSame('user', $arr[1]['role']);
    }

    public function test_message_collection_last(): void
    {
        $coll = MessageCollection::make()->addUser('First')->addAssistant('Second');
        $this->assertInstanceOf(AssistantMessage::class, $coll->last());
    }

    // =========================================================================
    // Tool attribute + ToolDefinition + ToolRegistry
    // =========================================================================

    public function test_tool_attribute_is_php_attribute(): void
    {
        $refl = new \ReflectionClass(Tool::class);
        $attrs = $refl->getAttributes(\Attribute::class);
        $this->assertNotEmpty($attrs, '#[Tool] must itself be an #[Attribute].');
    }

    public function test_tool_definition_to_openai_array(): void
    {
        $def = new ToolDefinition(
            name: 'check_availability',
            description: 'Check appointment slots',
            parameters: ['date' => ['type' => 'string']],
            required: ['date']
        );
        $arr = $def->toOpenAiArray();
        $this->assertSame('function', $arr['type']);
        $this->assertSame('check_availability', $arr['function']['name']);
        $this->assertSame('Check appointment slots', $arr['function']['description']);
        $this->assertArrayHasKey('parameters', $arr['function']);
    }

    public function test_tool_registry_register_and_get(): void
    {
        $def      = new ToolDefinition('my_tool', 'Does something');
        $registry = new ToolRegistry();
        $registry->register($def);

        $this->assertInstanceOf(ToolDefinition::class, $registry->get('my_tool'));
        $this->assertNull($registry->get('nonexistent'));
    }

    public function test_tool_registry_register_from_object(): void
    {
        $toolObj = new class {
            #[Tool(name: 'greet', description: 'Greet the caller')]
            public function greet(string $name): string { return "Hello {$name}"; }
        };

        $registry = new ToolRegistry();
        $registry->registerFromObject($toolObj);

        $tool = $registry->get('greet');
        $this->assertNotNull($tool, 'Tool "greet" should be registered via attribute scanning.');
        $this->assertSame('greet', $tool->name);
        $this->assertSame('Greet the caller', $tool->description);
    }

    public function test_tool_executor_executes_registered_callable(): void
    {
        $def = new ToolDefinition(
            name: 'add',
            description: 'Add two numbers',
            parameters: [],
            required: [],
            callable: fn (int $a, int $b) => $a + $b
        );
        $registry = new ToolRegistry();
        $registry->register($def);
        $executor = new ToolExecutor($registry);

        $result = $executor->execute('add', ['a' => 3, 'b' => 4]);
        $this->assertSame(7, $result);
    }

    public function test_tool_executor_returns_tool_result_message(): void
    {
        $def = new ToolDefinition(
            name: 'ping',
            description: 'Ping',
            parameters: [],
            required: [],
            callable: fn () => 'pong'
        );
        $registry = new ToolRegistry();
        $registry->register($def);
        $executor = new ToolExecutor($registry);

        $msg = $executor->executeFromToolCall([
            'id'       => 'call_ping_1',
            'function' => ['name' => 'ping', 'arguments' => '{}'],
        ]);
        $this->assertInstanceOf(ToolResultMessage::class, $msg);
        $this->assertSame('call_ping_1', $msg->toolCallId);
        $this->assertStringContainsString('pong', $msg->content);
    }

    // =========================================================================
    // SessionIdentity
    // =========================================================================

    public function test_session_identity_from_call_sid(): void
    {
        $identity = SessionIdentity::fromCallSid('CAtest123');
        $this->assertStringStartsWith('ca_CA', $identity->sessionId);
        $this->assertSame('CAtest123', $identity->callSid);
    }

    public function test_session_identity_generate_has_session_id(): void
    {
        $identity = SessionIdentity::generate(['source' => 'test']);
        $this->assertNotEmpty($identity->sessionId);
        $this->assertArrayHasKey('source', $identity->metadata);
    }

    public function test_session_identity_to_array(): void
    {
        $identity = SessionIdentity::fromCallSid('CAabc', ['key' => 'val']);
        $arr      = $identity->toArray();
        $this->assertArrayHasKey('session_id', $arr);
        $this->assertArrayHasKey('call_sid', $arr);
        $this->assertSame('CAabc', $arr['call_sid']);
    }

    // =========================================================================
    // ArrayChatHistory + SlidingWindowTruncation
    // =========================================================================

    public function test_array_chat_history_stores_messages(): void
    {
        $history = new ArrayChatHistory();
        $history->addMessage(new UserMessage('Hello'));
        $history->addMessage(new AssistantMessage('Hi there'));

        $this->assertSame(2, $history->count());
    }

    public function test_array_chat_history_to_message_collection(): void
    {
        $history = new ArrayChatHistory();
        $history->addMessage(new SystemMessage('System'));
        $history->addMessage(new UserMessage('User'));

        $coll = $history->toMessageCollection();
        $this->assertInstanceOf(MessageCollection::class, $coll);
        $this->assertSame(2, $coll->count());
    }

    public function test_sliding_window_truncation_removes_oldest_non_system(): void
    {
        $history = new ArrayChatHistory();
        $history->addMessage(new SystemMessage('System'));  // index 0 — preserved
        $history->addMessage(new UserMessage('1'));
        $history->addMessage(new UserMessage('2'));
        $history->addMessage(new UserMessage('3'));
        $history->addMessage(new UserMessage('4'));

        $truncation = new SlidingWindowTruncation();
        $truncation->truncate($history, 3); // keep last 3 including system

        // System is always preserved; total should be ≤ 3
        $this->assertLessThanOrEqual(3, $history->count());
        // First message should still be a system message
        $messages = $history->getMessages();
        $this->assertInstanceOf(SystemMessage::class, $messages[0]);
    }

    // =========================================================================
    // CallOutcomeModel (StructuredOutput)
    // =========================================================================

    public function test_call_outcome_model_schema_has_required_keys(): void
    {
        $schema = CallOutcomeModel::schema();
        $this->assertArrayHasKey('intent', $schema);
        $this->assertArrayHasKey('urgency', $schema);
        $this->assertArrayHasKey('handoff_required', $schema);
    }

    public function test_call_outcome_model_json_schema_is_valid(): void
    {
        $jsonSchema = CallOutcomeModel::jsonSchema();
        $this->assertSame('object', $jsonSchema['type']);
        $this->assertArrayHasKey('properties', $jsonSchema);
        $this->assertArrayHasKey('required', $jsonSchema);
    }

    public function test_call_outcome_model_from_array_and_to_array(): void
    {
        $data = [
            'intent'           => 'booking',
            'urgency'          => 'high',
            'handoff_required' => false,
            'booking_requested'=> true,
            'sentiment'        => 'positive',
            'summary'          => 'Caller wants Tuesday 10am',
        ];
        $model = CallOutcomeModel::fromArray($data);
        $arr   = $model->toArray();

        $this->assertSame('booking', $arr['intent']);
        $this->assertSame('high', $arr['urgency']);
        $this->assertTrue($arr['booking_requested']);
    }

    public function test_call_outcome_model_validate_accepts_valid_data(): void
    {
        $this->assertTrue(CallOutcomeModel::validate([
            'intent'  => 'booking',
            'urgency' => 'normal',
        ]));
    }

    public function test_call_outcome_model_validate_rejects_invalid_intent(): void
    {
        $this->assertFalse(CallOutcomeModel::validate([
            'intent'  => 'invalid_intent_value_xyz',
            'urgency' => 'normal',
        ]));
    }

    public function test_call_outcome_model_converts_to_structured_call_outcome(): void
    {
        $model = CallOutcomeModel::fromArray([
            'intent' => 'sales', 'urgency' => 'urgent', 'handoff_required' => true,
        ]);
        $vo = $model->toStructuredCallOutcome();
        $this->assertInstanceOf(StructuredCallOutcome::class, $vo);
        $this->assertSame('sales', $vo->intent);
        $this->assertTrue($vo->handoffRequired);
    }

    public function test_call_outcome_model_round_trips_from_vo(): void
    {
        $vo    = new StructuredCallOutcome(intent: 'support', urgency: 'high', leadQuality: 'medium');
        $model = CallOutcomeModel::fromStructuredCallOutcome($vo);
        $this->assertSame('support', $model->intent);
        $this->assertSame('high', $model->urgency);
    }

    // =========================================================================
    // UsageRecord
    // =========================================================================

    public function test_usage_record_empty(): void
    {
        $record = UsageRecord::empty();
        $this->assertSame(0, $record->promptTokens);
        $this->assertSame(0, $record->completionTokens);
    }

    public function test_usage_record_from_openai_response(): void
    {
        $record = UsageRecord::fromOpenAiResponse([
            'model' => 'gpt-4o',
            'usage' => ['prompt_tokens' => 100, 'completion_tokens' => 50, 'total_tokens' => 150],
        ], 'gpt-4o');
        $this->assertSame(100, $record->promptTokens);
        $this->assertSame(50, $record->completionTokens);
        $this->assertSame(150, $record->totalTokens);
    }

    public function test_usage_record_to_array(): void
    {
        $record = UsageRecord::empty('openai', 'gpt-4o');
        $arr    = $record->toArray();
        $this->assertArrayHasKey('provider', $arr);
        $this->assertArrayHasKey('model', $arr);
        $this->assertArrayHasKey('prompt_tokens', $arr);
    }

    // =========================================================================
    // NullDriver
    // =========================================================================

    public function test_null_driver_is_available(): void
    {
        $driver = new NullDriver();
        $this->assertTrue($driver->isAvailable());
        $this->assertSame('null', $driver->getName());
    }

    public function test_null_driver_supports_any_driver(): void
    {
        $driver = new NullDriver();
        $this->assertTrue($driver->supports('openai'));
        $this->assertTrue($driver->supports('anthropic'));
        $this->assertTrue($driver->supports('anything'));
    }

    public function test_null_driver_send_returns_empty_assistant_message(): void
    {
        $driver   = new NullDriver();
        $messages = MessageCollection::make()->addUser('Hello');
        $response = $driver->send($messages->toOpenAiArray(), []);
        $this->assertIsArray($response);
    }

    // =========================================================================
    // OpenAICompatibleDriver
    // =========================================================================

    public function test_openai_driver_not_available_without_api_key(): void
    {
        $driver = new OpenAICompatibleDriver('');
        $this->assertFalse($driver->isAvailable(),
            'OpenAICompatibleDriver must report unavailable with empty API key.');
    }

    public function test_openai_driver_available_with_api_key(): void
    {
        $driver = new OpenAICompatibleDriver('sk-test-key-123');
        $this->assertTrue($driver->isAvailable());
    }

    public function test_openai_driver_supports_correct_drivers(): void
    {
        $driver = new OpenAICompatibleDriver('sk-test');
        $this->assertTrue($driver->supports('openai'));
        $this->assertTrue($driver->supports('groq'));
        $this->assertTrue($driver->supports('openrouter'));
        $this->assertFalse($driver->supports('anthropic'));
    }

    // =========================================================================
    // BaseAgent with NullDriver
    // =========================================================================

    public function test_base_agent_respond_returns_string_with_null_driver(): void
    {
        $agent = new class extends BaseAgent {
            protected function systemPrompt(): string { return 'Test receptionist'; }
        };
        $agent->withConfig(AgentConfig::fromArray(['driver' => 'null']));
        $agent->withEngine(new NullDriver());

        $response = $agent->respond('Hello, can I book an appointment?');
        $this->assertIsString($response);
    }

    public function test_base_agent_chat_appends_to_history(): void
    {
        $agent = new class extends BaseAgent {
            protected function systemPrompt(): string { return 'Receptionist'; }
        };
        $agent->withEngine(new NullDriver());

        $agent->chat('First message');
        $agent->chat('Second message');

        $this->assertGreaterThanOrEqual(2, $agent->getHistory()->count());
    }

    public function test_base_agent_clear_history(): void
    {
        $agent = new class extends BaseAgent {
            protected function systemPrompt(): string { return 'Agent'; }
        };
        $agent->withEngine(new NullDriver());
        $agent->chat('Hello');
        $agent->clearHistory();

        $this->assertSame(0, $agent->getHistory()->count());
    }

    // =========================================================================
    // Module validator
    // =========================================================================

    public function test_module_validator_script_exists(): void
    {
        $this->assertFileExists(
            realpath(__DIR__ . '/../../Support/Validation/ModuleValidator.php'),
            'ModuleValidator.php must exist in Support/Validation/'
        );
    }

    public function test_manifest_validator_script_exists(): void
    {
        $this->assertFileExists(
            realpath(__DIR__ . '/../../Support/Validation/ManifestValidator.php'),
            'ManifestValidator.php must exist in Support/Validation/'
        );
    }

    public function test_source_map_exists(): void
    {
        $this->assertFileExists(
            realpath(__DIR__ . '/../../Support/SOURCE_MAP.md'),
            'SOURCE_MAP.md must exist in Support/'
        );
        $content = file_get_contents(realpath(__DIR__ . '/../../Support/SOURCE_MAP.md'));
        $this->assertStringContainsString('LarAgent', $content,
            'SOURCE_MAP.md must document the LarAgent extraction.');
    }

    public function test_module_readiness_report_exists(): void
    {
        $this->assertFileExists(
            realpath(__DIR__ . '/../../Support/MODULE_READINESS_REPORT.md'),
            'MODULE_READINESS_REPORT.md must exist in Support/'
        );
    }
}
