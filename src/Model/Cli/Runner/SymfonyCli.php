<?php

namespace Waffle\Model\Cli\Runner;

use Symfony\Component\Process\Process;
use Waffle\Model\Cli\BaseCliCommand;
use Exception;
use Waffle\Model\Cli\SymfonyCliCommand;
use Waffle\Helper\CliHelper;

class SymfonyCli extends BaseRunner
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Checks if symfony CLI is installed.
     *
     * @return bool
     * @throws Exception
     */
    public function isInstalled(): bool
    {
        // @todo: run this on construct and/or cache the result?
        
        $command = new BaseCliCommand(['which', 'symfony']);
        $process = $command->getProcess();
        $cliHelper = new CliHelper();
        $output = $cliHelper->getOutput($process);
        return !empty($output);
    }
    
    /**
     * Runs security:check for a composer.lock.
     *
     * @param string $directory
     *
     * @return Process
     * @throws Exception
     */
    public function securityCheck($directory = ''): Process
    {
        if (empty($directory)) {
            $directory = $this->config->getComposerPath();
        }
        
        $command = new SymfonyCliCommand(
            [
                'security:check',
                '--dir=' . $directory,
            ]
        );
        
        return $command->getProcess();
    }
}
