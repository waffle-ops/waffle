<?php

namespace Waffles\Model\Drush;

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
    public function __construct(array $args) {
        $this->args = $args;
    }

    public function run($input = '') {
        $args = array_unshift($this->args, 'drush');
        $process = new Process($this->args);

        if (!empty($input)) {
            $process->setInput($input);
        }

        $process->run();

        // TODO Check for error codes / standard errors.

        return $process;
    }

}
