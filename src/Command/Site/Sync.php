<?php

namespace Waffles\Command\Site;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;
use Waffles\Command\BaseCommand;

class Sync extends BaseCommand
{

    public const COMMAND_KEY = 'site:sync';

    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Syncs the local site from the specified upstream.');
        $this->setHelp('Syncs the local site from the specified upstream.');

        // $config = $this->getApplication()->getProjectConfig();
        // TODO Will need to switch between prod and live for pantheon and acquia.
        // $default_upstream = $config['default_upstream'] ?? 'prod';

        // Shortcuts would be nice, but there seems to be an odd bug as of now
        // when using dashes: https://github.com/symfony/symfony/issues/27333
        $this->addOption('upstream', null, InputArgument::OPTIONAL, 'The upstream environment to sync from.', 'prod');
        $this->addOption('skip-db', null, InputArgument::OPTIONAL, 'Option to skip the DB sync.', false);
        $this->addOption('skip-files', null, InputArgument::OPTIONAL, 'Option to skip the file sync.', true);
        $this->addOption('skip-release', null, InputArgument::OPTIONAL, 'Option to skip the release script.', false);
        $this->addOption('skip-login', null, InputArgument::OPTIONAL, 'Option to skip the user login step.', false);

        // TODO Expand the help section.
        // TODO Dynamically load in the upstream options from the config file.
        // TODO Validate the opstream option from the config file (in help).
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO Load site config and alter behavior depending on the config.
        // Pantheon, Acquia, WP, Drupal, etc...
        // Currently assumes Drupal 8

        // TODO Need to check that example settings file is present.
        // TODO Add error handling in general.

        $upstream = $input->getOption('upstream');
        $this->validateUpstream($upstream);

        $skip_db = $input->getOption('skip-db');
        if (!$skip_db) {
            $command = $this->getApplication()->find(Db::COMMAND_KEY);
            $args = new ArrayInput([]);
            $return_code = $command->run($args, $output);
            // TODO Handle return code issues.
        }

        $skip_files = $input->getOption('skip-files');
        if (!$skip_files) {
            $command = $this->getApplication()->find(Files::COMMAND_KEY);
            $args = new ArrayInput([]);
            $return_code = $command->run($args, $output);
            // TODO Handle return code issues.
        }

        $skip_release = $input->getOption('skip-release');
        if (!$skip_release) {
            $command = $this->getApplication()->find(Release::COMMAND_KEY);
            $args = new ArrayInput([]);
            $return_code = $command->run($args, $output);
            // TODO Handle return code issues.
        }

        $skip_login = $input->getOption('skip-login');
        if (!$skip_login) {
            $command = $this->getApplication()->find(Login::COMMAND_KEY);
            $args = new ArrayInput([]);
            $return_code = $command->run($args, $output);
            // TODO Handle return code issues.
        }

        return Command::SUCCESS;
    }

    private function validateUpstream($upstream)
    {
        $config = $this->getConfig();

        $allowed_upstreams = explode(',', $config['upstreams']);

        if (in_array($upstream, $allowed_upstreams)) {
            return;
        }
        
        // TODO Better exceptions.
        throw new \Exception('Invalid upstream...');
    }
}
