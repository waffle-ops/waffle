<?php

namespace Waffle\Model\Context;

use Waffle\Exception\Config\MissingConfigFileException;
use Waffle\Model\Config\ConfigLoaderInterface;

class BaseContext
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Constructor
     *
     * @param ConfigLoaderInterface
     */
    public function __construct(ConfigLoaderInterface $configLoader)
    {
        try {
            $this->config = $configLoader->load();
        } catch (MissingConfigFileException $e) {
            $this->config = [];
        }
    }

    /**
     * Gets the configuration for this context.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Check if context is avaliable. Returns true if so, false otherwise.
     *
     * @return bool
     */
    public function isAvaliable()
    {
        return !empty($this->config);
    }
}
