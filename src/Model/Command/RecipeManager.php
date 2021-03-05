<?php

namespace Waffle\Model\Command;

use Waffle\Command\Recipe;
use Waffle\Exception\Config\MissingConfigFileException;
use Waffle\Model\Context\Context;

class RecipeManager
{


    /**
     * @var Context
     */
    private $context;

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
    public function __construct(Context $context, iterable $commands = [])
    {
        $this->context = $context;

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
        $recipes = $this->context->getRecipes() ?? [];

        $user_recipes = [];

        foreach ($recipes as $recipe => $task_list) {
            $user_recipes[] = new Recipe($this->context, $recipe);
        }

        return $user_recipes;
    }
}
