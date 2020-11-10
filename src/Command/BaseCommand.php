<?php

namespace Waffle\Command;

use Symfony\Component\Console\Command\Command;
use Waffle\Model\Config\ProjectConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Process\Process;

class BaseCommand extends Command
{
    /**
     * Defines the Input/Output helper object.
     *
     * @var SymfonyStyle
     */
    protected $io;
    
    /**
     * The project config array.
     *
     * @var array
     */
    protected $config;
    
    /**
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     *
     * @throws LogicException When the command name is empty
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);
        
        $this->config = $this->getConfig();
    }
    
    /**
     * getConfig
     *
     * Gets the project configuration from the ProjectConfig singleton.
     *
     * @return array
     */
    protected function getConfig()
    {
        $project_config = ProjectConfig::getInstance();
        return $project_config->getProjectConfig();
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
        $this->io = new SymfonyStyle($input, $output);
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
