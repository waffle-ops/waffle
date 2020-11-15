<?php

namespace Waffle\Model\Command;

use Waffle\Traits\ConfigTrait;

class CommandManager
{
    use ConfigTrait;

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
    ];

    // These commands are needed to add custom features to Waffle, but they
    // should not appear by the 'list' command by default. Users must define
    // these custom options in the config file before they will appear.
    private $hidden_classes = [
        // Commands for user defined tasks and recipes.
        \Waffle\Command\Custom\Recipe::class,
        \Waffle\Command\Custom\Task::class,
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

        // Handle user defined tasks. Users can also override 'core' tasks by
        // using the right key.
        $tasks = $this->getUserDefinedTasks();

        foreach ($tasks as $task) {
            $command_key = $task->getName();
            $commands[$command_key] = $task;
        }

        // Handle user defined recipes.
        $recipes = $this->getUserDefinedRecipes();

        foreach ($recipes as $recipe) {
            $command_key = $recipe->getName();
            $commands[$command_key] = $recipe;
        }

        // TODO: Any 'core' commands that should not be able to be overrided
        // should go here.

        // TODO: Consider updating the 'list' command to separate recipes.

        return $commands;
    }

    /**
     * getUserDefinedTasks
     *
     * Gets a list of user defined task.
     *
     * @return Command[]
     */
    private function getUserDefinedTasks()
    {
        $config = $this->getConfig()->getProjectConfig();

        $user_tasks = [];

        // Recipes (runs multiple tasks). Allows overriding 'core' recipes.
        $tasks = isset($config['tasks']) ? $config['tasks'] : [];

        foreach ($tasks as $task => $task_list) {
            $user_tasks[] = new \Waffle\Command\Custom\Task($task);
        }

        return $user_tasks;
    }

    /**
     * getUserDefinedRecipes
     *
     * Gets a list of user defined recipes.
     *
     * @return Command[]
     */
    private function getUserDefinedRecipes()
    {
        $config = $this->getConfig()->getProjectConfig();

        $user_recipes = [];

        $recipes = isset($config['recipes']) ? $config['recipes'] : [];

        foreach ($recipes as $recipe => $task_list) {
            $user_recipes[] = new \Waffle\Command\Custom\Recipe($recipe);
        }

        // echo json_encode($config['tasks']) . PHP_EOL;
        return $user_recipes;
    }
}
