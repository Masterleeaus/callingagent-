<?php

namespace Modules\TitanChatbot\Console\Commands;

use Illuminate\Console\Command;

class MakeToolCommand extends Command
{
    protected $signature   = 'titan-chatbot:make-tool {name : Tool class name}';
    protected $description = 'Scaffold a new TitanChatbot tool class';

    public function handle(): int
    {
        $name = $this->argument('name');
        $name = ucfirst($name) . (str_ends_with($name, 'Tool') ? '' : 'Tool');

        $path = __DIR__ . '/../../AI/Tools/' . $name . '.php';

        if (file_exists($path)) {
            $this->error("Tool {$name} already exists at {$path}");
            return Command::FAILURE;
        }

        $stub = <<<PHP
<?php

namespace Modules\TitanChatbot\AI\Tools;

use Modules\TitanChatbot\AI\Attributes\Tool;
use Modules\TitanChatbot\AI\Attributes\Desc;

class {$name}
{
    #[Tool(name: 'example_tool', description: 'Describe what this tool does')]
    public function run(
        #[Desc('The input parameter')] string \$input
    ): string {
        return 'Result for: ' . \$input;
    }
}
PHP;

        file_put_contents($path, $stub);
        $this->info("Created tool: {$path}");
        return Command::SUCCESS;
    }
}
