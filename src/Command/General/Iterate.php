<?php

namespace Waffle\Command\General;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableCommandInterface;
use Waffle\Helper\CliHelper;
use Waffle\Model\Cli\WaffleCommand;
use Waffle\Model\Config\ProjectConfig;
use Waffle\Traits\ConfigTrait;

class Iterate extends BaseCommand implements DiscoverableCommandInterface
{
    use ConfigTrait;

    public const COMMAND_KEY = 'global:iterate';

    private $processes = [];

    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Runs a Waffle command for multiple projects.');
        $this->setHelp('Runs a Waffle command for multiple projects.');

        $this->addArgument(
            'cmd',
            InputArgument::REQUIRED,
            'The Waffle command you want to run against the projects (ie. site:update:status).',
        );

        $this->addOption(
            'dir',
            null,
            InputArgument::OPTIONAL,
            'The parent directory containing the Waffle projects. Defaults to current working directory.',
            getcwd()
        );

        $this->addOption(
            'max_depth',
            null,
            InputArgument::OPTIONAL,
            'The maximum depth Waffle should look for other Waffle projects.',
            3
        );

        $this->addOption(
            'max_threads',
            null,
            InputArgument::OPTIONAL,
            'The maximum number of projects that the command will be running against at any given time.',
            4
        );

        // In the future, consider adding a filter option. Perhaps something
        // like --filter=cms:drupal8 to only touch Drupal 8 projects.
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // While testing this, I noticed that Symfony does not behave quite as
        // expected. For example 'wfl global:iterate "--version"' will never
        // make it to this command code, but will instead print off the version
        // information. However, calling 'wfl global:iterate " --version"' will
        // make it in (notice the extra space).
        $cmd = trim($input->getArgument('cmd'));
        $dir = $input->getOption('dir');
        $max_depth = $input->getOption('max_depth');
        $max_threads = $input->getOption('max_threads');

        // Let's prevent inception.
        if ($cmd === self::COMMAND_KEY) {
            $this->io->error(sprintf(
                'You cannot run %s command through the %s command!',
                self::COMMAND_KEY,
                self::COMMAND_KEY
            ));

            return Command::FAILURE;
        }

        $projects = $this->getProjectPaths($dir, $max_depth);

        // Build the waffle processes to run.
        foreach ($projects as $project) {
            $path = dirname($project->getRealPath());
            $this->io->highlightText('Waffle project discovered at %s', [$path]);
            $this->processes[$path] = $this->getWaffleCommand($path, $cmd);
        }

        // Run the commands.
        $this->runWaffleCommands($max_threads);

        $cliHelper = new CliHelper($this->io);
        foreach ($this->processes as $path => $process) {
            // Consider changing the way this output is displayed. This is
            // likely already cumbersome to read.
            $this->io->newLine();
            $this->io->writeln('---------------------------------------------');
            $this->io->highlightText('Begin output from running %s on %s', [$cmd, $path]);
            $this->io->writeln($cliHelper->getOutput($process, false));
            $this->io->highlightText('End output from running %s on %s', [$cmd, $path]);
        }

        return Command::SUCCESS;
    }

    /**
     * Gets a list of projects.
     *
     * @param $directory
     *   The directory where we start looking for projects.
     * @param $max_depth
     *   The maximum depth that we should look for projects.
     *
     * @return Iterator
     */
    private function getProjectPaths($directory, $max_depth)
    {
        $finder = new Finder();
        $finder->ignoreDotFiles(false);
        $finder->files();
        $finder->in($directory);
        $finder->depth('<= ' . $max_depth);
        $finder->name(ProjectConfig::CONFIG_FILE);

        return $finder->getIterator();
    }
    
    /**
     * Runs a waffle command.
     *
     * @param string $path
     *   The path from which to run the command.
     * @param string $cmd
     *   The command to run.
     *
     * @return Process
     * @throws Exception
     */
    private function getWaffleCommand($path, $cmd): Process
    {
        // This is super basic and may need to be updated to support
        // arguments and options.
        $wfl_cmd = new WaffleCommand([$cmd]);
        $process = $wfl_cmd->getProcess();
        $process->setWorkingDirectory($path);
        return $process;
    }

    /**
     * Runs the Waffle commands.
     *
     * @param int $max_threads
     *   The maximum number of processes to run concurrently.
     */
    private function runWaffleCommands($max_threads)
    {
        $total_processes = count($this->processes);
        $total_finished = 0;

        $this->io->writeln('Starting...');
        $this->io->progressStart($total_processes);

        while ($this->waffleProcessesRunning()) {
            $running = 0;
            $finished = 0;
            foreach ($this->processes as $path => $process) {
                if ($process->isTerminated()) {
                    $finished++;
                    continue;
                }

                if ($process->isRunning()) {
                    $running++;
                    continue;
                }

                // We don't really care if there is no limit.
                if ($max_threads <= 0) {
                    $process->start();
                }

                // No reason to continue.
                if (($running + 1) > $max_threads) {
                    break;
                }

                // Run next process if we have not hit the max_threads limit.
                if (($running < $max_threads) && !$process->isStarted()) {
                    $process->start();
                    $running++;
                }
            }

            // Advance the progress bar with a rough guess.
            if ($total_finished < $finished) {
                $prev_total_finished = $total_finished;
                $total_finished = $total_processes - $finished;
                $ticks = $total_finished - $prev_total_finished;
                $this->io->progressAdvance($ticks);
            }
        }

        $this->io->progressFinish();
        $this->io->writeln('Finished! See output below:');
    }

    /**
     * Checks if Waffle processes are running.
     *
     * @return boolean
     *   True is processes are running, false otherwise.
     */
    private function waffleProcessesRunning()
    {
        foreach ($this->processes as $path => $process) {
            if (!$process->isStarted()) {
                return true;
            }

            if ($process->isRunning()) {
                return true;
            }
        }

        return false;
    }
}
