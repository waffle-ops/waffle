<?php

namespace Waffle\Tests\Command\Command;

use Waffle\Queue\Queue;
use Waffle\Tests\TestCase;

class QueueTest extends TestCase
{
    public function testPush()
    {
        $queue = new Queue();
        $queue->push('abc');
        $queue->push('def');
        $queue->push('hij');

        $this->assertEquals(3, $queue->count());
    }

    public function testPop()
    {
        $queue = new Queue();
        $queue->push('abc');
        $queue->push('def');
        $queue->push('hij');

        $this->assertEquals('abc', $queue->pop());
        $this->assertEquals('def', $queue->pop());
        $this->assertEquals('hij', $queue->pop());
    }
}
