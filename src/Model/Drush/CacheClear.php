<?php

namespace Waffle\Model\Drush;

class CacheClear extends DrushCommand
{

    public function __construct()
    {
        trigger_error('Warning: Drush command classes have been deprecated. Use DrushCommandRunner instead.');

        $command = ['cc', 'all'];
        if ($this->projectConfig['cms'] == 'drupal8') {
            $command = ['cr'];
        }

        $this->setArgs($command);
    }
}
