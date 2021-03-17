<?php

namespace Waffle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Exception\Config\MissingConfigFileException;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

class BaseCommand extends Command
{

    /**
     * Defines the Input/Output helper object.
     *
     * @var Command
     */
    protected $context;

    /**
     * Defines the Input/Output helper object.
     *
     * @var IOStyle
     */
    protected $io;

    /**
     * A boolean to indicate that this command is enabled.
     *
     * @var bool
     */
    protected $isEnabled = true;

    /**
     * @param Context $context
     * @param IOStyle $io
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     *
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        string $name = null
    ) {
        $this->context = $context;
        $this->io = $io;

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
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }
}
