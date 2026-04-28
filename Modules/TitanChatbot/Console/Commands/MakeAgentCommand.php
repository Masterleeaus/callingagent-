<?php

namespace Modules\TitanChatbot\Console\Commands;

use Illuminate\Console\Command;

class MakeAgentCommand extends Command
{
    protected $signature   = 'titan-chatbot:make-agent {name : Agent class name}';
    protected $description = 'Scaffold a new TitanChatbot agent class';

    public function handle(): int
    {
        $name = $this->argument('name');
        $name = ucfirst(str_replace(['agent', 'Agent'], '', $name)) . 'Agent';

        $path = __DIR__ . '/../../AI/Agents/' . $name . '.php';

        if (file_exists($path)) {
            $this->error("Agent {$name} already exists at {$path}");
            return Command::FAILURE;
        }

        $stub = <<<PHP
<?php

namespace Modules\TitanChatbot\AI\Agents;

use Modules\TitanChatbot\AI\Core\TitanAgent;

class {$name} extends TitanAgent
{
    protected string \$instructions = 'You are a helpful assistant.';

    protected int \$maxHistoryMessages = 20;
}
PHP;

        file_put_contents($path, $stub);
        $this->info("Created agent: {$path}");
        return Command::SUCCESS;
    }
}
