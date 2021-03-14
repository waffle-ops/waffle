<?php

namespace Waffle\Model\Build;

interface BackendHandlerInterface
{

    /**
     * Handles the backend build of the project.
     *
     * @return void
     */
    public function handle();
}
