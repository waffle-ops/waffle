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
        $recipes = isset($config['recipes'][$command]) ? $config['recipes'][$command] : [];

        foreach ($recipes as $recipe) {
            $output->writeln('<info>Calling recipe' . $recipe . '</info>');

            $command = $this->getApplication()->find($recipe);
            $args = new ArrayInput([]); // TODO Handle arguments.
            $return_code = $command->run($args, $output);
            // TODO Handle return code issues.
        }

        return Command::SUCCESS;
    }
}
