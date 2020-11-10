<?php

namespace Waffle\Command\Site;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Waffle\Command\BaseCommand;
use Waffle\Model\Output\Runner;
use Waffle\Model\Git\GitStatusShort;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Waffle\Model\Drush\CacheClear;
use Waffle\Model\Drush\UpdateDatabase;
use Waffle\Model\Git\GitAddAll;
use Waffle\Model\Git\GitCommit;
use Waffle\Model\Drush\ConfigExport;

class UpdateApply extends BaseCommand
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
     * The amount of time in seconds to wait for the install command to finish.
     *
     * @var int
     */
    protected $timeout = 300;
    
    /**
     * A list of composer packages that should be updated before others.
     *
     * @var string[]
     */
    protected $priorityPackages = ['drupal/core-recommended', 'drupal/core'];
    
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Applies any pending site updates.');
        $this->setHelp('Applies any pending site updates.');
        
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
            'Whether or not to skip the git commit step. The updater will stop after each dependency instead.',
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        
        $this->gitPrefix = $input->getOption('git-prefix');
        $this->gitPostfix = $input->getOption('git-postfix');
        $this->skipGit = $input->getOption('skip-git');
        $this->major = $input->getOption('major');
        $this->includeConfig = $input->getOption('include-config');
        $this->forceYes = $input->getOption('yes');
        $this->timeout = $input->getOption('timeout');
        
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
        $git_pending_output = (new GitStatusShort())->run()->getOutput();
        if (!empty($git_pending_output) && !$this->skipGit) {
            $this->io->caution($git_pending_output);
            throw new Exception(
                'You have pending changes in your git repo. Resolve these before attempting to run this command.'
            );
        }
        
        switch ($this->config['cms']) {
            case "drupal8":
                $this->applyDrupal8Updates();
                break;
            case "drupal7":
            case "wordpress":
            default:
                throw new Exception('Platform not implemented yet.');
                break;
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Applies Drupal 8 site and dependency updates.
     */
    protected function applyDrupal8Updates()
    {
        $this->io->title('Applying Drupal 8 Pending Updates');
        
        if (!isset($this->config['composer_path'])) {
            $this->io->warning('Unable to apply pending composer updates: Missing composer file.');
        } else {
            $this->updateMinorComposerDependencies();
        }
    }
    
    /**
     * Gets a list of the composer minor outdated dependencies and applies the update.
     */
    protected function updateMinorComposerDependencies()
    {
        $pending_updates = Runner::getOutput(
            'composer outdated -Dmn --no-ansi --format="json" --working-dir="' .
            $this->config['composer_path'] .
            '" "*/*"'
        );
        $pending_updates = json_decode($pending_updates, true);
        
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
        
        foreach ($priority as $package) {
            $this->updateMinorComposerDependency($package);
        }
        
        foreach ($non_priority as $package) {
            $this->updateMinorComposerDependency($package);
        }
    }
    
    /**
     * Updates a single composer dependency minor update.
     *
     * @param $package
     */
    protected function updateMinorComposerDependency($package)
    {
        $this->io->section("Updating {$package['name']} from {$package['version']} to {$package['latest']} ...");
        
        $update_command =
            "composer update --with-dependencies --no-ansi -n " .
            "--working-dir='{$this->config['composer_path']}' {$package['name']}";
        $update_process = Process::fromShellCommandline($update_command);
        // Increase the default timeout since this can sometimes take awhile.
        $update_process->setTimeout($this->timeout);
        $update_output = Runner::getOutput($update_process);
        
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
        $cc = (new CacheClear())->setup();
        $cc_output = Runner::getOutput($cc);
        if (!empty($cc->getExitCode())) {
            $this->io->error('Error when clearing Drupal cache.');
            $this->dumpProcess($cc);
            exit(1);
        }
        
        $this->io->writeln($cc_output);
        
        // Run any pending Drupal database updates.
        $this->io->section('Running any pending Drupal DB updates');
        $updb = (new UpdateDatabase())->setup();
        $updb_output = Runner::getOutput($updb);
        if (!empty($updb->getExitCode())) {
            $this->io->error('Error when running pending Drupal DB updates.');
            $this->dumpProcess($updb);
            exit(1);
        }
        
        $this->io->writeln($updb_output);
        
        $git_pending_output = (new GitStatusShort())->run()->getOutput();
        if (empty($git_pending_output)) {
            $this->io->warning(
                "No git changes found for: {$package['name']} ({$package['version']} => {$package['latest']})"
            );
        }
        if (!$this->skipGit && !empty($git_pending_output)) {
            // @todo: refactor this repeated code for git add/commit into something reusable
            $this->io->section('Adding pending changes to git index.');
            $git_add = (new GitAddAll())->setup();
            $git_add_output = Runner::getOutput($git_add);
            if (!empty($git_add->getExitCode())) {
                $this->io->error('Error when adding pending changes to git index.');
                $this->dumpProcess($git_add);
                exit(1);
            }
            $this->io->writeln($git_add_output);
            
            $this->io->section('Committing changes to git.');
            $message =
                "{$this->gitPrefix}Updated {$package['name']} " .
                "({$package['version']} => {$package['latest']}){$this->gitPostfix}";
            $git_commit = (new GitCommit($message))->setup();
            $git_commit_output = Runner::getOutput($git_commit);
            if (!empty($git_commit->getExitCode())) {
                $this->io->error('Error when committing to git.');
                $this->dumpProcess($git_commit);
                exit(1);
            }
            $this->io->writeln($git_commit_output);
        }
        
        if ($this->includeConfig) {
            $this->io->section('Clearing Drupal cache for config export');
            $cc = (new CacheClear())->setup();
            $cc_output = Runner::getOutput($cc);
            if (!empty($cc->getExitCode())) {
                $this->io->error('Error when clearing Drupal cache for config export.');
                $this->dumpProcess($cc);
                exit(1);
            }
            $this->io->writeln($cc_output);
            
            $this->io->section('Exporting config changes.');
            $cex = (new ConfigExport($this->configKey))->setup();
            $cex_output = Runner::getOutput($cex);
            if (!empty($cex->getExitCode())) {
                $this->io->error('Error when exporting config.');
                $this->dumpProcess($cex);
                exit(1);
            }
            
            $this->io->writeln($cex_output);
            
            $git_pending_output = (new GitStatusShort())->run()->getOutput();
            if (!$this->skipGit && !empty($git_pending_output)) {
                // @todo: refactor this repeated code for git add/commit into something reusable
                $this->io->section('Adding pending changes to git index.');
                $git_add = (new GitAddAll())->setup();
                $git_add_output = Runner::getOutput($git_add);
                if (!empty($git_add->getExitCode())) {
                    $this->io->error('Error when adding pending changes to git index.');
                    $this->dumpProcess($git_add);
                    exit(1);
                }
                $this->io->writeln($git_add_output);
                
                $this->io->section('Committing changes to git.');
                $message =
                    "{$this->gitPrefix}Export config changes from update of {$package['name']}" .
                    "({$package['version']} => {$package['latest']}){$this->gitPostfix}";
                $git_commit = (new GitCommit($message))->setup();
                $git_commit_output = Runner::getOutput($git_commit);
                if (!empty($git_commit->getExitCode())) {
                    $this->io->error('Error when committing to git.');
                    $this->dumpProcess($git_commit);
                    exit(1);
                }
                $this->io->writeln($git_commit_output);
            }
        }
        
        // @todo: run phpcs or any client-specific post-update processes as defined in .waffle.yml
        
        // @todo: Take a screenshot somehow of the site (maybe a configurable list of URLs in .waffle.yml?)
        
        // If we are skipping git commits then don't continue to the next one.
        if ($this->skipGit) {
            $this->io->writeln('Completed update. Review and commit the changes when ready.');
            exit(1);
        }
    }
}
