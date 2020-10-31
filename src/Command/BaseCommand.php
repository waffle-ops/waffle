<?php

namespace Waffle\Command;

use Symfony\Component\Console\Command\Command;
use Waffle\Model\Config\ProjectConfig;

class BaseCommand extends Command
{
    
    /**
     * getConfig
     *
     * Gets the project configuration from the ProjectConfig singleton.
     *
     * @return array
     */
    protected function getConfig()
    {
        $project_config = ProjectConfig::getInstance();
        return $project_config->getProjectConfig();
    }
}
