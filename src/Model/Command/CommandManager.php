<?php

namespace Waffle\Model\Command;

use SelfUpdate\SelfUpdateCommand;
use Waffle\Helper\PharHelper;
use Symfony\Component\Console\Command\HelpCommand;

class CommandManager
{

    /**
     * @var array
     *
     * Avaliable commands for the Waffle application.
     */
    private $commands = [];

    /**
     * Constructor
     *
     * @param iterable
     *   Commands configured in the DI container.
     */
    public function __construct(iterable $commands = [])
    {
        // Loads in commands from the DI container.
        foreach ($commands->getIterator() as $command) {
            // Adding as a keyed array so we can override later if needed.
            $command_key = $command::COMMAND_KEY;
            $this->commands[$command_key] = $command;
        }

        // We are overriding the way commands are loaded, so we need to add the
        // default 'help' command back in (because it is not loaded via DI).
        // We could probably autoload this, but this is fine for now.
        $help = new HelpCommand();
        $this->commands[$help->getName()] = $help;

        // Adds the self:update command.
        if (PharHelper::isPhar()) {
            $this->commands['self:update'] = new SelfUpdateCommand(Waffle::NAME, Waffle::VERSION, Waffle::REPOSITORY);
        }
    }

    /**
     * getCommands
     *
     * Gets a list of all Waffle commands.
     *
     * @return Command[]
     */
    public function getCommands()
    {
        return $this->commands;
    }
}
