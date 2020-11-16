<?php

namespace Waffle\Model\Drush;

use Symfony\Component\Process\Process;
use Exception;

class DrushCommandRunner
{

    /**
     * @var string
     */
    private $drush_major_version;

    /**
     * @var string
     */
    private $drupal_major_version;

    /**
     *  Constructor
     */
    public function __construct()
    {
        // Calling 'drush status --format=json' will give us a json blob that
        // we can parse to get info about the site.
        $status = new DrushCommand(['status', '--format=json']);
        $process = $status->run();
        $json = $process->getOutput();

        $drush_status_data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // TODO: Throw and handle an exception for this.
        }

        if (isset($drush_status_data['drush-version'])) {
            $parts = explode('.', $drush_status_data['drush-version']);
            $this->drush_major_version = $parts[0];
        } else {
            // TODO: Throw and handle an exception for this.
        }

        if (isset($drush_status_data['drupal-version'])) {
            $parts = explode('.', $drush_status_data['drupal-version']);
            $this->drupal_major_version = $parts[0];
        } else {
            // TODO: Throw and handle an exception for this.
        }

        // TODO: Store status JSON. Would be useful for 'uli' calls to check
        // for the baseurl. Would also be useful for any instances with an
        // alias. We could try to verify the alias is working.
    }

    /**
     * Resets the local database.
     *
     * @return Process
     */
    private function resetDatabase()
    {
        $db_reset = new DrushCommand(['sql-create', '-y']);
        return $db_reset->run();
    }

    /**
     * Gets a database dump from the provided alias.
     *
     * @return Process
     */
    private function getDatabaseDump($alias)
    {
        $db_export =  new DrushCommand([$alias, 'sql-dump']);
        return $db_export->run();
    }

    /**
     * Imports dumped sql into the database.
     *
     * @return Process
     */
    private function importDatabase($sql)
    {
        $db_import = new DrushCommand(['sql-cli']);
        return $db_import->run($sql);
    }

    /**
     * Syncs the database from the remote alias to local.
     *
     * @return Process
     */
    public function syncDatabase($alias)
    {
        $this->resetDatabase();
        $dump = $this->getDatabaseDump($alias);
        $sql = $dump->getOutput();
        $this->importDatabase($sql);
        $this->clearCaches();
    }

    /**
     * Downloads the files for Drupal sites.
     *
     * @return Process
     */
    public function syncFiles($alias)
    {
        $file_sync = new DrushCommand(['-y', 'core-rsync', $alias, 'sites/default/files']);
        return $file_sync->run();
    }

    /**
     * Attempts to log user into the local site.
     *
     * @return Process
     */
    public function userLogin()
    {
        $uli = new DrushCommand(['uli']);
        return $uli->run();
    }

    /**
     * Clears caches for Drupal sites.
     *
     * @return Process
     */
    public function clearCaches()
    {
        $cc = [];

        switch ($this->drupal_major_version) {
            case '7':
                $cc = ['cc', 'all'];
                break;
            case '8':
                $cc = ['cr'];
                break;
            default:
                throw new Exception(
                    sprintf('Clearing caches with Drush for Drupal %s not supported.', $this->drupal_major_version)
                );
        }

        $cache_clear = new DrushCommand($cc);
        return $cache_clear->run();
    }
    
    /**
     * Checks for pending security (Drush 9) and non-security (Drush 8) updates.
     *
     * @param string $format
     * @return Process
     * @throws Exception
     */
    public function pmSecurity($format = 'table')
    {
        $args = [];
        switch ($this->drush_major_version) {
            case '8':
                $args = ['ups', '--check-disabled', "--format={$format}"];
                break;
            case '9':
                $args = ['pm:security', "--format={$format}"];
                break;
            default:
                throw new Exception(
                    sprintf(
                        'Checking pending updates with Drush for Drush %s not supported.',
                        $this->drush_major_version
                    )
                );
        }
        
        $process = new DrushCommand($args);
        return $process->run();
    }
    
    /**
     * Runs any pending database updates.
     *
     * @return Process
     */
    public function updateDatabase()
    {
        $process = new DrushCommand(['updb', '-y']);
        return $process->run();
    }
    
    /**
     * Exports any changed config back to the filesystem.
     *
     * @param string $config_key
     * @return Process
     * @throws Exception
     */
    public function configExport($config_key = 'sync')
    {
        if ($this->drupal_major_version <= 7) {
            throw new Exception(
                sprintf('Exporting config with Drush for Drupal %s not supported.', $this->drupal_major_version)
            );
        }
        
        $process = new DrushCommand(['cex', '--no-ansi', '-y', $config_key]);
        return $process->run();
    }
}
