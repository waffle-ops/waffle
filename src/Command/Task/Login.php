<?php

namespace Waffle\Command\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableTaskInterface;
use Waffle\Model\Config\ProjectConfig;
use Waffle\Model\Site\Sync\SiteSyncFactory;
use Waffle\Traits\ConfigTrait;

class Login extends BaseCommand implements DiscoverableTaskInterface
{
    use ConfigTrait;

    public const COMMAND_KEY = 'login';

    /**
     * @var ProjectConfig
     */
    protected $config;

    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Attempts to perform a user login action on the site.');
        $this->setHelp('Attempts to perform a user login action on the site.');

        // TODO Add support for arguments: --name, email?, user id?
        // This could be pulled out a level and support dev, stg, prod

        $this->config = $this->getConfig();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $config = $this->getConfig();

        try {
            $factory = new SiteSyncFactory();
            $sync = $factory->getSiteSyncAdapter($config->getCms());
            $process = $sync->postSyncLogin();
            $url = $process->getOutput();
            $this->io->success(sprintf('User Login: %s', $url));
            // TODO: Attempt to open the url with the browser. Drush has
            // already solved this problem. Check to see how they solved it.
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
