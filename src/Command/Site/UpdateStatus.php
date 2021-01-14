<?php

namespace Waffle\Command\Site;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableCommandInterface;
use Waffle\Model\Cli\Runner\Drush;
use Waffle\Model\Cli\Runner\SymfonyCli;
use Waffle\Model\Cli\Runner\Composer;

class UpdateStatus extends BaseCommand implements DiscoverableCommandInterface
{
    public const COMMAND_KEY = 'site:update:status';
    
    /**
     * @var Drush
     */
    protected $drush;
    
    /**
     * @var SymfonyCli
     */
    protected $symfonyCli;
    
    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Checks the project for any pending updates and generates reports.');
        $this->setHelp('Checks the project for any pending updates and generates reports.');
        
        // @todo Add support for arguments: --format, ...?
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
    
        $this->drush = new Drush();
        $this->symfonyCli = new SymfonyCli();

        switch ($this->config->getCms()) {
            case "drupal8":
                $this->generateDrupal8Report();
                break;
            case "drupal7":
                $this->generateDrupal7Report();
                break;
            case "wordpress":
            default:
                throw new Exception('Platform not implemented yet.');
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
    
        $this->io->message('Checking Drupal core and contrib via drush', $this->drush->pmSecurity());
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
    
        $this->io->message('Checking Drupal core and contrib via drush', $this->drush->pmSecurity());

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
        $this->io->message(
            'Checking minor version composer updates',
            $composer->getMinorVersionUpdates()
        );
    
        // @todo: low priority: this is only showing the 2nd grep command in output b/c of the grep filtering.
        $this->io->message(
            'Checking major version composer updates',
            $composer->getMajorVersionUpdates()
        );
    
        if (!$this->symfonyCli->isInstalled()) {
            $this->io->warning('Unable to generate Symfony security reports: Missing Symfony CLI installation.');
        } else {
            $this->io->message(
                'Checking Symfony CLI security',
                $this->symfonyCli->securityCheck()
            );
        }
    }
}
