<?php

namespace Waffle\Queue;

use SplQueue;

class Queue extends SplQueue implements QueueInterface
{

    /**
     * {@inheritDoc}
     */
    public function push($value)
    {
        $this->enqueue($value);
    }

    /**
     * {@inheritDoc}
     */
    public function pop()
    {
        return $this->dequeue();
    }
}
