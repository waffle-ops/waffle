<?php

namespace Waffle\Traits;

use Waffle\Model\Config\ProjectConfig;

trait ConfigTrait
{

    /**
     * getConfig
     *
     * Gets the project configuration from the ProjectConfig singleton.
     *
     * @return ProjectConfig
     */
    protected function getConfig()
    {
        $project_config = ProjectConfig::getInstance();
        return $project_config;
    }
}
