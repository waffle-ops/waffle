<?php

namespace Waffle\Model\Command;

class CommandManager
{
    // Sadly, CommandFileDiscovery from consolidation/annotated-command is
    // deprecated. It also does not work with .phar files, which is also a deal
    // breaker. I'm not thrilled with maintaining a static list of commands,
    // but setting up DI isn't something I really want to do either since the
    // feature set of Waffle is still in the air.

    private $command_classes = [
        // Site Sync Commands
        \Waffle\Command\Site\Sync::class,
        \Waffle\Command\Site\Db::class,
        \Waffle\Command\Site\Files::class,
        \Waffle\Command\Site\Login::class,
        \Waffle\Command\Site\Release::class,
        \Waffle\Command\Site\UpdateStatus::class,
        \Waffle\Command\Site\UpdateApply::class,
    
        // Command for running shell commands.
        \Waffle\Command\Shell\Shell::class,
    ];

    // I'm interested in lazy loading, but that's is something for another day.
    // Again, the feature set is still up in the air, so I don't want to box
    // myself into a corner by jumping into to lazy loading.
    // TODO: Consider lazy loading Waffle commands.
    // https://symfony.com/doc/current/console/lazy_commands.html

    /**
     * getCommands
     *
     * Gets a list of all Waffle commands.
     *
     * @return Command[]
     */
    public function getCommands()
    {
        // TODO Consider caching this. We may need to call this in other areas.
        $commands = [];

        foreach ($this->command_classes as $clazz) {
            $command_key = $clazz::COMMAND_KEY;
            $commands[$command_key] = new $clazz();
        }

        // This would be the best place add user defined commands. We can
        // likely also allow users to override core commands if they use the
        // right key.

        // TODO: Determine how user defined tasks work. Validate as needed.

        return $commands;
    }
}
