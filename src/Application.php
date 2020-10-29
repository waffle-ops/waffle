<?php

namespace Waffles;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Consolidation\AnnotatedCommand\CommandFileDiscovery;
use Waffles\Command\CommandManager;

class Application extends SymfonyApplication
{
    public const NAME = 'Waffles';
    public const VERSION = '1.0.0-beta';

    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);
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
