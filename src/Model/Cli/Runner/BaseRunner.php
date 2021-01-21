<?php

namespace Waffle\Model\Cli\Runner;

use Waffle\Model\IO\IO;
use Waffle\Traits\ConfigTrait;
use Waffle\Model\Config\ProjectConfig;

class BaseRunner
{
    use ConfigTrait;
    
    /**
     * @var IO
     */
    protected $io;
    
    /**
     * A reference to the project config.
     *
     * @var ProjectConfig
     */
    protected $config;
    
    /**
     *  Constructor
     */
    public function __construct()
    {
        $this->io = IO::getInstance()->getIO();
        $this->config = $this->getConfig();
    }
}
