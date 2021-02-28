<?php

namespace Waffle\Model\Config;

use Symfony\Component\Yaml\Yaml;

abstract class BaseConfigLoader implements ConfigLoaderInterface
{
    /**
     * Loads the config file.
     *
     * @return array
     */
    public function load()
    {
        $config = $this->getConfigFile();
        return $this->project_config = Yaml::parseFile($config);
    }

    /**
     * Gets the config file path to be loaded.
     *
     * @return string
     */
    abstract protected function getConfigFile();
}
