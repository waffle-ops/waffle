<?php

namespace Waffles\Model\Dependency;

use Symfony\Component\Process\Process;

class ComposerDependency extends CliDependency
{

    public function __construct()
    {
        parent::__construct(['composer', '--version']);
    }
}
