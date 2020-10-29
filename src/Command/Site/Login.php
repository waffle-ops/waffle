<?php

namespace Waffles\Command\Site;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Waffles\Command\BaseCommand;
use Waffles\Model\Drush\DrushCommand;
use Waffles\Model\Drush\CacheClear;

class Login extends BaseCommand
{

    public const COMMAND_KEY = 'site:sync:login';

    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Attempts to perform a user login action on the site.');
        $this->setHelp('Attempts to perform a user login action on the site.');
        
        // TODO Add support for arguments: --name, email?, user id?
        // This could be pulled out a level and support dev, stg, prod
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO Load site config and alter behavior depending on the config.
        // Pantheon, Acquia, WP, Drupal, etc...
        // Currently assumes Drupal 8, no hosting provider

        // Installs the DB.
        $output->writeln('<info>Attempting user login...</info>');
        $uli = new DrushCommand(['uli']);
        $uli_process = $uli->run();
        $uli_process_output = $uli_process->getOutput();
        $output->writeln('<info>' . $uli_process_output . '</info>');

        return Command::SUCCESS;
    }
}
