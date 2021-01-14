<?php

namespace Waffle\Model\Cli\Runner;

use Waffle\Model\Cli\GitCommand;
use Symfony\Component\Process\Process;
use Exception;

class Git extends BaseRunner
{
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Adds all pending changes to index.
     *
     * @return Process
     * @throws Exception
     */
    public function addAll(): Process
    {
        $command = new GitCommand(['add', '-A']);
        return $command->getProcess();
    }
    
    /**
     * Check git status.
     *
     * @return Process
     * @throws Exception
     */
    public function statusShort(): Process
    {
        $command = new GitCommand(['status', '--short']);
        return $command->getProcess();
    }
    
    /**
     * Check if current git repo has any pending uncommitted changes.
     *
     * @return bool
     * @throws Exception
     */
    public function hasPendingChanges(): bool
    {
        $process = $this->statusShort();
        $process->run();
        return !empty($process->getOutput());
    }
    
    /**
     * Commit staged changes.
     *
     * @param string $message
     *
     * @return Process
     * @throws Exception
     */
    public function commit($message = 'Committing changes.'): Process
    {
        $command = new GitCommand(['commit', "--message={$message}"]);
        return $command->getProcess();
    }
}
