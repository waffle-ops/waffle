<?php

namespace Waffle\Model\Command;

use Waffle\Command\Task;
use Waffle\Traits\ConfigTrait;

class TaskManager
{
    use ConfigTrait;

    /**
     * @var array
     *
     * Avaliable commands (tasks) for the Waffle application.
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
}
