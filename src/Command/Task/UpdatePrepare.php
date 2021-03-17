<?php

namespace Waffle\Command\Task;

use DateTime;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableTaskInterface;
use Waffle\Helper\CliHelper;
use Waffle\Model\Cli\Runner\Composer;
use Waffle\Model\Cli\Runner\Git;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

class UpdatePrepare extends BaseCommand implements DiscoverableTaskInterface
{
    public const COMMAND_KEY = 'update-prepare';

    /**
     * The name of the main branch (typically master).
     *
     * @var string
     */
    protected $masterBranch = 'master';

    /**
     * The name of the update branch.
     *
     * Using {MM} and {YYYY} will be replaced with the current month and year.
     *
     * @var string
     */
    protected $updateBranch = 'updates/{MM}-{YYYY}';

    /**
     * @var Git
     */
    protected $git;

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var CliHelper
     */
    protected $cliHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param IOStyle $io
     * @param CliHelper $cliHelper
     * @param Composer $composer
     * @param Git $git
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        CliHelper $cliHelper,
        Composer $composer,
        Git $git
    ) {
        $this->cliHelper = $cliHelper;
        $this->composer = $composer;
        $this->git = $git;
        parent::__construct($context, $io);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Prepares a site for checking for and running updates.');
        $this->setHelp('Prepares a site for checking for and running updates.');

        $this->addOption(
            'master-branch',
            null,
            InputArgument::OPTIONAL,
            'The name of the main branch (typically master).',
            'master'
        );

        $this->addOption(
            'update-branch',
            null,
            InputArgument::OPTIONAL,
            'The name of the update branch. Using {MM} and {YYYY} will be replaced with the current month and year.',
            'updates/{MM}-{YYYY}'
        );

        // Attempting to load config. Parent class will catch exception if we
        // are unable to load it.
    }

    /**
     * Runs the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $this->masterBranch = $input->getOption('master-branch');
        $this->updateBranch = $input->getOption('update-branch');
        $date = new DateTime();
        $this->updateBranch = str_replace('{MM}', $date->format('m'), $this->updateBranch);
        $this->updateBranch = str_replace('{YYYY}', $date->format('Y'), $this->updateBranch);

        $this->io->title('Preparing environment for updates');

        // Fail if there are any pending git changes before starting.
        if ($this->git->hasPendingChanges()) {
            $this->io->caution($this->cliHelper->getOutput($this->git->statusShort()));
            throw new Exception(
                'You have pending changes in your git repo. Resolve these before attempting to run this command.'
            );
        }

        // Figure out the state of the master and updates branches and ensure we are on the update branch.
        $currentBranch = $this->git->getCurrentBranch();
        if ($currentBranch != $this->updateBranch) {
            if ($this->git->branchExists($this->updateBranch)) {
                $this->io->note("Switching to existing update branch.");
                $checkout = $this->git->checkout($this->updateBranch);
                $this->cliHelper->outputOrFail($checkout, 'Error when checking out update branch.');
            } else {
                $this->createUpdateBranch();
            }
        }

        // @todo: Run local setup script or something like that here as optional step.

        if (!empty($this->context->getComposerPath())) {
            $install = $this->composer->install();
            $this->cliHelper->outputOrFail($install, "Error installing composer dependencies.");
        }

        return Command::SUCCESS;
    }

    /**
     *
     * @throws Exception
     */
    protected function createUpdateBranch()
    {
        if (!$this->git->branchExists($this->masterBranch)) {
            throw new Exception(
                "The main branch ({$this->masterBranch}) does not exist locally."
            );
        }



        $checkout = $this->git->checkout($this->masterBranch);
        $this->cliHelper->outputOrFail($checkout, 'Error when attempting to change branches.');

        // @todo: check for pending changes again?

        $fetch = $this->git->fetch();
        $this->cliHelper->outputOrFail($fetch, 'Error when attempting to fetch from upstream.');

        if ($this->git->hasUpstreamPending()) {
            throw new Exception(
                "The main branch ({$this->updateBranch}) is behind upstream and needs to be updated."
            );
        }

        $checkout = $this->git->checkout($this->updateBranch, true);
        $this->cliHelper->outputOrFail($checkout, "Error checking out update branch ({$this->updateBranch}).");
    }
}
