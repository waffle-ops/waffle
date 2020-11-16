<?php

namespace Waffle\Model\Drush;

use Symfony\Component\Process\Process;

class ConfigExport extends DrushCommand
{

    public function __construct($config_key)
    {
        trigger_error('Warning: Drush command classes have been deprecated. Use DrushCommandRunner instead.');
        $command = ['cex', '--no-ansi', '-y', $config_key];
        parent::__construct($command);
    }
}
