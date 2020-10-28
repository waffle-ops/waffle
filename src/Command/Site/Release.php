<?php

namespace Waffles\Command\Site;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Waffles\Model\Drush\DrushCommand;
use Waffles\Model\Drush\CacheClear;

class Release extends Command
{

    const COMMAND_KEY = 'site:sync:release';

    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Runs the release script after syncing from upstream.');
        $this->setHelp('Runs the release script after syncing from upstream.');

        // TODO Accept an argument for file path.
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO Load site config and alter behavior depending on the config. 
        // Pantheon, Acquia, WP, Drupal, etc...
        // Currently assumes Drupal 8, no hosting provider

        // TODO -- Run the release script.
        $output->writeln('<info>Running release script(s)...</info>');

        // Clears the caches.
        $output->writeln('<info>Clearing caches...</info>');
        $cache_clear = new CacheClear();
        $cache_clear->run();

        return Command::SUCCESS;
    }
}
