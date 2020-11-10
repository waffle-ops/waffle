<?php

namespace Waffle\Model\Drush;

use Symfony\Component\Process\Process;

class PmSecurity extends DrushCommand
{
    
    public function __construct()
    {
        $command = ['pm:security', '--no-ansi'];
        if ($this->projectConfig['drush_major_version'] == '8') {
            $command = ['ups', '--check-disabled'];
        }
        
        parent::__construct($command);
    }
}
