<?php

namespace Waffle\Command\Custom;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;
use Waffle\Command\BaseCommand;

class Multi extends BaseCommand
{

    protected function configure()
    {
        $this->setDescription('Syncs the local site from the specified upstream.');
        $this->setHelp('Syncs the local site from the specified upstream.');

        // TODO Expand the help section.
        // TODO Dynamically load in the upstream options from the config file.
        // TODO Validate the upstream option from the config file (in help).
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // Note: This is not explicitly defined here, but is from the parent
        // class.
        $command = $input->getArgument('command');
        
        $config = $this->getConfig();
        $tasks = isset($config['tasks'][$command]) ? $config['tasks'][$command] : [];

        foreach ($tasks as $task) {
            $output->writeln('<info>Calling ' . $task . '</info>');

            $command = $this->getApplication()->find($task);
            $args = new ArrayInput([]); // TODO Handle arguments.
            $return_code = $command->run($args, $output);
            // TODO Handle return code issues.
        }

        return Command::SUCCESS;
    }
}
