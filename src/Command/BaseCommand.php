<?php

namespace Waffle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Process\Process;
use Waffle\Traits\ConfigTrait;
use Waffle\Model\Config\ProjectConfig;
use Waffle\Model\Drush\DrushCommandRunner;
use Waffle\Model\IO\IO;
use Waffle\Model\IO\IOStyle;

class BaseCommand extends Command
{
    use ConfigTrait;

    /**
     * Defines the Input/Output helper object.
     *
     * @var IOStyle
     */
    protected $io;

    /**
     * A reference to the project config.
     *
     * @var ProjectConfig
     */
    protected $config;

    /**
     * A reference to the drush command runner.
     *
     * @var DrushCommandRunner
     */
    protected $drushRunner;

    /**
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     *
     * @throws LogicException When the command name is empty
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->config = $this->getConfig();
        $this->drushRunner = new DrushCommandRunner();
        $this->io = IO::getInstance()->getIO();
    }

    /**
     * Sets up properties used for all commands.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }

    /**
     * Defines a utility function to dump all relevant process information for debugging.
     *
     * @param Process $process
     */
    protected function dumpProcess(Process $process)
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
}
