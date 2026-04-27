<?php
namespace Modules\CallingAgent\Tests\Unit; use PHPUnit\Framework\TestCase; use Modules\CallingAgent\AI\Agents\ReceptionistAgent; class ReceptionistAgentTest extends TestCase { public function test_fallback_booking(): void { $this->assertStringContainsString('bookings',(new ReceptionistAgent)->respond('book appointment')); } }
