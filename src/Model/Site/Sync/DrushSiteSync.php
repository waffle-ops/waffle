<?php

namespace Waffle\Model\Site\Sync;

use Waffle\Model\Cli\Runner\Drush;

class DrushSiteSync implements SiteSyncInterface
{

    /**
     * @var Drush
     */
    private $drush;

    /**
     * Constructor
     *
     * @param Drush $drush
     */
    public function __construct(Drush $drush)
    {
        $this->drush = $drush;
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
