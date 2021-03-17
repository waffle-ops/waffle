<?php

namespace Waffle\Command\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableTaskInterface;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;
use Waffle\Model\Site\Sync\SiteSyncFactory;
use Waffle\Traits\DefaultUpstreamTrait;

class Db extends BaseCommand implements DiscoverableTaskInterface
{
    use DefaultUpstreamTrait;

    public const COMMAND_KEY = 'sync-db';

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
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Pulls the database down from the specified upstream.');
        $this->setHelp('Pulls the database down from the specified upstream.');

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
        // TODO Validate the upstream option from the config file (in help).
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        // TODO Need to check that example settings file is present.
        // TOOD Need to check that DB connection is valid.

        $upstream = $input->getOption('upstream');
        $allowed_upstreams = $this->context->getUpstreams();
        $remote_alias = sprintf('@%s.%s', $this->context->getAlias(), $upstream);

        // Ensure upstream is valid.
        if (!in_array($upstream, $allowed_upstreams)) {
            $this->io->error(
                sprintf('Invalid upstream: %s. Allowed upstreams: %s', $upstream, implode('|', $allowed_upstreams))
            );
            return Command::FAILURE;
        }

        try {
            $sync = $this->siteSyncFactory->getSiteSyncAdapter($this->context->getCms());
            $sync->syncDatabase($remote_alias);
            $this->io->success('Database Sync');
            // TODO Write to the console with more general status updates.
            // Maybe expose the reset, export, import, and cache clear steps?
            // Holding on this until we jump to Wordpress -- anything common
            // between the two can be exposed for better output.
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
