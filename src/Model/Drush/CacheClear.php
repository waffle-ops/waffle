<?php

namespace Waffle\Model\Drush;

class CacheClear extends DrushCommand
{

    public function __construct()
    {
        parent::__construct();
    
        $command = ['cc', 'all'];
        if ($this->projectConfig['cms'] == 'drupal8') {
            $command = ['cr'];
        }
    
        $this->setArgs($command);
    }
}
