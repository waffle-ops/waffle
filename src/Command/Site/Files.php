<?php

namespace Waffles\Command\Site;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Waffles\Command\BaseCommand;
use Waffles\Model\Drush\DrushCommand;
use Waffles\Model\Drush\CacheClear;
use Waffles\Traits\DefaultUpstreamTrait;

class Files extends BaseCommand
{
    use DefaultUpstreamTrait;

    public const COMMAND_KEY = 'site:sync:files';

    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Pulls the files down from the specified upstream.');
        $this->setHelp('Pulls the files down from the specified upstream.');
        
        // Shortcuts would be nice, but there seems to be an odd bug as of now
        // when using dashes: https://github.com/symfony/symfony/issues/27333
        $this->addOption(
            'upstream',
            null,
            InputArgument::OPTIONAL,
            'The upstream environment to sync from.',
            $this->getDefaultUpstream()
        );

        // TODO Expand the help section.
        // TODO Dynamically load in the upstream options from the config file.
        // TODO Validate the opstream option from the config file (in help).
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO Load site config and alter behavior depending on the config.
        // Pantheon, Acquia, WP, Drupal, etc...
        // Currently assumes Drupal 8, no hosting provider

        // TODO -- Download the files.
        $output->writeln('<info>Downloading files...</info>');

        return Command::SUCCESS;
    }
}
