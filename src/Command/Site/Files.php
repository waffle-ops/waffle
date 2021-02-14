<?php

namespace Waffle\Command\Site;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableTaskInterface;
use Waffle\Model\Site\Sync\SiteSyncFactory;
use Waffle\Traits\ConfigTrait;
use Waffle\Traits\DefaultUpstreamTrait;

class Files extends BaseCommand implements DiscoverableTaskInterface
{
    use DefaultUpstreamTrait;
    use ConfigTrait;

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
        parent::execute($input, $output);

        $config = $this->getConfig();
        $upstream = $input->getOption('upstream');
        $allowed_upstreams = $config->getUpstreams();

        // Ensure upstream is valid.
        if (!in_array($upstream, $allowed_upstreams)) {
            $this->io->error(
                sprintf('Invalid upstream: %s. Allowed upstreams: %s', $upstream, implode('|', $allowed_upstreams))
            );
            return Command::FAILURE;
        }

        $remote_alias = sprintf('@%s.%s:%%files/', $config->getAlias(), $upstream);

        try {
            $factory = new SiteSyncFactory();
            $sync = $factory->getSiteSyncAdapter($config->getCms());
            $sync->syncFiles($remote_alias);
            $this->io->success('Files Sync');
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
