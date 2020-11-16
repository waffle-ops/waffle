<?php

namespace Waffle\Command\Site;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Waffle\Command\BaseCommand;
use Waffle\Model\Site\Sync\SiteSyncFactory;
use Waffle\Traits\DefaultUpstreamTrait;
use Waffle\Traits\ConfigTrait;

class Db extends BaseCommand
{
    use DefaultUpstreamTrait;
    use ConfigTrait;

    public const COMMAND_KEY = 'site:sync:db';

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

        $config = $this->getConfig();
        $upstream = $input->getOption('upstream');
        $allowed_upstreams = $config->getUpstreams();
        $remote_alias = sprintf('@%s.%s', $config->getAlias(), $upstream);

        // Ensure upstream is valid.
        if (!in_array($upstream, $allowed_upstreams)) {
            $this->io->error(
                sprintf('Invalid upstream: %s. Allowed upstreams: %s', $upstream, implode('|', $allowed_upstreams))
            );
            return Command::FAILURE;
        }

        try {
            $factory = new SiteSyncFactory();
            $sync = $factory->getSiteSyncAdapter($config->getCms());
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
