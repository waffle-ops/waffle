<?php

namespace Waffle\Model\IO;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Process\Process;
use Waffle\Model\Output\Runner;

class IOStyle extends SymfonyStyle implements StyleInterface
{
    // See https://github.com/symfony/console/blob/5.x/Style/SymfonyStyle.php.
    
    /**
     *
     * @param $message
     * @param $command
     *
     * @return string
     */
    public function message($message, $command)
    {
        $this->section($message);
        if (is_string($command)) {
            $this->writeln($command);
        } elseif ($command instanceof Process) {
            $this->writeln($command->getCommandLine());
        }
        $this->newLine();
        
        $output = $this->getOutput($command);
        $this->writeln($output);
        
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
    public function getOutput($command, $withRun = true, $withError = true)
    {
        $process = $this->setup($command);
        
        if ($withRun) {
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
    public function setup($command)
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
        
        $this->error($error_message);
        $this->writeln('Command:');
        $this->writeln($process->getCommandLine());
        $this->writeln('Exit Code:');
        $this->writeln($process->getExitCode());
        $this->writeln('Error Output:');
        $this->writeln($process->getErrorOutput());
        $this->writeln('Standard Output:');
        $this->writeln($process->getOutput());
        exit(1);
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
        $this->writeln($this->getOutput($process));
        return $process;
    }
}
