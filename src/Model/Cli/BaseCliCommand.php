<?php

namespace Waffle\Model\Cli;

use Symfony\Component\Process\Process;
use Exception;
use Waffle\Traits\ConfigTrait;
use Waffle\Model\Config\ProjectConfig;

class BaseCliCommand
{
    use ConfigTrait;
    
    /**
     * @var string[]
     */
    private $args = [];
    
    /**
     * @var Process
     */
    private $process;
    
    /**
     * A reference to the project config.
     *
     * @var ProjectConfig
     */
    protected $config;
    
    /**
     * Constructor
     *
     * @param string[] The Arguments.
     *
     * @throws Exception
     */
    public function __construct(array $args)
    {
        if (empty($args)) {
            throw new Exception('Invalid Arguments: You must pass at least one argument.');
        }
        
        $this->config = $this->getConfig();
        
        // @todo: check for config prefix
        if (!empty($this->config->getCommandPrefix())) {
            array_unshift($args, $this->config->getCommandPrefix());
        }
        
        $this->process = new Process($args);
    }

    /**
     * Gets the process.
     *
     * @return Process
     */
    public function getProcess(): Process
    {
        return $this->process;
    }
}
