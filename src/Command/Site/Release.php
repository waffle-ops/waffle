<?php

namespace Waffle\Command\Site;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableTaskInterface;

class Release extends BaseCommand implements DiscoverableTaskInterface
{

    public const COMMAND_KEY = 'site:sync:release';

    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Runs the release script after syncing from upstream.');
        $this->setHelp('Runs the release script after syncing from upstream.');

        // TODO Accept an argument for file path.
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO -- Run the release script.

        return Command::SUCCESS;
    }
}
