<?php

namespace Waffle\Model\Build;

class NullBuildHandler implements BuildHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        // Intentionally empty.
    }
}
