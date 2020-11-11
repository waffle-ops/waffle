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

    protected function configure()
    {
        // TODO: Help and description are not set since these are populated.
        // Consider allow help and description text to be set in config.

        // Forces all tasks to fall under the task namespace.
        $name = $this->getName();
        $this->setName('task:' . $name);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // Note: This is not explicitly defined here, but is from the parent
        // class.
        $command = $input->getArgument('command');
        
        $config = $this->getConfig();
        $task = isset($config['tasks'][$command]) ? $config['tasks'][$command] : [];

        // TODO: Would be wise to add some sort of validation here.

        // TODO: I'm not a huge fan of using the shell command line method.
        // Would be better id this used an input array.
        $process = Process::fromShellCommandline($task);
        $process->run();
                
        // TODO Handle output. Can it be streamed? Or do we actually have to
        // wait until process completes? What happens in the case of something
        // like 'drush pmu' where the '-y' is ommitted?

        if ($process->isSuccessful()) {
            $output->writeln('<info>Task <comment>' . $command . '</comment> ran sucessfully</info>');
            return Command::SUCCESS;
        } else {
            $output->writeln('<error>Task ' . $command . ' returned with an error.</error>');
            $output->writeln('<error>' . $process->getOutput() . '</error>');
            $output->writeln('<error>' . $process->getErrorOutput() . '</error>');
            return Command::FAILURE;
        }
    }
}
