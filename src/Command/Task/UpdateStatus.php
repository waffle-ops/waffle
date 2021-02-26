<?php

namespace Waffle\Command\Task;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableTaskInterface;
use Waffle\Helper\CliHelper;
use Waffle\Model\Cli\Runner\Composer;
use Waffle\Model\Cli\Runner\Drush;
use Waffle\Model\Cli\Runner\SymfonyCli;
use Waffle\Model\Cli\Runner\WpCli;
use Waffle\Model\Config\ProjectConfig;
use Waffle\Traits\ConfigTrait;

class UpdateStatus extends BaseCommand implements DiscoverableTaskInterface
{
    use ConfigTrait;

    public const COMMAND_KEY = 'update-status';

    /**
     * @var Drush
     */
    protected $drush;

    /**
     * @var SymfonyCli
     */
    protected $symfonyCli;

    /**
     * @var ProjectConfig
     */
    protected $config;

    /**
     * @var CliHelper
     */
    protected $cliHelper;

    /**
     * Constructor
     *
     * @param CliHelper $cliHelper
     */
    public function __construct(CliHelper $cliHelper) {
        $this->cliHelper = $cliHelper;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Checks the project for any pending updates and generates reports.');
        $this->setHelp('Checks the project for any pending updates and generates reports.');

        // @todo Add support for arguments: --format, ...?

        // @todo: Add parameter to output full report to file instead of screen

        // Attempting to load config. Parent class will catch exception if we
        // are unable to load it.
        $this->config = $this->getConfig();
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

        $this->symfonyCli = new SymfonyCli();

        switch ($this->config->getCms()) {
            case ProjectConfig::CMS_DRUPAL_8:
                $this->drush = new Drush();
                $this->generateDrupal8Report();
                break;
            case ProjectConfig::CMS_DRUPAL_7:
                $this->drush = new Drush();
                $this->generateDrupal7Report();
                break;
            case ProjectConfig::CMS_WORDPRESS:
                $this->generateWordpressReport();
                break;
            default:
                throw new Exception('Platform not implemented yet or missing CMS config.');
        }

        return Command::SUCCESS;
    }

    /**
     * Outputs a Drupal 8 update report.
     *
     * @throws Exception
     */
    protected function generateDrupal8Report()
    {
        $this->io->title('Generating Drupal 8 Update Reports');

        if (empty($this->config->getComposerPath())) {
            $this->io->warning('Unable to generate composer reports: Missing composer file.');
        } else {
            // @todo: Add a report on what packages are required by composer but not currently installed by Drupal

            $this->generateComposerReport();
        }

        $this->cliHelper->message('Checking Drupal core and contrib via drush', $this->drush->pmSecurity());
        // @todo: get non-composer-tracked pending updates for drush 9+ via
        // @todo: `drush eval "var_export(update_get_available(TRUE));"`
        // @todo: see docroot/core/modules/update/src/Controller/UpdateController.php::updateStatus()

        // @todo: What other type of reporting should be done here? `npm audit`?
        // @todo: Run an ADA compliance audit/tester?
        // @todo: Run Lighthouse Audit?
    }

    /**
     * Outputs a Drupal 8 update report.
     *
     * @throws Exception
     */
    protected function generateDrupal7Report()
    {
        $this->io->title('Generating Drupal 7 Update Reports');

        if (!empty($this->config->getComposerPath())) {
            $this->generateComposerReport();
        }

        $this->cliHelper->message('Checking Drupal core and contrib via drush', $this->drush->pmSecurity());

        // @todo: What other type of reporting should be done here? `npm audit`?
        // @todo: Run an ADA compliance audit/tester?
        // @todo: Run Lighthouse Audit?
    }

    /**
     * Runs composer-related update reporting.
     *
     * @throws Exception
     */
    protected function generateComposerReport()
    {
        $composer = new Composer();
        $this->cliHelper->message(
            'Checking minor version composer updates',
            $composer->getMinorVersionUpdates()
        );

        // @todo: low priority: this is only showing the 2nd grep command in output b/c of the grep filtering.
        $this->cliHelper->message(
            'Checking major version composer updates',
            $composer->getMajorVersionUpdates()
        );

        if (!$this->symfonyCli->isInstalled()) {
            $this->io->warning('Unable to generate Symfony security reports: Missing Symfony CLI installation.');
        } else {
            $this->cliHelper->message(
                'Checking Symfony CLI security',
                $this->symfonyCli->securityCheck()
            );
        }
    }

    /**
     * Outputs a Wordpress update report.
     *
     * @throws Exception
     */
    protected function generateWordpressReport()
    {
        $this->io->title('Generating Wordpress Update Reports');

        if (!empty($this->config->getComposerPath())) {
            $this->generateComposerReport();
        }

        $wp = new WpCli();
        if (!$wp->isInstalled()) {
            $this->io->warning('Unable to generate Wordpress update report: Missing WP CLI installation.');
            return;
        }

        $this->cliHelper->message(
            'Check Wordpress core pending updates',
            $wp->coreCheckUpdate()
        );

        $this->cliHelper->message(
            'Checking plugin pending updates',
            $wp->pluginListAvailable()
        );

        $this->cliHelper->message(
            'Checking theme pending updates',
            $wp->themeListAvailable()
        );

        // @todo: Possibly add WP CLI security scanning
        // @todo: https://guides.wp-bullet.com/using-wp-cli-to-scan-for-wordpress-security-vulnerabilities/
    }
}
