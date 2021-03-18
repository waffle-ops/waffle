<?php

namespace Waffle\Model\Build;

interface BuildHandlerInterface
{

    /**
     * Handles build steps of the project.
     *
     * @return void
     */
    public function handle();
}
