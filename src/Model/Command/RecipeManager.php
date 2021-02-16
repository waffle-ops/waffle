<?php

namespace Waffle\Model\Command;

use Waffle\Command\Custom\Recipe;
use Waffle\Traits\ConfigTrait;

class RecipeManager
{
    use ConfigTrait;

    /**
     * @var array
     *
     * Avaliable commands (recipes) for the Waffle application.
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

        // Handle user defined recipes.
        $recipes = $this->getUserDefinedRecipes();

        foreach ($recipes as $recipe) {
            $command_key = $recipe->getName();
            $this->commands[$command_key] = $recipe;
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
