<?php

namespace Waffle\Model\Output;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

// @todo: Is there a better name for this?
// @todo: Should this be a helper instead of a model?

/**
 * Utility class for running processes and displaying formatted output.
 *
 * @package Waffle\Model\Output
 */
class Runner
{

    /**
     *
     */
    public function __construct()
    {
        trigger_error(sprintf('Class %s is deprecated and will be removed in the next release.', __CLASS__));
    }

    /**
     *
     * @param SymfonyStyle $io
     * @param $message
     * @param $command
     * @return string
     */
    public static function message(SymfonyStyle $io, $message, $command)
    {
        trigger_error(sprintf(
            'Function %s::%s() is deprecated and will be removed in the next release.',
            __CLASS__,
            __FUNCTION__
        ));

        $io->section($message);
        if (is_string($command)) {
            $io->writeln($command);
        } elseif ($command instanceof Process) {
            $io->writeln($command->getCommandLine());
        }
        $io->newLine();

        $output = Runner::getOutput($command);
        $io->writeln($output);

        return $output;
    }

    /**
     * Get the output of a command
     *
     * @param string|Process $command
     * @param bool $withRun
     * @return string
     */
    public static function getOutput($command, $withRun = true)
    {
        trigger_error(sprintf(
            'Function %s::%s() is deprecated and will be removed in the next release.',
            __CLASS__,
            __FUNCTION__
        ));

        $process = Runner::setup($command);

        if ($withRun) {
            $process->run();
        }

        // Lots of commands (ex: composer) seem to use both channels for normal output so we
        // combine them so that nothing is hidden.
        $output = $process->getErrorOutput() . "\n\r" . $process->getOutput();
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
     * @return Process|null
     */
    public static function setup($command)
    {
        trigger_error(sprintf(
            'Function %s::%s() is deprecated and will be removed in the next release.',
            __CLASS__,
            __FUNCTION__
        ));

        $process = null;
        if (is_string($command)) {
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
     * @param SymfonyStyle $io
     * @param $command
     * @param string $error_message
     * @return Process|null
     */
    public static function failIfError(SymfonyStyle $io, $command, $error_message = 'Error when running process.')
    {
        trigger_error(sprintf(
            'Function %s::%s() is deprecated and will be removed in the next release.',
            __CLASS__,
            __FUNCTION__
        ));

        $process = Runner::setup($command);

        if (!$process->isStarted() && !$process->isRunning()) {
            $process->run();
        }

        if (empty($process->getExitCode())) {
            return $process;
        }

        $io->error($error_message);
        $io->writeln('Command:');
        $io->writeln($process->getCommandLine());
        $io->writeln('Exit Code:');
        $io->writeln($process->getExitCode());
        $io->writeln('Error Output:');
        $io->writeln($process->getErrorOutput());
        $io->writeln('Standard Output:');
        $io->writeln($process->getOutput());
        exit(1);
    }

    /**
     * Output a process after running or fail if it throws an error.
     *
     * @param SymfonyStyle $io
     * @param $command
     * @param string $error_message
     * @return Process|null
     */
    public static function outputOrFail(SymfonyStyle $io, $command, $error_message = 'Error when running process.')
    {
        trigger_error(sprintf(
            'Function %s::%s() is deprecated and will be removed in the next release.',
            __CLASS__,
            __FUNCTION__
        ));

        $process = Runner::failIfError($io, $command, $error_message);
        $io->writeln(Runner::getOutput($process));
        return $process;
    }
}
