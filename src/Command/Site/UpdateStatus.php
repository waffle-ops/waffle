<?php

namespace Waffle\Command\Site;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Command\BaseCommand;
use Waffle\Model\Output\Runner;

class UpdateStatus extends BaseCommand
{
    public const COMMAND_KEY = 'site:update:status';
    
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
        
        // @todo: refactor this to reduce nesting and be separate functions.
        if (empty($this->config->getComposerPath())) {
            $this->io->warning('Unable to generate composer reports: Missing composer file.');
        } else {
            // @todo: should we run `composer install` here?
    
            $this->generateComposerReport();
        }
    
        if (empty($this->config->getDrushMajorVersion())) {
            $this->io->warning('Unable to generate Drush module status: Missing drush install.');
        } else {
            $pmSecurity = $this->drushRunner->pmSecurity();
            Runner::message($this->io, 'Checking Drupal core and contrib via drush', $pmSecurity);
        }
    
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
        
        if (empty($this->config->getDrushMajorVersion())) {
            $this->io->warning('Unable to generate Drush module status: Missing drush install.');
        } else {
            $pmSecurity = $this->drushRunner->pmSecurity();
            Runner::message($this->io, 'Checking Drupal core and contrib via drush', $pmSecurity);
        }
        
        // @todo: What other type of reporting should be done here? `npm audit`?
        // @todo: Run an ADA compliance audit/tester?
        // @todo: Run Lighthouse Audit?
    }
    
    /**
     * Runs composer-related update reporting.
     */
    protected function generateComposerReport()
    {
        Runner::message(
            $this->io,
            'Checking minor version composer updates',
            'composer outdated -Dmn --no-ansi --working-dir="' . $this->config->getComposerPath() . '" "*/*"'
        );
        Runner::message(
            $this->io,
            'Checking major version composer updates',
            'composer outdated -Dn --no-ansi --working-dir="' .
            $this->config->getComposerPath() .
            '" "*/*"  | grep -v "!"'
        );
        
        if (empty($this->config->getSymfonyCli())) {
            $this->io->warning('Unable to generate Symfony security reports: Missing Symfony CLI installation.');
        } else {
            Runner::message(
                $this->io,
                'Checking Symfony CLI security',
                'symfony security:check --dir="' . $this->config->getComposerPath() . '"'
            );
        }
    }
}
