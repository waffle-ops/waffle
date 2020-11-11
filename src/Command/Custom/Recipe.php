<?php

namespace Waffle\Command\Custom;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;
use Waffle\Command\BaseCommand;

class Recipe extends BaseCommand
{

    protected function configure()
    {
        // TODO: Help and description are not set since these are populated.
        // Consider allow help and description text to be set in config.

        // Forces all recipes to fall under the task namespace.
        $name = $this->getName();
        $this->setName('recipe:' . $name);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // Note: This is not explicitly defined here, but is from the parent
        // class.
        $recipe = $input->getArgument('command');
        
        $config = $this->getConfig();
        $recipe_tasks = isset($config['recipes'][$recipe]) ? $config['recipes'][$recipe] : [];

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
            // $task_arguments = new ArrayInput([$arguments]);

            // $output->writeln('<info>Recipe - running <comment>' . json_encode($arguments) . '</comment></info>');

            $return_code = $task_command->run($task_arguments, $output);
            // TODO Handle return code issues.
        }

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
