<?php

namespace Waffle\Command\Site;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Waffle\Command\BaseCommand;
use Waffle\Model\Drush\DrushCommand;
use Waffle\Model\Drush\CacheClear;
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
        // TODO Load site config and alter behavior depending on the config.
        // Pantheon, Acquia, WP, Drupal, etc...
        // Currently assumes Drupal 8, no hosting provider

        // TODO Need to check that example settings file is present.
        // TOOD Need to check that DB connection is valid.
        // TODO Add some error handling in general.
        // TODO Write to the console with general status updates.

        $config = $this->getConfig();

        // TODO Validate that drush alias is present / seems to be working with a status call.
        $config = $this->getConfig();
        $upstream = $input->getOption('upstream');
        $allowed_upstreams = $config->getUpstreams();
        $remote_alias = sprintf('@%s.%s', $config->getAlias(), $upstream);

        // Ensure upstream is valid.
        if (!in_array($upstream, $allowed_upstreams)) {
            $output->writeln('<error>Invalid upstream: ' . $upstream . '</error>');
            $output->writeln('<error>Allowed upstreams: ' . implode('|', $allowed_upstreams) . '</error>');
            return Command::FAILURE;
        }

        // Creates or clears the DB.
        $output->writeln('<info>Resetting the local database...</info>');
        $db_reset = new DrushCommand(['sql-create', '-y']);
        $db_reset_process = $db_reset->run();
        $db_reset_output = $db_reset_process->getOutput();

        // It may be wise to have a flag to try using the below. It is
        // technically better for larger databases, but is harder to debug when
        // things go wrong.
        // $db_sync = Process::fromShellCommandline('drush @local-ci-test.dev sql-dump | drush sql-cli');

        // Note: Writing the DB to a temporary file and deleting also falls in
        // category.

        // TODO: We should have a flag to pull from a recent backup instead of
        // adding more load to the DB server.

        // Pulls down the DB.
        $output->writeln('<info>Downloading latest database...</info>');
        $db_export =  new DrushCommand([$remote_alias, 'sql-dump']);
        // The 'sql-sync' command does not work on all Pantheon sites. See
        // https://pantheon.io/docs/drush
        $db_export_process = $db_export->run();
        $db_export_output = $db_export_process->getOutput();

        // Installs the DB.
        $output->writeln('<info>Installing latest database...</info>');
        $db_import = new DrushCommand(['sql-cli']);
        $db_import_process = $db_import->run($db_export_output);
        $db_import_output = $db_import_process->getOutput();

        // Clears the caches.
        $output->writeln('<info>Clearing caches...</info>');
        $cache_clear = new CacheClear();
        $cache_clear->run();

        return Command::SUCCESS;
    }
}
