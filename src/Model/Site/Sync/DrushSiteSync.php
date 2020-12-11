<?php

namespace Waffle\Model\Site\Sync;

use Waffle\Model\Cli\Runner\Drush;
use Waffle\Model\Site\Sync\SiteSyncInterface;

class DrushSiteSync implements SiteSyncInterface
{

    /**
     * @var Drush
     */
    private $drush;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->drush = new Drush();
    }

    /**
     * {@inheritdoc}
     */
    public function syncDatabase($alias)
    {
        return $this->drush->syncDatabase($alias);
    }

    /**
     * {@inheritdoc}
     */
    public function syncFiles($alias)
    {
        return $this->drush->syncFiles($alias);
    }

    /**
     * {@inheritdoc}
     */
    public function postSyncLogin()
    {
        return $this->drush->userLogin();
    }

    /**
     * {@inheritdoc}
     */
    public function clearCaches()
    {
        return $this->drush->clearCaches();
    }
}
