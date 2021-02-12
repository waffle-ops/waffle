<?php

namespace Waffle\Helper;

use Symfony\Component\Process\Process;
use Waffle\Model\IO\IO;
use Waffle\Traits\ConfigTrait;
use Waffle\Model\IO\IOStyle;

class CliHelper
{
    use ConfigTrait;
    
    /**
     * Defines the Input/Output helper object.
     *
     * @var IOStyle
     */
    protected $io;
    
    /**
     * Creates a new CliHelper instance.
     *
     * @param IO $io
     */
    public function __construct($io = null)
    {
        if (empty($io)) {
            $this->io = IO::getInstance()->getIO();
        } else {
            $this->io = $io;
        }
    }
    
    /**
     *
     * @param $message
     * @param $command
     *
     * @return string
     */
    public function message($message, $command)
    {
        $this->io->section($message);
        if (is_string($command)) {
            $this->io->writeln($command);
        } elseif ($command instanceof Process) {
            $this->io->writeln($command->getCommandLine());
        }
        $this->io->newLine();
        
        $output = $this->getOutput($command);
        $this->io->writeln($output);
        
        return $output;
    }
    
    /**
     * Get the output of a command
     *
     * @param string|Process $command
     * @param bool $withRun
     * @param bool $withError
     *
     * @return string
     */
    public function getOutput($command, $withRun = true, $withError = true): string
    {
        $process = $this->setup($command);
    
        if ($withRun && !$process->isStarted() && !$process->isRunning()) {
            $process->run();
        }
        
        // Lots of commands (ex: composer) seem to use both channels for normal output so we
        // combine them so that nothing is hidden.
        $output = '';
        if ($withError) {
            $output .= $process->getErrorOutput() . "\n\r";
        }
        $output .= $process->getOutput();
        if (!empty($output)) {
            return $output;
        }
        
        // We didn't get anything back, so try to determine exit code instead.
        $output = $process->getExitCodeText();
        if (!empty($output)) {
            return $output;
        }
        
        return 'NO OUTPUT';
    }
    
    /**
     * Setup the process based on the type of passed command.
     *
     * @param $command
     *
     * @return Process|null
     */
    public function setup($command): ?Process
    {
        $process = null;
        if (is_string($command)) {
            trigger_error(sprintf(
                'Using strings for function %s::%s() is deprecated and will be removed in the next release.',
                __CLASS__,
                __FUNCTION__
            ));
            
            $process = Process::fromShellCommandline($command);
        } else {
            if ($command instanceof Process) {
                $process = $command;
            }
        }
        
        return $process;
    }
    
    /**
     * Exits the process if a command returns a non-zero exit code and dumps debug information.
     *
     * @param $command
     * @param string $error_message
     *
     * @return Process|null
     */
    public function failIfError(
        $command,
        $error_message = 'Error when running process.'
    ): ?Process {
        $process = $this->setup($command);
        
        if (!$process->isStarted() && !$process->isRunning()) {
            $process->run();
        }
        
        if (empty($process->getExitCode())) {
            return $process;
        }
        
        $this->io->error($error_message);
        $this->dumpProcess($process);
        exit(1);
    }
    
    /**
     * Gets full process information for debugging.
     *
     * @param $process
     */
    public function dumpProcess($process)
    {
        $this->io->writeln('Command:');
        $this->io->writeln($process->getCommandLine());
        $this->io->writeln('Exit Code:');
        $this->io->writeln($process->getExitCode());
        $this->io->writeln('Error Output:');
        $this->io->writeln($process->getErrorOutput());
        $this->io->writeln('Standard Output:');
        $this->io->writeln($process->getOutput());
    }
    
    /**
     * Output a process after running or fail if it throws an error.
     *
     * @param $command
     * @param string $error_message
     *
     * @return Process|null
     */
    public function outputOrFail($command, $error_message = 'Error when running process.'): ?Process
    {
        $process = $this->failIfError($command, $error_message);
        $this->io->writeln($this->getOutput($process));
        return $process;
    }
}
