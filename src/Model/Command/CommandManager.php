<?php

namespace Waffle\Model\Command;

use SelfUpdate\SelfUpdateCommand;
use Waffle\Application as Waffle;
use Waffle\Command\Custom\Recipe;
use Waffle\Command\Custom\Task;
use Waffle\Helper\PharHelper;
use Waffle\Traits\ConfigTrait;

class CommandManager
{
    use ConfigTrait;

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

        // Handle user defined tasks. Users can also override 'core' tasks by
        // using the right key.
        $tasks = $this->getUserDefinedTasks();

        foreach ($tasks as $task) {
            $command_key = $task->getName();
            $this->commands[$command_key] = $task;
        }

        // Handle user defined recipes.
        $recipes = $this->getUserDefinedRecipes();

        foreach ($recipes as $recipe) {
            $command_key = $recipe->getName();
            $this->commands[$command_key] = $recipe;
        }

        // Adds the self:update command.
        if (PharHelper::isPhar()) {
            $this->commands['self:update'] = new SelfUpdateCommand(Waffle::NAME, Waffle::VERSION, Waffle::REPOSITORY);
        }


        // TODO: Any 'core' commands that should not be able to be overrided
        // should go here.

        // TODO: Consider updating the 'list' command to separate recipes.
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

    /**
     * getUserDefinedTasks
     *
     * Gets a list of user defined task.
     *
     * @return Command[]
     */
    private function getUserDefinedTasks()
    {
        $user_tasks = [];

        // Recipes (runs multiple tasks). Allows overriding 'core' recipes.
        $tasks = $this->getConfig()->getTasks() ?? [];

        foreach ($tasks as $task => $task_list) {
            $user_tasks[] = new Task($task);
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
        $user_recipes = [];

        $recipes = $this->getConfig()->getRecipes() ?? [];

        foreach ($recipes as $recipe => $task_list) {
            $user_recipes[] = new Recipe($recipe);
        }

        return $user_recipes;
    }
}
