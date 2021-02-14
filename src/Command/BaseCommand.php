<?php

namespace Waffle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Waffle\Helper\CliHelper;
use Waffle\Model\IO\IO;
use Waffle\Model\IO\IOStyle;

class BaseCommand extends Command
{

    /**
     * Defines the Input/Output helper object.
     *
     * @var IOStyle
     */
    protected $io;

    /**
     * A reference to the project config.
     *
     * @var CliHelper
     */
    protected $cliHelper;

    /**
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     *
     * @throws LogicException When the command name is empty
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->io = IO::getInstance()->getIO();
        $this->cliHelper = new CliHelper($this->io);
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
        $this->cliHelper->dumpProcess($process);
    }
}
