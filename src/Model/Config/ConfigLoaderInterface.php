<?php

namespace Waffle\Model\Config;

interface ConfigLoaderInterface
{
    /**
     * Loads the config file.
     *
     * @return array
     */
    public function load();
}
