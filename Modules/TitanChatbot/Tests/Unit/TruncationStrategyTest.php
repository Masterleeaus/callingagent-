<?php
namespace Modules\TitanChatbot\Tests\Unit;
use PHPUnit\Framework\TestCase;
use Modules\TitanChatbot\AI\Memory\Truncation\SimpleTruncationStrategy;
use Modules\TitanChatbot\AI\Memory\Truncation\SummarizationStrategy;

class TruncationStrategyTest extends TestCase
{
    public function test_simple_strategy_keeps_last_n_messages()
    {
        $strategy = new SimpleTruncationStrategy();
        $messages = array_map(fn($i) => ['role' => 'user', 'content' => "msg {$i}"], range(1, 20));

        $truncated = $strategy->truncate($messages, 5);
        $this->assertCount(5, $truncated);
        $this->assertSame('msg 16', $truncated[0]['content']);
        $this->assertSame('msg 20', $truncated[4]['content']);
    }

    public function test_simple_strategy_preserves_system_message()
    {
        $strategy = new SimpleTruncationStrategy();
        $messages = array_merge(
            [['role' => 'system', 'content' => 'System instructions']],
            array_map(fn($i) => ['role' => 'user', 'content' => "msg {$i}"], range(1, 15))
        );

        $truncated = $strategy->truncate($messages, 5);

        // System msg + 5 most recent
        $this->assertCount(6, $truncated);
        $this->assertSame('system', $truncated[0]['role']);
    }

    public function test_simple_strategy_does_nothing_when_under_limit()
    {
        $strategy  = new SimpleTruncationStrategy();
        $messages  = [['role' => 'user', 'content' => 'one'], ['role' => 'assistant', 'content' => 'two']];

        $truncated = $strategy->truncate($messages, 10);
        $this->assertCount(2, $truncated);
    }

    public function test_summarization_strategy_inserts_summary_message()
    {
        $strategy = new SummarizationStrategy(keepRecent: 3);
        $messages = array_map(fn($i) => ['role' => 'user', 'content' => "msg {$i}"], range(1, 10));

        $truncated = $strategy->truncate($messages, 5);

        // Should have: summary system msg + 3 recent messages
        $this->assertCount(4, $truncated);

        // First message should be the summary
        $this->assertSame('system', $truncated[0]['role']);
        $this->assertStringContainsString('[Conversation summary', $truncated[0]['content']);
    }

    public function test_summarization_strategy_keeps_recent_messages()
    {
        $strategy = new SummarizationStrategy(keepRecent: 3);
        $messages = array_map(fn($i) => ['role' => 'user', 'content' => "msg {$i}"], range(1, 10));

        $truncated = $strategy->truncate($messages, 5);

        // Last 3 messages should be msg 8, 9, 10
        $this->assertSame('msg 8',  $truncated[1]['content']);
        $this->assertSame('msg 9',  $truncated[2]['content']);
        $this->assertSame('msg 10', $truncated[3]['content']);
    }
}
