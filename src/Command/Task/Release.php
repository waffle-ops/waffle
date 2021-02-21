<?php

namespace Waffle\Command\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableTaskInterface;
use Waffle\Model\Config\ProjectConfig;
use Waffle\Traits\ConfigTrait;

class Release extends BaseCommand implements DiscoverableTaskInterface
{
    use ConfigTrait;

    public const COMMAND_KEY = 'release-script';

    /**
     * @var ProjectConfig
     */
    protected $config;

    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Runs the release script after syncing from upstream.');
        $this->setHelp('Runs the release script after syncing from upstream.');

        // TODO Accept an argument for file path.

        $this->config = $this->getConfig();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO -- Run the release script.

        return Command::SUCCESS;
    }
}
