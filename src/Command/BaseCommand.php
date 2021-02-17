<?php

namespace Waffle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Waffle\Exception\Config\MissingConfigFileException;
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
     * @todo Remove this from the base class and inject where needed.
     *
     * @var CliHelper
     */
    protected $cliHelper;

    /**
     * A boolean to indicate that this command is enabled.
     *
     * @var bool
     */
    protected $isEnabled = true;

    /**
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     *
     * @throws LogicException When the command name is empty
     */
    public function __construct(string $name = null)
    {
        $this->io = IO::getInstance()->getIO();
        $this->cliHelper = new CliHelper($this->io);

        // We don't want to automatically load config for all commands. We can,
        // however assume they will attempt to load config in the configure()
        // method if it is needed. The default behavior will be if a command
        // fails to load config, it will be disabled automatically.
        try {
            parent::__construct($name);
        } catch (MissingConfigFileException $e) {
            $this->isEnabled = false;
        }
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

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }
}
