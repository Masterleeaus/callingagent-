<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Core;

use Modules\CallingAgent\AI\Context\ArrayChatHistory;
use Modules\CallingAgent\AI\Context\ChatHistoryInterface;
use Modules\CallingAgent\AI\Messages\MessageCollection;
use Modules\CallingAgent\AI\Tools\ToolRegistry;
use Modules\CallingAgent\AI\Usage\UsageRecord;
use Modules\CallingAgent\Events\AI\AfterAgentSend;
use Modules\CallingAgent\Events\AI\BeforeAgentSend;
use Modules\CallingAgent\Events\AI\EngineError;

abstract class BaseAgent
{
    protected AgentConfig $config;

    protected ChatHistoryInterface $history;

    protected ToolRegistry $registry;

    protected ?AgentEngine $engine = null;

    public function __construct()
    {
        $this->config = new AgentConfig();
        $this->history = new ArrayChatHistory();
        $this->registry = new ToolRegistry();
    }

    abstract protected function systemPrompt(): string;

    public function withConfig(AgentConfig $config): static
    {
        $clone = clone $this;
        $clone->config = $config;

        return $clone;
    }

    public function withEngine(AgentEngine $engine): static
    {
        $clone = clone $this;
        $clone->engine = $engine;

        return $clone;
    }

    public function respond(string $userMessage, array $context = []): string
    {
        $systemPrompt = $this->systemPrompt();

        $collection = MessageCollection::make();

        if ($systemPrompt !== '') {
            $collection = $collection->addSystem($systemPrompt);
        }

        foreach ($this->history->getMessages() as $msg) {
            $collection = $collection->add($msg);
        }

        $collection = $collection->addUser($userMessage);

        if ($this->engine === null) {
            return '';
        }

        try {
            if (class_exists(\Illuminate\Support\Facades\Event::class)) {
                \Illuminate\Support\Facades\Event::dispatch(
                    new BeforeAgentSend($collection, $this->config),
                );
            }

            $response = $this->engine->generate($collection, $this->config);

            $usage = $this->engine instanceof \Modules\CallingAgent\AI\Drivers\DriverInterface
                ? $this->engine->getUsage()
                : UsageRecord::empty();

            if (class_exists(\Illuminate\Support\Facades\Event::class)) {
                \Illuminate\Support\Facades\Event::dispatch(
                    new AfterAgentSend($collection, $this->config, $response, $usage),
                );
            }

            return $response->content;
        } catch (\Throwable $e) {
            if (class_exists(\Illuminate\Support\Facades\Event::class)) {
                \Illuminate\Support\Facades\Event::dispatch(
                    new EngineError($e, $this->config),
                );
            }

            throw $e;
        }
    }

    public function chat(string $userMessage, array $context = []): string
    {
        $answer = $this->respond($userMessage, $context);

        $this->history->addMessage(new \Modules\CallingAgent\AI\Messages\UserMessage($userMessage));
        $this->history->addMessage(new \Modules\CallingAgent\AI\Messages\AssistantMessage($answer));

        if ($this->history->count() > $this->config->historyLimit) {
            $this->history->truncate($this->config->historyLimit);
        }

        return $answer;
    }

    public function getHistory(): ChatHistoryInterface
    {
        return $this->history;
    }

    public function clearHistory(): void
    {
        $this->history->clear();
    }
}
