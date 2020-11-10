<?php

namespace Waffle\Model\Drush;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Output\ConsoleOutput;

class PmSecurity extends DrushCommand
{
    
    public function __construct()
    {
        parent::__construct();
        
        $command = ['pm:security', '--no-ansi'];
        if ($this->projectConfig['drush_major_version'] == '8') {
            $command = ['ups', '--check-disabled'];
        }
        
        $this->setArgs($command);
    }
}
