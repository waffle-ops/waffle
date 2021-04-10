<?php

namespace Waffle\Model\Cli;

use Exception;
use Symfony\Component\Process\Process;
use Waffle\Model\Context\Context;

class BaseCliCommand
{

    /**
     * @var Process
     */
    private $process;

    /**
     * @var Context
     */
    protected $context;

    /**
     * Constructor
     *
     * @param Context $context
     *   The application context / config.
     * @param string[] $args
     *   The command / arguments to execute.
     *
     * @throws Exception
     */
    public function __construct(Context $context, array $args)
    {
        $this->context = $context;

        if (empty($args)) {
            throw new Exception('Invalid Arguments: You must pass at least one argument.');
        }

        /**
         * @todo: Each command type should have its own prefix that can be
         * defined in config. Leaving this for now for backwards compatability.
         */
        if (!empty($this->context->getCommandPrefix())) {
            array_unshift($args, $this->context->getCommandPrefix());
        }

        $directory = $this->context->getTaskWorkingDirectory();
        $env = $this->context->getEnvironmentVariables();
        $this->process = new Process($args, $directory, $env);

        /**
         * @todo: Similar to above. Consider revisiting how this works in the
         * future.
         */
        if (!empty($this->context->getTimeout())) {
            $this->process->setTimeout($this->context->getTimeout());
        }
    }

    /**
     * Gets the process.
     *
     * @return Process
     */
    public function getProcess(): Process
    {
        return $this->process;
    }
}
