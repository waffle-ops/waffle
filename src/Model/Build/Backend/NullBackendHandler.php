<?php

namespace Waffle\Model\Build\Backend;

use Waffle\Model\Build\BackendHandlerInterface;

class NullBackendHandler implements BackendHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        // Intentionally empty.
    }
}
