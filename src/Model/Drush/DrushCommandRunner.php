<?php

namespace Waffle\Model\Drush;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Exception;
use Waffle\Model\IO\IO;

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
        $this->io = IO::getInstance()->getIO();

        // Calling 'drush status --format=json' will give us a json blob that
        // we can parse to get info about the site.
        $status = new DrushCommand(['status', '--format=json']);
        $process = $status->getProcess();
        $process->run();
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

        // Attempt a DB connection / verify that local settings are present.
        $this->ensureLocalSettings();
        $this->validateDbAccess();
    }

    /**
     * Resets the local database.
     *
     * @return Process
     */
    private function resetDatabase()
    {
        $db_reset = new DrushCommand(['sql-create', '-y']);
        $process = $db_reset->getProcess();
        $process->run();
        return $process;
    }

    /**
     * Gets a database dump from the provided alias.
     *
     * @return Process
     */
    private function getDatabaseDump($alias)
    {
        $db_export =  new DrushCommand([$alias, 'sql-dump']);
        $process = $db_export->getProcess();
        $process->run();
        return $process;
    }

    /**
     * Imports dumped sql into the database.
     *
     * @return Process
     */
    private function importDatabase($sql)
    {
        $db_import = new DrushCommand(['sql-cli']);
        $process = $db_import->getProcess();
        $process->setInput($sql);
        $process->run();
        return $process;
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
        $process = $file_sync->getProcess();
        $process->run();
        return $process;
    }

    /**
     * Attempts to log user into the local site.
     *
     * @return Process
     */
    public function userLogin()
    {
        $uli = new DrushCommand(['uli']);
        $process = $uli->getProcess();
        $process->run();
        return $process;
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
        $process = $cache_clear->getProcess();
        $process->run();
        return $process;
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

        $pm_security = new DrushCommand($args);
        $process = $pm_security->getProcess();
        $process->run();
        return $process;
    }

    /**
     * Runs any pending database updates.
     *
     * @return Process
     */
    public function updateDatabase()
    {
        $updb = new DrushCommand(['updb', '-y']);
        $process = $updb->getProcess();
        $process->run();
        return $process;
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

        $cex = new DrushCommand(['cex', '-y', $config_key]);
        $process = $cex->getProcess();
        $process->run();
        return $process;
    }

    /**
     * Validates database access.
     *
     * @return void
     * @throws Exception
     */
    private function validateDbAccess()
    {
        $status = new DrushCommand(['sql:query', 'select 1']);
        $process = $status->getProcess();
        $process->run();
        $output = $process->getOutput();

        // The sql:query command returns a 1 even if unsuccessful. Explicitly
        // checking the output.
        if (trim($output) !== '1') {
            $error = 'Unable to connect to database. Make sure that your local ';
            $error .= 'settings file is present and has valid database connection details.';
            throw new Exception($error);
        }
    }

    /**
     * Checks for the local settings file. Will attempt to create it if needed.
     *
     * @return void
     * @throws Exception
     */
    private function ensureLocalSettings()
    {
        // Look for local settings file.
        $finder = new Finder();
        $finder->files();
        $finder->in(getcwd());
        $finder->name('settings.local.php');

        if ($finder->hasResults()) {
            return;
        }

        $this->io->warning('Local settings not found, copying from example.settings.local.php');

        // Attempt to add local settings file from example.
        $finder = new Finder();
        $finder->files();
        $finder->in(getcwd());
        $finder->name('example.settings.local.php');

        if (!$finder->hasResults()) {
            throw new Exception('Unable to find example.settings.local.php file.');
        }

        $iterator = $finder->getIterator();
        $iterator->rewind();
        $file = $iterator->current();
        $example_settings = $file->getRealPath();
        $local_settings = $file->getPath() . '/default/settings.local.php';

        if (!copy($example_settings, $local_settings)) {
            throw new Exception('Unable to create settings.local.php');
        }
    }
}
