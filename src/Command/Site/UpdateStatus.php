<?php

namespace Waffle\Command\Site;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Waffle\Command\BaseCommand;
use Waffle\Model\Drush\DrushCommand;
use Waffle\Model\Drush\PmSecurity;
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
        $io = new SymfonyStyle($input, $output);
        $config = $this->getConfig();
        
        switch ($config['cms']) {
            case "drupal8":
                $this->generateDrupal8Report($io);
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
     * Outputs a Drupal 8 update report.
     *
     * @param SymfonyStyle $io
     */
    protected function generateDrupal8Report(SymfonyStyle $io)
    {
        $config = $this->getConfig();
        
        $io->title('Generating Drupal 8 Update Reports');
        
        // @todo: refactor this to reduce nesting and be separate functions.
        if (!isset($config['composer_path'])) {
            $io->warning('Unable to generate composer reports: Missing composer file.');
        } else {
            Runner::message(
                $io,
                'Checking minor version composer updates',
                'composer outdated -Dmn --no-ansi --working-dir="' . $config['composer_path'] . '" "*/*"'
            );
            Runner::message(
                $io,
                'Checking major version composer updates',
                'composer outdated -Dn --no-ansi --working-dir="' . $config['composer_path'] . '" "*/*"  | grep -v "!"'
            );
            
            if (!isset($config['symfony_cli'])) {
                $io->warning('Unable to generate Symfony security reports: Missing Symfony CLI installation.');
            } else {
                Runner::message(
                    $io,
                    'Checking Symfony CLI security',
                    'symfony security:check --dir="' . $config['composer_path'] . '"'
                );
            }
        }
        
        if (!isset($config['drush_major_version'])) {
            $io->warning('Unable to generate Drush module status: Missing drush install.');
        } else {
            $pmSecurity = new PmSecurity();
            Runner::message($io, 'Checking Drupal core and contrib via drush', $pmSecurity->setup());
        }
        
        // @todo: What other type of reporting should be done here? `npm audit`?
    }
}
