<?php
namespace Modules\TitanChatbot\Tests\Unit;
use PHPUnit\Framework\TestCase;
use Modules\TitanChatbot\AI\Memory\StorageManager;
use Modules\TitanChatbot\AI\Memory\Drivers\InMemoryMemoryDriver;

class StorageManagerTest extends TestCase
{
    protected function setUp(): void
    {
        InMemoryMemoryDriver::flush();
    }

    public function test_in_memory_driver_stores_and_retrieves()
    {
        $manager = new StorageManager('in_memory');
        $manager->push('sess1', 'user', 'Hello');
        $manager->push('sess1', 'assistant', 'Hi there!');

        $messages = $manager->get('sess1');
        $this->assertCount(2, $messages);
        $this->assertSame('user', $messages[0]['role']);
        $this->assertSame('Hello', $messages[0]['content']);
    }

    public function test_all_with_limit_truncates_to_last_n()
    {
        $manager = new StorageManager('in_memory');
        for ($i = 1; $i <= 10; $i++) {
            $manager->push('sess2', 'user', "message {$i}");
        }

        $messages = $manager->all('sess2', 3);
        $this->assertCount(3, $messages);
        $this->assertSame('message 8', $messages[0]['content']);
    }

    public function test_forget_clears_session()
    {
        $manager = new StorageManager('in_memory');
        $manager->push('sess3', 'user', 'to be forgotten');
        $manager->forget('sess3');

        $this->assertEmpty($manager->get('sess3'));
    }

    public function test_different_sessions_are_isolated()
    {
        $manager = new StorageManager('in_memory');
        $manager->push('sessA', 'user', 'A message');
        $manager->push('sessB', 'user', 'B message');

        $this->assertCount(1, $manager->get('sessA'));
        $this->assertCount(1, $manager->get('sessB'));
        $this->assertSame('A message', $manager->get('sessA')[0]['content']);
    }

    public function test_file_driver_stores_and_retrieves()
    {
        $storageDir = dirname(__DIR__, 2) . '/Tests/storage_test_' . uniqid();
        $driver     = new \Modules\TitanChatbot\AI\Memory\Drivers\FileMemoryDriver($storageDir);

        $driver->push('fs1', 'user', 'File message');
        $messages = $driver->get('fs1');

        $this->assertCount(1, $messages);
        $this->assertSame('File message', $messages[0]['content']);

        // cleanup
        $driver->forget('fs1');
        $this->assertEmpty($driver->get('fs1'));

        @rmdir($storageDir);
    }
}
