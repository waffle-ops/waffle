<?php

namespace Waffle;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Consolidation\AnnotatedCommand\CommandFileDiscovery;
use Waffle\Command\CommandManager;

class Application extends SymfonyApplication
{
    public const NAME = 'Waffle';
    public const VERSION = '1.0.0-beta';
    public const EMOJI = "\u{1F9C7}";

    public function __construct()
    {
        // Adding a waffle emoji for fun, but it is not guaranteed to work.
        // The waffle emoji was accepted in 2019, but it may not work on all
        // devices yet. Consider adding some sort of check? Not sure what that
        // look like at the moment.
        $name = sprintf('%s %s', self::EMOJI, self::NAME);
        parent::__construct($name, self::VERSION);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $command_manager = new CommandManager();
        $this->addCommands($command_manager->getCommands());

        // TODO Add user defined commands? Or should it be kept to build targets only?
        // TODO Add user defined dependencies here so that we can check that they are there.

        parent::run();
    }
}
