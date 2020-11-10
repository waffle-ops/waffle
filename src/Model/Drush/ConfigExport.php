<?php

namespace Waffle\Model\Drush;

use Symfony\Component\Process\Process;

class ConfigExport extends DrushCommand
{
    
    public function __construct($config_key)
    {
        $command = ['cex', '--no-ansi', '-y', $config_key];
        parent::__construct($command);
    }
}
