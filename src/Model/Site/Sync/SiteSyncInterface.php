<?php

namespace Waffle\Model\Site\Sync;

use Symfony\Component\Process\Process;

interface SiteSyncInterface
{

    /**
     * Syncs the database from the remote site to the local site.
     *
     * @return Process
     */
    public function syncDatabase($alias);

    /**
     * Syncs the database from the remote site to the local site.
     *
     * @return Process
     */
    public function syncFiles();

    /**
     * Runs local release script after a database sync.
     *
     * @return Process
     */
    public function postSyncRelease();

    /**
     * Automatically logs the user into the site after a database sync.
     *
     * @return Process
     */
    public function postSyncLogin();

    /**
     * Clears the site caches.
     *
     * @return Process
     */
    public function clearCaches();
}
