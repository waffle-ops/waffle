<?php

namespace Waffle\Command\General;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;
use Waffle\Command\BaseCommand;
use Waffle\Traits\ConfigTrait;
use Waffle\Command\DiscoverableCommandInterface;
use Waffle\Model\Config\ProjectConfig;
use Symfony\Component\Finder\Finder;
use Waffle\Model\Cli\WaffleCommand;

class Iterate extends BaseCommand implements DiscoverableCommandInterface
{
    use ConfigTrait;

    public const COMMAND_KEY = 'global:iterate';

    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Runs a Waffle command for multiple projects.');
        $this->setHelp('INIT'); // TODO

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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Whle testing this, I noticed that Symfony does not behave quite as
        // expected. For example 'wfl global:iterate "--version"' will never
        // make it to this command code, but will instead print off the version
        // information. However, calling 'wfl global:iterate " --version"' will
        // make it in (notice the extra space).
        $cmd = trim($input->getArgument('cmd'));
        $dir = $input->getOption('dir');
        $max_depth = $input->getOption('max_depth');

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

        // Run the command over each project sequentially.
        // In the future, it would be nice to run these concurrently.
        foreach($projects as $project) {
            $this->io->highlightText('Starting %s for %s', [
                $cmd,
                $projects->getRealPath(),
            ]);

            // This is super basic and may need to be updated to support
            // aruments and options.
            $wfl_cmd = new \Waffle\Model\Cli\WaffleCommand([$cmd]);
            $process = $wfl_cmd->getProcess();
            $process->run();

            // TODO -- This will be cumbersome to read. Maybe we should instead
            // output to a log file in each project directory.
            $this->io->writeln($process->getOutput());

            $this->io->highlightText('Finished %s for %s', [
                $cmd,
                $projects->getRealPath(),
            ]);
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
     * @return string
     */
    private function getProjectPaths($directory, $max_depth)
    {
        $finder = new Finder();
        $finder->ignoreDotFiles(false);
        $finder->files();
        $finder->in($directory);
        $finder->depth('< ' . $max_depth);
        $finder->name(ProjectConfig::CONFIG_FILE);

        return $finder->getIterator();
    }
}
