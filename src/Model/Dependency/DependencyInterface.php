<?php

namespace Waffle\Model\Dependency;

interface DependencyInterface
{
    public function isMet();

    public function getInstructions();

    public function attemptInstall();
}
