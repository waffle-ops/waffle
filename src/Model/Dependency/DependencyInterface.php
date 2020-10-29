<?php

namespace Waffles\Model\Dependency;

interface DependencyInterface
{
    public function isMet();

    public function getInstructions();

    public function attemptInstall();
}
