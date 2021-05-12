<?php

namespace Waffle\Model\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

abstract class BaseConfigLoader implements ConfigLoaderInterface, ConfigurationInterface
{
    /**
     * Loads the config file.
     *
     * @return array
     */
    public function load()
    {
        $configFile = $this->getConfigFile();
        $rawConfig = Yaml::parseFile($configFile);
        $processor = new Processor();
        $config = $processor->processConfiguration($this, [$rawConfig]);
        return $config;
    }

    /**
     * Gets the config file path to be loaded.
     *
     * @return string
     */
    abstract protected function getConfigFile();

    /**
     * {@inheritdoc}
     */
    abstract public function getConfigTreeBuilder();
}
