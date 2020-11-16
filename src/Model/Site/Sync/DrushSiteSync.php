<?php

namespace Waffle\Model\Site\Sync;

use Waffle\Model\Drush\DrushCommandRunner;
use Waffle\Model\Site\Sync\SiteSyncInterface;

class DrushSiteSync implements SiteSyncInterface
{

    /**
     * @var DrushCommandRunner
     */
    private $drushRunner;

    /**
     * Constructor
     */
    public function __construct() {
        $this->drushRunner = new DrushCommandRunner();
    }

    /**
     * {@inheritdoc}
     */
    public function syncDatabase($alias) {
        return $this->drushRunner->syncDatabase($alias);
    }

    /**
     * {@inheritdoc}
     */
    public function syncFiles() {


    }

    /**
     * {@inheritdoc}
     */
    public function postSyncRelease() {


    }

    /**
     * {@inheritdoc}
     */
    public function postSyncLogin() {

    }

    /**
     * {@inheritdoc}
     */
    public function clearCaches() {
        return $this->drushRunner->clearCaches();
    }
}
