<?php

namespace Waffle\Queue;

interface QueueInterface
{

    /**
     * This interface is a means to standardize how we use queues in Waffle.
     * The native SplQueue has push() and pop() methods, but they end up
     * treating the data as a stack instead of a queue.
     *
     * Since the maintainers are human and know that they will not always
     * remember to instead use enqueue() and dequeue(), we are providing our
     * own interface. It's also a good place for us to easily to extend in the
     * future!
     */

    /**
     * Pushes an object into the queue
     *
     * @param mixed $value
     *   The item to be added to the queue.
     *
     * @return void
     */
    public function push($value);

    /**
     * Removes and returns the first element of the queue.
     *
     * @return mixed
     */
    public function pop();
}
