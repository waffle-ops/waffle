<?php

namespace Waffle\Command\Recipe;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableRecipeInterface;
use Waffle\Command\Task\Db;
use Waffle\Command\Task\Files;
use Waffle\Command\Task\Login;
use Waffle\Command\Task\Release;
use Waffle\Traits\DefaultUpstreamTrait;

class Sync extends BaseCommand implements DiscoverableRecipeInterface
{
    use DefaultUpstreamTrait;

    public const COMMAND_KEY = 'site-sync';

    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Syncs the local site from the specified upstream.');
        $this->setHelp('Syncs the local site from the specified upstream.');

        // Shortcuts would be nice, but there seems to be an odd bug as of now
        // when using dashes: https://github.com/symfony/symfony/issues/27333
        $this->addOption(
            'upstream',
            null,
            InputArgument::OPTIONAL,
            'The upstream environment to sync from.',
            $this->getDefaultUpstream()
        );
        $this->addOption('skip-db', null, InputOption::VALUE_NONE, 'Option to skip the DB sync.');
        $this->addOption('skip-files', null, InputOption::VALUE_NONE, 'Option to skip the file sync.');
        $this->addOption('skip-release', null, InputOption::VALUE_NONE, 'Option to skip the release script.');
        $this->addOption('skip-login', null, InputOption::VALUE_NONE, 'Option to skip the user login step.');
        $this->addOption('skip-build', null, InputOption::VALUE_NONE, 'Option to skip the user login step.');

        // TODO Expand the help section.
    }

    /**
     * {@inheritdoc}
     */
    protected function process(InputInterface $input)
    {
        $upstream = $input->getOption('upstream');
        $allowed_upstreams = $this->context->getUpstreams();

        // Ensure upstream is valid.
        if (!in_array($upstream, $allowed_upstreams)) {
            $this->io->error(
                sprintf('Invalid upstream: %s. Allowed upstreams: %s', $upstream, implode('|', $allowed_upstreams))
            );
            return Command::FAILURE;
        }

        $tasks = $this->buildTasks($input);

        foreach ($tasks as $task => $args) {
            $this->io->highlightText('Running task %s', [$task]);

            $command = $this->getApplication()->find($task);
            $command_args = new ArrayInput($args);
            $return_code = $command->run($command_args, $this->io->getOutput());

            if ($return_code !== Command::SUCCESS) {
                return Command::FAILURE;
            }

            $this->io->highlightText('Finished task %s', [$task]);
        }

        return Command::SUCCESS;
    }

    /**
     * Builds the tasks list for the recipe run.
     *
     * @return array
     */
    private function buildTasks(InputInterface $input)
    {
        $tasks = [];

        $upstream = $input->getOption('upstream');

        $skip_db = $input->getOption('skip-db');
        if (!$skip_db) {
            $tasks[Db::COMMAND_KEY] = ['--upstream' => $upstream];
        }

        $skip_files = $input->getOption('skip-files');
        if (!$skip_files) {
            $tasks[Files::COMMAND_KEY] = ['--upstream' => $upstream];
        }

        $skip_release = $input->getOption('skip-release');
        if (!$skip_release) {
            $tasks[Release::COMMAND_KEY] = [];
        }

        $skip_login = $input->getOption('skip-login');
        if (!$skip_login) {
            $tasks[Login::COMMAND_KEY] = [];
        }

        return $tasks;
    }
}
