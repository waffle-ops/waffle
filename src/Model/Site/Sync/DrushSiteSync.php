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
    public function __construct()
    {
        $this->drushRunner = new DrushCommandRunner();
    }

    /**
     * {@inheritdoc}
     */
    public function syncDatabase($alias)
    {
        return $this->drushRunner->syncDatabase($alias);
    }

    /**
     * {@inheritdoc}
     */
    public function syncFiles($alias)
    {
        return $this->drushRunner->syncFiles($alias);
    }

    /**
     * {@inheritdoc}
     */
    public function postSyncLogin()
    {
        return $this->drushRunner->userLogin();
    }

    /**
     * {@inheritdoc}
     */
    public function clearCaches()
    {
        return $this->drushRunner->clearCaches();
    }
}
