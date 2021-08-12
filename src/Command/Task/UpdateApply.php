<?php

namespace Waffle\Command\Task;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Waffle\Command\BaseTask;
use Waffle\Command\DiscoverableTaskInterface;
use Waffle\Helper\CliHelper;
use Waffle\Model\Cli\Runner\Composer;
use Waffle\Model\Cli\Runner\Drush;
use Waffle\Model\Cli\Runner\Git;
use Waffle\Model\Cli\Runner\WpCli;
use Waffle\Model\Config\Item\Cms;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

class UpdateApply extends BaseTask implements DiscoverableTaskInterface
{
    public const COMMAND_KEY = 'update-apply';

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
     * The amount of time in seconds to wait for the command to finish.
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
    protected $priorityPackages = ['drupal/core-recommended', 'drupal/core', 'drupal/core-dev'];

    /**
     * Whether or not the updater should attempt to continue on failure.
     *
     * @var bool
     */
    protected $skipOnFail = false;

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
     * @var WpCli
     */
    protected $wp;

    /**
     * @var CliHelper
     */
    protected $cliHelper;

    /**
     * List of failed packages to report at end of updater.
     *
     * @var array
     */
    protected $failedPackages = [];

    /**
     * Constructor
     *
     * @param Context $context
     * @param IOStyle $io
     * @param CliHelper $cliHelper
     * @param Drush $drush
     * @param Git $git
     * @param Composer $composer
     * @param WpCli $wp
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        CliHelper $cliHelper,
        Drush $drush,
        Git $git,
        Composer $composer,
        WpCli $wp
    ) {
        $this->cliHelper = $cliHelper;
        $this->drush = $drush;
        $this->git = $git;
        $this->composer = $composer;
        $this->wp = $wp;
        parent::__construct($context, $io);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();
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

        $this->addOption(
            'skip-on-fail',
            null,
            null,
            'Whether or not the updater should attempt to continue on update failure for an item.'
        );

        // @todo: add an option to set the git commit message format template

        // Attempting to load config. Parent class will catch exception if we
        // are unable to load it.
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    protected function process(InputInterface $input): int
    {
        $this->gitPrefix = $input->getOption('git-prefix');
        $this->gitPostfix = $input->getOption('git-postfix');
        $this->skipGit = $input->getOption('skip-git');
        $this->major = $input->getOption('major');
        $this->includeConfig = $input->getOption('include-config');
        $this->forceYes = $input->getOption('yes');
        $this->timeout = $input->getOption('timeout');
        $this->skipOnFail = $input->getOption('skip-on-fail');
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
            $this->io->caution($this->cliHelper->getOutput($this->git->statusShort()));
            throw new Exception(
                'You have pending changes in your git repo. Resolve these before attempting to run this command.'
            );
        }

        switch ($this->context->getCms()) {
            case Cms::OPTION_DRUPAL_8:
            case Cms::OPTION_DRUPAL_9:
                $this->applyDrupal8Updates();
                break;
            case Cms::OPTION_DRUPAL_7:
                $this->applyDrupal7Updates();
                break;
            case Cms::OPTION_WORDPRESS:
                $this->applyWordpressUpdates();
                break;
            default:
                throw new Exception('Platform not implemented yet.');
        }

        if (!empty($this->failedPackages)) {
            $this->outputFailedPackages();
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
        // Fail if there are any pending config changes before starting.
        if ($this->includeConfig) {
            $cc = $this->drush->clearCaches();
            $this->cliHelper->outputOrFail($cc, 'Error when clearing Drupal cache for config check.');

            $hasPending = $this->drush->hasPendingConfigChanges();
            if ($hasPending) {
                throw new Exception(
                    'You have pending changes in your config. Resolve these before attempting to run this command.'
                );
            }
        }

        $this->io->title('Applying Drupal 8 Pending Updates');

        if (empty($this->context->getComposerPath())) {
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

        if (!empty($this->context->getComposerPath())) {
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

        foreach ($updates as $update) {
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
     *
     * @throws Exception
     */
    protected function updateDrupal7Item($package)
    {
        $name = $package['name'];
        $from = $package['existing_version'];
        if (empty($package['latest_version'])) {
            $this->io->warning("Skipping {$name} because old and new version are the same. ({$from})");
            return;
        }

        $to = $package['latest_version'];

        $this->io->section("Updating {$name} from {$from} to {$to} ...");
        $this->cliHelper->outputOrFail(
            $this->drush->updateCode($name),
            "Error updating item {$name} ({$from} => {$to})"
        );

        $process = $this->git->statusShort();
        $process->run();
        $output = $process->getOutput();

        if (strpos($output, '.htaccess') !== false) {
            $message = ".htaccess file was updated!";
            $this->io->warning($message);
            $this->addFailedPackage($name, $from, $to, $message);
        }

        $this->io->section('Clearing Drupal cache');
        $this->cliHelper->outputOrFail($this->drush->clearCaches(), 'Error when clearing Drupal cache.');

        $this->io->section('Running any pending Drupal DB updates');
        $this->cliHelper->outputOrFail($this->drush->updateDatabase(), 'Error when running pending Drupal DB updates.');

        if (!$this->git->hasPendingChanges()) {
            $this->io->warning("No git changes found for: {$name} ({$from} => {$to})");
            // No need to attempt to commit or export config if there are no changes.
            return;
        }
        if (!$this->skipGit && $this->git->hasPendingChanges()) {
            $message = "{$this->gitPrefix}Updated {$name} " . "({$from} => {$to}){$this->gitPostfix}";
            $this->gitCommitChanges($message);
        }

        if (!$this->drush->getDrushPatchingEnabled()) {
            $this->io->warning(
                "Unable to automatically reapply patches due to missing dependency: Drush Patcher"
            );
        } else {
            $this->io->section("Reapplying any found patches for {$name}");
            // The patcher will throw an error if no patches are defined for the module so we need to check for that.
            $pp = $this->drush->patchApply($name);
            if (!empty($pp->getExitCode())) {
                $pp_output = $this->cliHelper->getOutput($pp);
                // Patcher does not throw a standard error code, so we check string for the only success message. If
                // the output is not a success, then we assume it was a failure.
                if (strpos($pp_output, 'There are no patches') === false) {
                    $this->io->error('Unable to reapply patch.');
                    $this->cliHelper->dumpProcess($pp);
                    // Attempt to fail gracefully and skip.
                    if ($this->skipOnFail) {
                        $this->gitReset();
                        $this->addFailedPackage($name, $from, $to, 'Unable to reapply patch');
                        $this->io->warning("Skipping {$name} because of failed patches. ({$from} => {$to})");
                    } else {
                        exit(1);
                    }
                }
            }
            $this->io->writeln($this->cliHelper->getOutput($pp));

            if (!$this->skipGit && $this->git->hasPendingChanges()) {
                $message = "{$this->gitPrefix}Reapplied patches for {$name} " . "({$from} => {$to}){$this->gitPostfix}";
                $this->gitCommitChanges($message);
            }
        }

        // If we are skipping git commits then don't continue to the next one.
        if ($this->skipGit) {
            $this->outputFailedPackages();
            $this->io->writeln('Completed update. Review and commit the changes when ready.');
            exit(0);
        }
    }

    /**
     * Gets a list of the composer minor outdated dependencies and applies the update.
     *
     * @throws Exception
     */
    protected function updateMinorComposerDependencies()
    {
        // Ensure installed composer dependencies are up to date with composer.lock before checking pending updates.
        $this->cliHelper->outputOrFail($this->composer->install(), 'Error installing composer dependencies.');

        $pending_updates =
            $this->cliHelper->getOutput($this->composer->getMinorVersionUpdates('json'), true, false);
        $pending_updates = json_decode($pending_updates, true);

        if (!empty($this->packages)) {
            $direct = $this->composer->getDirectDependencyList();
            foreach ($this->packages as $specific_package) {
                if (!in_array($specific_package, $direct)) {
                    // Provided packages need to be at least be in the list of direct dependencies.
                    $this->io->warning(
                        "Package {$specific_package} not found in list of composer direct dependencies."
                    );
                    continue;
                }

                // Attempt to get package info from existing array.
                $key = array_search($specific_package, array_column($pending_updates['installed'], 'name'));
                if ($key === false) {
                    // Package info not found in existing array - we need to get it separately.
                    $package = $this->composer->getPackageInfo($specific_package);
                } else {
                    $package = $pending_updates['installed'][$key];
                }
                $this->updateMinorComposerDependency($package, true);
            }
            return;
        }

        if (empty($pending_updates['installed'])) {
            $this->io->section('No pending updates found.');
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
                $this->addFailedPackage($package['name'], '', '', 'Skipping ignored package');
                continue;
            }
            $this->updateMinorComposerDependency($package);
        }

        foreach ($non_priority as $package) {
            if (in_array($package['name'], $this->ignore)) {
                $this->io->warning("Skipping ignored package: {$package['name']}");
                $this->addFailedPackage($package['name'], '', '', 'Skipping ignored package');
                continue;
            }
            $this->updateMinorComposerDependency($package);
        }
    }

    /**
     * Updates a single composer dependency minor update.
     *
     * @param $package
     *
     * @throws Exception
     */
    protected function updateMinorComposerDependency($package, $forceUpdate = false)
    {
        if (empty($package['name'])) {
            $this->io->warning("Skipping because of empty package name");
            $this->addFailedPackage('?', '?', '?', 'Empty package name');
            return;
        }

        $name = $package['name'];
        if (empty($package['version']) || empty($package['latest'])) {
            $this->io->warning("Skipping {$name} because of invalid versions");
            $this->addFailedPackage($name, '?', '?', 'Invalid versions');
            return;
        }

        $from = $package['version'];
        $to = $package['latest'];

        // @todo: Fix how packages are pulled to filter out deprecated items by this point.
        if ($from == $to && !$forceUpdate) {
            $this->io->warning("Skipping {$name} because old and new version are the same. ({$from})");
            return;
        }

        $this->io->section("Updating {$name} from {$from} to {$to} ...");

        $update_process = $this->composer->updatePackage($package['name'], $this->timeout);
        $update_output = $this->cliHelper->getOutput($update_process);

        // We use the exit code for Composer since it outputs to both normal & error channels.
        if (!empty($update_process->getExitCode())) {
            $this->io->error('Composer update failed with error.');
            $this->cliHelper->dumpProcess($update_process);
            // Attempt to fail gracefully and skip.
            if ($this->skipOnFail) {
                $this->gitReset();
                $this->addFailedPackage($name, $from, $to, 'Composer failure');
                $this->io->warning("Skipping {$name} because of composer failure. ({$from} => {$to})");
                return;
            } else {
                exit(1);
            }
        }

        // Check to see if there was a patch that did not reapply cleanly.
        if (strpos($update_output, 'Could not apply patch!') !== false) {
            $this->io->error('Composer patching failed with error.');
            $this->cliHelper->dumpProcess($update_process);
            // Attempt to fail gracefully and skip.
            if ($this->skipOnFail) {
                $this->gitReset();
                $this->addFailedPackage($name, $from, $to, 'Failed patch');
                $this->io->warning("Skipping {$name} because of failed patches. ({$from} => {$to})");
                return;
            } else {
                exit(1);
            }
        }

        // For some reason Composer puts the "error" output before the normal output.
        $this->io->writeln($update_process->getErrorOutput());
        $this->io->writeln($update_process->getOutput());

        // Clear the Drupal cache.
        $this->io->section('Clearing Drupal cache');
        $cc = $this->drush->clearCaches();
        // @todo: This isn't detecting php error output for some reason.
        $this->cliHelper->outputOrFail($cc, 'Error when clearing Drupal cache.');

        // Run any pending Drupal database updates.
        $this->io->section('Running any pending Drupal DB updates');
        $updb = $this->drush->updateDatabase();
        $this->cliHelper->outputOrFail($updb, 'Error when running pending Drupal DB updates.');

        // Check if the $to version is accurate and update commit message if not.
        $installed = $this->composer->getPackageInstalledVersion($name);
        $message = "{$this->gitPrefix}Updated {$name} " . "({$from} => {$to}){$this->gitPostfix}";
        if ($installed === $from) {
            // Version wasn't updated, but sub-dependencies may have updated.
            $message = "{$this->gitPrefix}Updated dependencies for {$name} " . "({$from}){$this->gitPostfix}";
            $this->addFailedPackage($name, $from, $to, "Unable to update version. Check `composer why`.");
            $to = $installed;
        } elseif ($installed !== $to) {
            // Version was updated, but not to the latest expected version.
            $message = "{$this->gitPrefix}Updated {$name} " . "({$from} => {$to}){$this->gitPostfix}";
            $this->addFailedPackage($name, $from, $to, "Unable to update to latest version. Check `composer why`.");
            $to = $installed;
        }

        $this->gitCommitChanges($message);

        if ($this->includeConfig) {
            $this->io->section('Clearing Drupal cache for config export');
            $cc = $this->drush->clearCaches();
            $this->cliHelper->outputOrFail($cc, 'Error when clearing Drupal cache for config export.');

            $this->io->section('Exporting config changes.');
            $cex = $this->drush->configExport($this->configKey);
            $this->cliHelper->outputOrFail($cex, 'Error when exporting config.');

            $message =
                "{$this->gitPrefix}Export config changes from update of {$name}" .
                "({$from} => {$to}){$this->gitPostfix}";
            $this->gitCommitChanges($message);
        }

        // @todo: run phpcs or any client-specific post-update processes as defined in .waffle.yml

        // @todo: Take a screenshot somehow of the site (maybe a configurable list of URLs in .waffle.yml?)

        // If we are skipping git commits then don't continue to the next one.
        if ($this->skipGit) {
            $this->outputFailedPackages();
            $this->io->writeln('Completed update. Review and commit the changes when ready.');
            exit(0);
        }
    }

    /**
     * Applies Wordpress site and dependency updates.
     *
     * @throws Exception
     */
    protected function applyWordpressUpdates()
    {
        $this->io->title('Applying Wordpress Pending Updates');

        if (!empty($this->context->getComposerPath())) {
            $this->updateMinorComposerDependencies();
        }

        $core = $this->wp->coreCheckUpdate('json');
        // @todo: check for errors before continuing.
        $core = $this->cliHelper->getOutput($core, true, false);
        $core = json_decode($core, true);

        $plugins = $this->wp->pluginListAvailable('json');
        // @todo: check for errors before continuing.
        $plugins = $this->cliHelper->getOutput($plugins, true, false);
        $plugins = json_decode($plugins, true);

        $themes = $this->wp->themeListAvailable('json');
        // @todo: check for errors before continuing.
        $themes = $this->cliHelper->getOutput($themes, true, false);
        $themes = json_decode($themes, true);

        $core_version = $this->wp->coreVersion();

        $updates = [];
        foreach ($core as $core_update) {
            // Default to using the first one.
            if (!array_key_exists('core', $updates)) {
                $updates['core'] = [
                    'name' => 'core',
                    'type' => 'core',
                    'version' => $core_version,
                    'update_version' => $core_update['version'],
                ];
            }

            // If there are multiple available, then use the major one.
            if ($core_update['update_type'] == 'major') {
                $updates['core'] = [
                    'name' => 'core',
                    'type' => 'core',
                    'version' => $core_version,
                    'update_version' => $core_update['version'],
                ];
            }
        }

        foreach ($plugins as $plugin) {
            $updates[$plugin['name']] = $plugin;
            $updates[$plugin['name']]['type'] = 'plugin';
        }

        foreach ($themes as $theme) {
            $updates[$theme['name']] = $theme;
            $updates[$theme['name']]['type'] = 'theme';
        }

        if (empty($updates)) {
            $this->io->writeln('No pending updates found.');
            exit(0);
        }

        if (!empty($this->packages)) {
            foreach ($this->packages as $specific_package) {
                if (!array_key_exists($specific_package, $updates)) {
                    $this->io->warning("Package {$specific_package} not found in list of pending Wordpress updates.");
                    continue;
                }
                $this->updateWordpressItem($updates[$specific_package]);
            }
            return;
        }

        foreach ($updates as $update) {
            if (in_array($update['name'], $this->ignore)) {
                $this->io->warning("Skipping ignored package: {$update['name']}");
                continue;
            }
            $this->updateWordpressItem($update);
        }
    }

    /**
     * Updates a single Wordpress plugin/theme/core item.
     *
     * @param $package
     *
     * @throws Exception
     */
    protected function updateWordpressItem($package)
    {
        $name = $package['name'];
        $type = $package['type'];
        $from = $package['version'];
        $to = $package['update_version'];

        $this->io->section("Updating {$name} from {$from} to {$to} ...");
        $this->cliHelper->outputOrFail(
            $this->wp->updatePackage($name, $type, $to),
            "Error updating item {$name} ({$from} => {$to})"
        );

        // @todo: If htaccess was updated, output a warning.

        $this->io->section('Clearing Wordpress cache');
        $this->cliHelper->outputOrFail($this->wp->cacheFlush(), 'Error when clearing Wordpress cache.');

        $this->io->section('Running any pending Wordpress DB updates');
        $this->cliHelper->outputOrFail($this->wp->updateDatabase(), 'Error when running pending Wordpress DB updates.');

        if (!$this->git->hasPendingChanges()) {
            $this->io->warning("No git changes found for: {$name} ({$from} => {$to})");
            // No need to attempt to commit or export config if there are no changes.
            return;
        }
        if (!$this->skipGit && $this->git->hasPendingChanges()) {
            $message = "{$this->gitPrefix}Updated {$name} " . "({$from} => {$to}){$this->gitPostfix}";
            $this->gitCommitChanges($message);
        }

        // @todo: Add automatic patch reapplication

        // If we are skipping git commits then don't continue to the next one.
        if ($this->skipGit) {
            $this->outputFailedPackages();
            $this->io->writeln('Completed update. Review and commit the changes when ready.');
            exit(0);
        }
    }

    /**
     * Helper function to quickly add a failed package to the tracker.
     *
     * @param $name
     * @param $from
     * @param $to
     * @param $message
     */
    protected function addFailedPackage($name, $from, $to, $message)
    {
        $failed = [
            $name,
            $from,
            $to,
            $message,
        ];
        $this->failedPackages[] = $failed;
    }

    /**
     * Helper function to dump the list of failed packages.
     */
    protected function outputFailedPackages()
    {
        $this->io->section('Failed packages');
        $headers = ['Package', 'From', 'To', 'Reason'];
        $this->io->table($headers, $this->failedPackages);
    }

    /**
     * Helper utility function that does all the processing needed for resetting the git repo.
     */
    protected function gitReset()
    {
        $this->cliHelper->outputOrFail($this->git->resetHard(), 'Error: Unable to hard reset git repo.');
        $this->cliHelper->outputOrFail($this->git->clean(), 'Error: Unable to clean the git repo.');
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
        $this->cliHelper->outputOrFail($this->git->addAll(), 'Error when adding pending changes to git index.');

        $this->io->section('Committing changes to git.');
        $this->cliHelper->outputOrFail($this->git->commit($message), 'Error when committing to git.');
    }
}
