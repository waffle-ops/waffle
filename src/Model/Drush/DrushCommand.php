<?php

namespace Waffle\Model\Drush;

use Symfony\Component\Process\Process;

class DrushCommand
{

    /**
     * @var string[]
     */
    private $args = [];

    /**
     *
     */
    public function __construct(array $args = null)
    {
        if (!empty($args)) {
            $this->args = $args;
        }
    }

    protected function setArgs(array $args)
    {
        $this->args = $args;
    }

    public function setup($input = '')
    {
        // @todo: Add support to prefix all shell command calls with `command_prefix` in .waffle.yml
        $args = array_unshift($this->args, 'drush');
        $process = new Process($this->args);

        if (!empty($input)) {
            $process->setInput($input);
        }

        // TODO Check for error codes / standard errors.

        return $process;
    }

    public function run($input = '')
    {
        $process = $this->setup($input);
        $process->run();

        // TODO Check for error codes / standard errors.

        return $process;
    }
}
