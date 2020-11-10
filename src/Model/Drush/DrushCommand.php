<?php

namespace Waffle\Model\Drush;

use Symfony\Component\Process\Process;
use Waffle\Model\Config\ProjectConfig;

class DrushCommand
{
    /**
     * @var string[]
     */
    private $args = [];
    
    /**
     * @var array
     */
    protected $projectConfig;
    
    /**
     *
     */
    public function __construct(array $args = null)
    {
        if (!empty($args)) {
            $this->args = $args;
        }
        
        $project_config = ProjectConfig::getInstance();
        $this->projectConfig = $project_config->getProjectConfig();
    }
    
    protected function setArgs(array $args)
    {
        $this->args = $args;
    }
    
    public function setup($input = '')
    {
        $args = array_unshift($this->args, 'drush');
        $process = new Process($this->args);
        
        if (!empty($input)) {
            $process->setInput($input);
        }
        
        // TODO Check for error codes / standard errors.
        
        return $process;
    }
    
    public function run($input = '')
    {
        $process = $this->setup($input);
        $process->run();
        
        // TODO Check for error codes / standard errors.
        
        return $process;
    }
}
