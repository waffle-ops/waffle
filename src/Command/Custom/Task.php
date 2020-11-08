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
        $this->setDescription('Syncs the local site from the specified upstream.');
        $this->setHelp('Syncs the local site from the specified upstream.');
        // TODO -- Using the array input option would be a really nice feature.
        // In order to do that, we would need to change the way these tasks are
        // getting created in the CommandManager class.
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
        $process_output = $process->getOutput();
        
        // TODO Handle output. Can it be streamed? Or do we actually have to
        // wait until process completes? What happens in the case of something
        // like 'drush pmu' where the '-y' is ommitted?
        $output->writeln($process_output);

        return Command::SUCCESS;
    }
}
