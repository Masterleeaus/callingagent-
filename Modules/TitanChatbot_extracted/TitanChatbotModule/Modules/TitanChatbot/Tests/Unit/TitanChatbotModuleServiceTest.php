<?php
namespace Modules\TitanChatbot\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\TitanChatbot\Services\TitanChatbotModuleService;

class TitanChatbotModuleServiceTest extends TestCase
{
    public function testSummaryHasModuleName(): void
    {
        $this->assertSame('TitanChatbot', (new TitanChatbotModuleService())->summary()['module']);
    }
}
