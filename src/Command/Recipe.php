<?php

namespace Waffle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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

        // TODO: Help and description are not properly set since these are
        // populated. Consider allow help and description text to be set in
        // config.
        $help = 'Custom Recipe -- <comment>See Waffle config file.</comment>';
        $this->setDescription($help);
        $this->setHelp($help);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->highlightText('Running recipe %s', [$this->config_key]);

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
            $task_key = $this->getTaskKey($task);

            $task_key = $this->getTaskKey($task);
            $args = $this->getTaskArguments($task_key, $task);

            $task_command = $this->getApplication()->find($task_key);

            $task_arguments = $this->prepareTaskArguments($args);
            $return_code = $task_command->run($task_arguments, $output);

            if ($return_code !== Command::SUCCESS) {
                $this->io->highlightText(
                    '[Recipe %s] Failed while running task %s',
                    [$this->config_key, $task_key],
                    'error',
                    'none'
                );
                $this->io->styledText('See error output for more details.', 'error');
                return Command::FAILURE;
            }
        }

        $this->io->highlightText('Recipe %s complete!', [$this->config_key]);

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
}
