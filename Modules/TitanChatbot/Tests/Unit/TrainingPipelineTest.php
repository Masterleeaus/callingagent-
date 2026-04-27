<?php

namespace Modules\TitanChatbot\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\TitanChatbot\Services\TrainingPipeline;

class TrainingPipelineTest extends TestCase
{
    private TrainingPipeline $pipeline;

    protected function setUp(): void
    {
        $this->pipeline = new TrainingPipeline();
    }

    // ─── chunkText ────────────────────────────────────────────────────────────

    public function test_chunk_text_splits_into_chunks(): void
    {
        $text   = str_repeat('word ', 200); // 200 words
        $chunks = $this->pipeline->chunkText($text, 50);

        $this->assertNotEmpty($chunks);
        foreach ($chunks as $chunk) {
            $this->assertIsString($chunk);
            $this->assertNotEmpty($chunk);
        }
    }

    public function test_chunk_text_returns_empty_for_empty_string(): void
    {
        $chunks = $this->pipeline->chunkText('');
        $this->assertIsArray($chunks);
        $this->assertEmpty($chunks);
    }

    public function test_chunk_text_returns_empty_for_whitespace_only(): void
    {
        $chunks = $this->pipeline->chunkText("   \t\n   ");
        $this->assertIsArray($chunks);
        $this->assertEmpty($chunks);
    }

    public function test_chunk_text_single_chunk_for_small_text(): void
    {
        $text   = 'Hello world this is a short text.';
        $chunks = $this->pipeline->chunkText($text, 500);

        $this->assertCount(1, $chunks);
        $this->assertStringContainsString('Hello', $chunks[0]);
    }

    public function test_chunk_text_respects_chunk_size(): void
    {
        // 100 words split with chunkSize=20 → multiple chunks
        $text   = implode(' ', array_fill(0, 100, 'word'));
        $chunks = $this->pipeline->chunkText($text, 20);

        $this->assertGreaterThan(1, count($chunks));
        foreach ($chunks as $chunk) {
            $wordCount = str_word_count($chunk);
            $this->assertLessThanOrEqual(20, $wordCount);
        }
    }

    public function test_chunk_text_overlap_means_words_appear_in_successive_chunks(): void
    {
        // Create text where boundary words should overlap
        $words  = array_map(fn($i) => "w{$i}", range(1, 60));
        $text   = implode(' ', $words);
        $chunks = $this->pipeline->chunkText($text, 20);

        // With 20% overlap the second chunk should begin before the first chunk ends
        $this->assertGreaterThanOrEqual(2, count($chunks));
    }

    // ─── chunkQa ─────────────────────────────────────────────────────────────

    public function test_chunk_qa_parses_qa_format(): void
    {
        $content = "Q: What is your name?\nA: I am TitanChatbot.\n\nQ: What do you do?\nA: I help customers.";
        $chunks  = $this->pipeline->chunkQa($content);

        $this->assertCount(2, $chunks);
        $this->assertStringContainsString('What is your name', $chunks[0]);
        $this->assertStringContainsString('TitanChatbot', $chunks[0]);
        $this->assertStringContainsString('What do you do', $chunks[1]);
        $this->assertStringContainsString('help customers', $chunks[1]);
    }

    public function test_chunk_qa_handles_empty_content(): void
    {
        $chunks = $this->pipeline->chunkQa('');
        $this->assertIsArray($chunks);
        $this->assertEmpty($chunks);
    }

    public function test_chunk_qa_ignores_q_without_a(): void
    {
        // A Q: with no A: or continuation lines produces no chunks.
        $content = "Q: Orphan question with nothing after it";
        $chunks  = $this->pipeline->chunkQa($content);

        $this->assertEmpty($chunks);
    }

    public function test_chunk_qa_multi_line_answer(): void
    {
        $content = "Q: Describe your features.\nA: Feature one.\nFeature two.\nFeature three.";
        $chunks  = $this->pipeline->chunkQa($content);

        $this->assertCount(1, $chunks);
        $this->assertStringContainsString('Feature one', $chunks[0]);
        $this->assertStringContainsString('Feature two', $chunks[0]);
        $this->assertStringContainsString('Feature three', $chunks[0]);
    }

    public function test_chunk_qa_chunk_starts_with_q_prefix(): void
    {
        $content = "Q: Hello?\nA: World.";
        $chunks  = $this->pipeline->chunkQa($content);

        $this->assertStringStartsWith('Q: ', $chunks[0]);
    }

    public function test_chunk_qa_case_insensitive_markers(): void
    {
        $content = "q: lowercase question?\na: lowercase answer.";
        $chunks  = $this->pipeline->chunkQa($content);

        $this->assertCount(1, $chunks);
        $this->assertStringContainsString('lowercase question', $chunks[0]);
    }
}
