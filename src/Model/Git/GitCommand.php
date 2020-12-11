<?php

namespace Waffle\Model\Git;

use Symfony\Component\Process\Process;
use Waffle\Traits\ConfigTrait;

class GitCommand
{
    use ConfigTrait;

    /**
     * @var string[]
     */
    private $args = [];

    /**
     *
     */
    public function __construct(array $args)
    {
        trigger_error(sprintf('Class %s is deprecated and will be removed in the next release. ', __CLASS__));

        $this->args = $args;
    }

    public function setup($input = '')
    {
        $args = array_unshift($this->args, 'git');
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
