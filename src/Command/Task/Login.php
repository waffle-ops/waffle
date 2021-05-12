<?php

namespace Waffle\Command\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Waffle\Command\BaseTask;
use Waffle\Command\DiscoverableTaskInterface;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;
use Waffle\Model\Site\Sync\SiteSyncFactory;

class Login extends BaseTask implements DiscoverableTaskInterface
{
    public const COMMAND_KEY = 'login';

    /**
     * @var SiteSyncFactory
     */
    protected $siteSyncFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param IOStyle $io
     * @param SiteSyncFactory $siteSyncFactory
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        SiteSyncFactory $siteSyncFactory
    ) {
        $this->siteSyncFactory = $siteSyncFactory;
        parent::__construct($context, $io);
    }

    protected function configure()
    {
        parent::configure();
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Attempts to perform a user login action on the site.');
        $this->setHelp('Attempts to perform a user login action on the site.');

        // TODO Add support for arguments: --name, email?, user id?
        // This could be pulled out a level and support dev, stg, prod
    }

    /**
     * {@inheritdoc}
     */
    protected function process(InputInterface $input)
    {
        try {
            $sync = $this->siteSyncFactory->getSiteSyncAdapter($this->context->getCms());
            $process = $sync->postSyncLogin();
            $url = $process->getOutput();
            $this->io->success(sprintf('User Login: %s', $url));
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
