<?php

namespace Waffle\Model\Cli;

use Symfony\Component\Process\Process;

class BaseCliCommand
{

    /**
     * @var string[]
     */
    private $args = [];

    /**
     * @var Process
     */
    private $process;

    /**
     * Constructor
     *
     * @param string[] The Arguments.
     */
    public function __construct(array $args)
    {
        if (empty($args)) {
            throw new \Exception('Invalid Arguments: You must pass at least one argument.');
        }

        $this->process = new Process($args);
    }

    /**
     * Gets the process.
     *
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }
}
