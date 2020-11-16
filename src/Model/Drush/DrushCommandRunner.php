<?php

namespace Waffle\Model\Drush;

use Symfony\Component\Process\Process;
use Waffle\Model\Drush\DrushCommand;

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
    }

    /**
     * Clears caches for Drupal sites.
     *
     * @return void
     */
    public function clearCaches() {
        $cc = [];

        switch ($this->drupal_major_version) {
            case '7':
                $cc = ['cc', 'all'];
                break;
            case '8':
                $cc = ['cr'];
                break;
            default:
                throw new \Exception(
                    sprintf('Clearing caches with Drush for Drupal %s not supported.', $this->drupal_major_version)
                );
        }

        $cache_clear = new DrushCommand($cc);
        return $cache_clear->run();
    }

}
