<?php

namespace Waffle\Command\Site;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableCommandInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Waffle\Model\Cli\Runner\Drush;
use Waffle\Model\Cli\Runner\Git;
use Waffle\Model\Cli\Runner\Composer;

class UpdateApply extends BaseCommand implements DiscoverableCommandInterface
{
    public const COMMAND_KEY = 'site:update:apply';
    
    /**
     * A short string to put before the git commit message.
     *
     * @var string
     */
    protected $gitPrefix = '';

    /**
     * A short string to put after the git commit message.
     *
     * @var string
     */
    protected $gitPostfix = '';

    /**
     * Whether or not run a git commit step. If TRUE, the updater stops after each update instead.
     *
     * @var bool
     */
    protected $skipGit = false;

    /**
     * Say yes to all prompts from the Waffle tool.
     *
     * @var bool
     */
    protected $forceYes = false;

    /**
     * NOT IMPLEMENTED - Whether or not to include major updates.
     *
     * @var bool
     */
    protected $major = false;

    /**
     * Drupal 8 only: Whether to export config and commit the changes after each update.
     *
     * @var bool
     */
    protected $includeConfig = false;

    /**
     * Drupal 8 only: The config key to export against.
     *
     * @var string
     */
    protected $configKey = 'sync';

    /**
     * A list of packages/modules to ignore.
     *
     * @var array
     */
    protected $ignore = [];

    /**
     * The amount of time in seconds to wait for the install command to finish.
     *
     * @var int
     */
    protected $timeout = 300;

    /**
     * A specific list of packages/modules to update. Overrides --ignore.
     *
     * @var array
     */
    protected $packages = [];
    
    /**
     * A list of composer packages that should be updated before others.
     *
     * @var string[]
     */
    protected $priorityPackages = ['drupal/core-recommended', 'drupal/core'];
    
    /**
     * @var Git
     */
    protected $git;
    
    /**
     * @var Drush
     */
    protected $drush;
    
    /**
     * @var Composer
     */
    protected $composer;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->git = new Git();
        $this->drush = new Drush();
        $this->composer = new Composer();
    }
    
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Applies any pending site updates.');
        $this->setHelp('Applies any pending site updates.');
        
        // @todo: have these configurable at the .waffle.yml level (and test)

        $this->addOption(
            'git-prefix',
            null,
            InputArgument::OPTIONAL,
            'A short string to prefix git commits with.',
            ''
        );

        $this->addOption(
            'git-postfix',
            null,
            InputArgument::OPTIONAL,
            'A short string to postfix git commits with.',
            ''
        );

        $this->addOption(
            'skip-git',
            null,
            null,
            'Whether or not to skip the git commit step. The updater will stop after each dependency instead.'
        );

        $this->addOption(
            'yes',
            'y',
            null,
            'Say yes to all prompts from the Waffle tool.'
        );

        $this->addOption(
            'major',
            null,
            null,
            'NOT IMPLEMENTED - Whether or not the updater should attempt to apply major composer dependency updates.'
        );

        $this->addOption(
            'include-config',
            null,
            null,
            'Drupal 8 only: Whether to export config and commit the changes after each update.'
        );

        $this->addOption(
            'config-key',
            null,
            InputOption::VALUE_OPTIONAL,
            'Drupal 8 only: The config key to export against.',
            'sync'
        );

        $this->addOption(
            'timeout',
            null,
            InputOption::VALUE_OPTIONAL,
            'The amount of time in seconds to wait for the install command to finish.',
            300
        );

        $this->addOption(
            'ignore',
            null,
            InputOption::VALUE_OPTIONAL,
            'A list of comma-separated packages/modules to ignore.',
            ''
        );

        $this->addOption(
            'packages',
            null,
            InputOption::VALUE_OPTIONAL,
            'A list of specific comma-separated packages to update. Overrides --ignore.',
            ''
        );


        // @todo: add an option to set the git commit message format template
    }

    /**
     * Runs the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        
        $this->gitPrefix = $input->getOption('git-prefix');
        $this->gitPostfix = $input->getOption('git-postfix');
        $this->skipGit = $input->getOption('skip-git');
        $this->major = $input->getOption('major');
        $this->includeConfig = $input->getOption('include-config');
        $this->forceYes = $input->getOption('yes');
        $this->timeout = $input->getOption('timeout');
        $packages = str_replace(' ', '', $input->getOption('packages'));
        if (!empty($packages)) {
            $this->packages = str_getcsv($packages);
        }
        $ignore = str_replace(' ', '', $input->getOption('ignore'));
        if (!empty($ignore)) {
            $this->ignore = str_getcsv($ignore);
        }
        
        // Warn user that this will be applying git commits to the local repo.
        if (!$this->skipGit && !$this->forceYes) {
            $confirmation = $this->io->confirm(
                'This will commit changes to your local git repo. Are you sure you want to proceed?',
                false
            );
            if (!$confirmation) {
                $this->io->comment('Cancelling updates...');
                return 1;
            }
        }
        
        // Fail if there are any pending git changes before starting.
        if ($this->git->hasPendingChanges() && !$this->skipGit) {
            $this->io->caution($this->io->getOutput($this->git->statusShort()));
            throw new Exception(
                'You have pending changes in your git repo. Resolve these before attempting to run this command.'
            );
        }
        
        switch ($this->config->getCms()) {
            case "drupal8":
                $this->applyDrupal8Updates();
                break;
            case "drupal7":
                $this->applyDrupal7Updates();
                break;
            case "wordpress":
            default:
                throw new Exception('Platform not implemented yet.');
        }

        return Command::SUCCESS;
    }

    /**
     * Applies Drupal 8 site and dependency updates.
     *
     * @throws Exception
     */
    protected function applyDrupal8Updates()
    {
        $this->io->title('Applying Drupal 8 Pending Updates');

        if (empty($this->config->getComposerPath())) {
            $this->io->warning('Unable to apply pending composer updates: Missing composer file.');
        } else {
            $this->updateMinorComposerDependencies();
        }

        // @todo: Get list of pending updates not tracked in composer
        // @todo: How does `/admin/reports/updates` do it? Possibly recreate it.
    }

    /**
     * Applies Drupal 7 site and dependency updates.
     *
     * @throws Exception
     */
    protected function applyDrupal7Updates()
    {
        $this->io->title('Applying Drupal 7 Pending Updates');
    
        if (!empty($this->config->getComposerPath())) {
            $this->updateMinorComposerDependencies();
        }
    
        $ups = $this->drush->pmSecurity('json');
    
        // @todo: check for errors before continuing.
        $ups_output = $ups->getOutput();
        $updates = json_decode($ups_output, true);
    
        if (empty($updates)) {
            $this->io->writeln('No pending updates found.');
            exit(0);
        }
    
        if (!empty($this->packages)) {
            foreach ($this->packages as $specific_package) {
                if (!array_key_exists($specific_package, $updates)) {
                    $this->io->warning("Package {$specific_package} not found in list of pending Drupal updates.");
                    continue;
                }
                $this->updateDrupal7Item($updates[$specific_package]);
            }
            return;
        }
    
        foreach ($updates as $module => $update) {
            if (in_array($update['name'], $this->ignore)) {
                $this->io->warning("Skipping ignored package: {$update['name']}");
                continue;
            }
            $this->updateDrupal7Item($update);
        }
    }

    /**
     * Updates a single Drupal 7 module/core package.
     *
     * @param $package
     * @throws Exception
     */
    protected function updateDrupal7Item($package)
    {
        $name = $package['name'];
        $from = $package['existing_version'];
        $to = $package['latest_version'];
    
        $this->io->section("Updating {$name} from {$from} to {$to} ...");
        // @todo: use $drush for upc call
        $this->io->failIfErrorAndOutput(
            "drush upc {$name} --check-disabled -y",
            "Error updating item {$name} ({$from} => {$to})"
        );
    
        $this->io->section('Clearing Drupal cache');
        $cc = $this->drush->clearCaches();
        $this->io->outputOrFail($cc, 'Error when clearing Drupal cache.');
    
        $this->io->section('Running any pending Drupal DB updates');
        $updb = $this->drush->updateDatabase();
        $this->io->outputOrFail($updb, 'Error when running pending Drupal DB updates.');
    
        if ($this->git->hasPendingChanges()) {
            $this->io->warning("No git changes found for: {$name} ({$from} => {$to})");
        }
        if (!$this->skipGit && $this->git->hasPendingChanges()) {
            // @todo: refactor this repeated code for git add/commit into something reusable
            $this->io->section('Adding pending changes to git index.');
            $this->io->failIfErrorAndOutput($this->git->addAll(), 'Error when adding pending changes to git index.');
        
            $this->io->section('Committing changes to git.');
            $message = "{$this->gitPrefix}Updated {$name} " . "({$from} => {$to}){$this->gitPostfix}";
            $this->io->failIfErrorAndOutput($this->git->commit($message), 'Error when committing to git.');
        }
    
        if (!$this->drush->getDrushPatchingEnabled()) {
            $this->io->warning(
                "Unable to automatically reapply patches due to missing dependency: Drush Patcher"
            );
        } else {
            $this->io->section("Reapplying any found patches for {$name}");
            // The patcher will throw an error if no patches are defined for the module so we need to check for that.
            $pp = Process::fromShellCommandline("drush pp {$name} -y");
            $pp->run();
            if (!empty($pp->getExitCode())) {
                $pp_output = $this->io->getOutput($pp);
                if (strpos($pp_output, 'There are no patches') === false) {
                    $this->io->error('Unable to reapply patch.');
                    $this->dumpProcess($pp);
                    exit(1);
                }
            }
            $this->io->writeln($this->io->getOutput($pp));
        
            if (!$this->skipGit && $this->git->hasPendingChanges()) {
                // @todo: refactor this repeated code for git add/commit into something reusable
                $this->io->section('Adding pending changes to git index.');
                $this->io->failIfErrorAndOutput(
                    $this->git->addAll(),
                    'Error when adding pending changes to git index.'
                );
            
                $this->io->section('Committing changes to git.');
                $message = "{$this->gitPrefix}Reapplied patches for {$name} " . "({$from} => {$to}){$this->gitPostfix}";
                $this->io->failIfErrorAndOutput($this->git->commit($message), 'Error when committing to git.');
            }
        }

        // If we are skipping git commits then don't continue to the next one.
        if ($this->skipGit) {
            $this->io->writeln('Completed update. Review and commit the changes when ready.');
            exit(1);
        }
    }

    /**
     * Gets a list of the composer minor outdated dependencies and applies the update.
     *
     * @throws Exception
     */
    protected function updateMinorComposerDependencies()
    {
        $pending_updates = $this->io->getOutput($this->composer->getMinorVersionUpdates('', 'json'), true, false);
        $pending_updates = json_decode($pending_updates, true);
        if (empty($pending_updates['installed'])) {
            $this->io->section('No pending updates found.');
            return;
        }
    
        // If updating a specific package, then search for it in the pending list.
        // @todo: Should we refactor this to allow non-pending items?
        if (!empty($this->packages)) {
            foreach ($this->packages as $specific_package) {
                $key = array_search($specific_package, array_column($pending_updates['installed'], 'name'));
                if ($key === false) {
                    $this->io->warning("Package {$specific_package} not found in list of pending composer updates.");
                    continue;
                }
                $package = $pending_updates['installed'][$key];
                $this->updateMinorComposerDependency($package);
            }
            return;
        }

        // Filter the list to be separated by priority and non-priority.
        $priority = array_filter(
            $pending_updates['installed'],
            function ($package) {
                return in_array($package['name'], $this->priorityPackages);
            }
        );

        $non_priority = array_filter(
            $pending_updates['installed'],
            function ($package) {
                return !in_array($package['name'], $this->priorityPackages);
            }
        );

        // @todo: combine priority/non-priority into a single array and foreach on that instead.

        foreach ($priority as $package) {
            if (in_array($package['name'], $this->ignore)) {
                $this->io->warning("Skipping ignored package: {$package['name']}");
                continue;
            }
            $this->updateMinorComposerDependency($package);
        }

        foreach ($non_priority as $package) {
            if (in_array($package['name'], $this->ignore)) {
                $this->io->warning("Skipping ignored package: {$package['name']}");
                continue;
            }
            $this->updateMinorComposerDependency($package);
        }
    }

    /**
     * Updates a single composer dependency minor update.
     *
     * @param $package
     * @throws Exception
     */
    protected function updateMinorComposerDependency($package)
    {
        $name = $package['name'];
        $from = $package['version'];
        $to = $package['latest'];
    
        if ($from == $to) {
            $this->io->warning("Skipping {$name} because old and new version are the same. ({$from})");
            return;
        }
    
        $this->io->section("Updating {$package['name']} from {$package['version']} to {$package['latest']} ...");
    
        $update_process = $this->composer->updatePackage($package['name'], $this->timeout);
        $update_output = $this->io->getOutput($update_process);
    
        // We use the exit code for Composer since it outputs to both normal & error channels.
        if (!empty($update_process->getExitCode())) {
            $this->io->error('Composer update failed with error.');
            $this->dumpProcess($update_process);
            exit(1);
        }
    
        // Check to see if there was a patch that did not reapply cleanly.
        if (strpos($update_output, 'Could not apply patch!') !== false) {
            $this->io->error('Composer patching failed with error.');
            $this->dumpProcess($update_process);
            exit(1);
        }
    
        // For some reason Composer puts the "error" output before the normal output.
        $this->io->writeln($update_process->getErrorOutput());
        $this->io->writeln($update_process->getOutput());
    
        // Clear the Drupal cache.
        $this->io->section('Clearing Drupal cache');
        $cc = $this->drush->clearCaches();
        // @todo: This isn't detecting php error output for some reason.
        $this->io->outputOrFail($cc, 'Error when clearing Drupal cache.');
    
        // Run any pending Drupal database updates.
        $this->io->section('Running any pending Drupal DB updates');
        $updb = $this->drush->updateDatabase();
        $this->io->outputOrFail($updb, 'Error when running pending Drupal DB updates.');
    
        if (!$this->git->hasPendingChanges()) {
            $this->io->warning(
                "No git changes found for: {$package['name']} ({$package['version']} => {$package['latest']})"
            );
        }
    
        $message = "{$this->gitPrefix}Updated {$name} " . "({$from} => {$to}){$this->gitPostfix}";
        $this->gitCommitChanges($message);
    
        if ($this->includeConfig) {
            $this->io->section('Clearing Drupal cache for config export');
            $cc = $this->drush->clearCaches();
            $this->io->outputOrFail($cc, 'Error when clearing Drupal cache for config export.');
        
            $this->io->section('Exporting config changes.');
            $cex = $this->drush->configExport($this->configKey);
            $this->io->outputOrFail($cex, 'Error when exporting config.');
        
            $message =
                "{$this->gitPrefix}Export config changes from update of {$name}" .
                "({$from} => {$to}){$this->gitPostfix}";
            $this->gitCommitChanges($message);
        }
    
        // @todo: run phpcs or any client-specific post-update processes as defined in .waffle.yml
    
        // @todo: Take a screenshot somehow of the site (maybe a configurable list of URLs in .waffle.yml?)
    
        // If we are skipping git commits then don't continue to the next one.
        if ($this->skipGit) {
            $this->io->writeln('Completed update. Review and commit the changes when ready.');
            exit(1);
        }
    }
    
    /**
     * Helper utility function that stages all changes in git and commits them.
     *
     * @param $message
     *
     * @throws Exception
     */
    protected function gitCommitChanges($message)
    {
        if ($this->skipGit || !$this->git->hasPendingChanges()) {
            return;
        }
        
        $this->io->section('Adding pending changes to git index.');
        $this->io->failIfErrorAndOutput($this->git->addAll(), 'Error when adding pending changes to git index.');
        
        $this->io->section('Committing changes to git.');
        $this->io->failIfErrorAndOutput($this->git->commit($message), 'Error when committing to git.');
    }
}
