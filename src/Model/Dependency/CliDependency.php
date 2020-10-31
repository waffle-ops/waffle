<?php

namespace Waffle\Model\Dependency;

use Symfony\Component\Process\Process;

class CliDependency extends DependencyBase
{

    /**
     * @var string[]
     */
    private $args = [];

    /**
     *
     */
    public function __construct(array $args)
    {
        // TODO Validate all strings ...
        $this->args = $args;
    }

    public function isMet()
    {
        $process = new Process($this->args);

        try {
            $process->mustRun();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getInstructions()
    {
        // TODO: Implement getInstructions() method.
    }

    public function attemptInstall()
    {
        // TODO: Implement attemptInstall() method.
    }
}
