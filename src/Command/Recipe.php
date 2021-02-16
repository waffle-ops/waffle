<?php

namespace Waffle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Command\BaseCommand;
use Waffle\Traits\ConfigTrait;

class Recipe extends BaseCommand
{
    use ConfigTrait;

    /**
     * @var string
     *
     * The config key used to define this recipe.
     */
    private $config_key;

    protected function configure()
    {
        // Storing the config to be used later.
        $this->config_key = $this->getName();

        // Forces all recipes to fall under the recipe namespace.
        $recipe_key = $this->getRecipeKey($this->config_key);
        $this->setName($recipe_key);

        // TODO: Help and description are not properly set since these are
        // populated. Consider allow help and description text to be set in
        // config.
        $help = 'Custom Recipe -- <comment>See Waffle config file.</comment>';
        $this->setDescription($help);
        $this->setHelp($help);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $recipe_key = $this->getRecipeKey($this->config_key);

        $output->writeln('<info>Running recipe <comment>' . $recipe_key . '</comment></info>');

        // Note: This is not explicitly defined here, but is from the parent
        // class.
        $recipe = $input->getArgument('command');

        $config_recipes = $this->getConfig()->getRecipes();
        $recipe_tasks = isset($config_recipes[$this->config_key]) ? $config_recipes[$this->config_key] : [];

        $tasks = [];
        $arguments = [];

        // Doing some simple validation before we start. Just enough to verify
        // that the tasks are present (before we run any of them).
        foreach ($recipe_tasks as $task) {
            // This will throw an exception if not a string or array.
            $task_key = $this->getTaskKey($task);
            $args = $this->getTaskArguments($task_key, $task);

            // This will throw an exception if the task is not found.
            $command = $this->getApplication()->find($task_key);

            $arguments = isset($task[$task_key]) ? $task[$task_key] : [];
            $tasks[] = [$task_key => $arguments];
        }

        // Runs the tasks for the recipes.
        foreach ($tasks as $task) {
            $output->writeln('<info>Recipe - running <comment>' . $task_key . '</comment></info>');

            $task_key = $this->getTaskKey($task);
            $args = $this->getTaskArguments($task_key, $task);

            $task_command = $this->getApplication()->find($task_key);

            $task_arguments = $this->prepareTaskArguments($args);
            $return_code = $task_command->run($task_arguments, $output);

            if ($return_code !== Command::SUCCESS) {
                $output->writeln('<error>Recipe ' . $recipe_key . ' failed while running ' . $task_key . '.</error>');
                $output->writeln('<error>See error output for more details.</error>');
                return Command::FAILURE;
            }
        }

        $output->writeln('<info>Recipe <comment>' . $recipe_key . '</comment> complete</info>');

        return Command::SUCCESS;
    }

    private function getTaskKey($task)
    {
        if (is_string($task)) {
            $task_key = $task;
        } elseif (is_array($task)) {
            $task_key = array_key_first($task);
        } else {
            throw new \Exception('Invalid task definition : ' . json_encode($task));
        }

        return $task_key;
    }

    private function getTaskArguments($task_key, $task)
    {
        if (is_string($task)) {
            return [];
        }

        if (isset($task[$task_key])) {
            return $task[$task_key];
        }

        return [];
    }

    private function prepareTaskArguments($args)
    {
        $input_args = [];

        foreach ($args as $key => $value) {
            $input_args[$key] = $value;
        }

        return new ArrayInput($input_args);
    }

    private function getRecipeKey($config_key)
    {
        return 'recipe:' . $config_key;
    }
}
