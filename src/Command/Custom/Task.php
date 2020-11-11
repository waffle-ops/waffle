<?php

namespace Waffle\Command\Custom;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;
use Waffle\Command\BaseCommand;

class Task extends BaseCommand
{

    /**
     * @var string
     *
     * The config key used to define this task.
     */
    private $config_key;

    protected function configure()
    {
        // TODO: Help and description are not set since these are populated.
        // Consider allow help and description text to be set in config.

        // Storing the config to be used later.
        $this->config_key = $this->getName();

        // Forces all tasks to fall under the task namespace.
        $task_key = $this->getTaskKey($this->config_key);
        $this->setName($task_key);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $task_key = $this->getTaskKey($this->config_key);

        $output->writeln('<info>Running task <comment>' . $task_key . '</comment></info>');
        
        $config = $this->getConfig();
        $task = isset($config['tasks'][$this->config_key]) ? $config['tasks'][$this->config_key] : '';

        // TODO: Would be wise to add some sort of validation here.

        // TODO: I'm not a huge fan of using the shell command line method.
        // Would be better id this used an input array.
        $process = Process::fromShellCommandline($task);
        $process->run();
                
        // TODO Handle output. Can it be streamed? Or do we actually have to
        // wait until process completes? What happens in the case of something
        // like 'drush pmu' where the '-y' is ommitted?

        if ($process->isSuccessful()) {
            $output->writeln('<info>Task <comment>' . $task_key . '</comment> ran sucessfully</info>');
            return Command::SUCCESS;
        } else {
            $output->writeln('<error>Task ' . $task_key . ' returned with an error.</error>');
            $output->writeln('<error>' . $process->getOutput() . '</error>');
            $output->writeln('<error>' . $process->getErrorOutput() . '</error>');
            return Command::FAILURE;
        }
    }

    private function getTaskKey($config_key)
    {
        return 'task:' . $config_key;
    }
}
