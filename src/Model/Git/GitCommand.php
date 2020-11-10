<?php

namespace Waffle\Model\Git;

use Waffle\Model\Config\ProjectConfig;
use Symfony\Component\Process\Process;

class GitCommand
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
    public function __construct(array $args)
    {
        $this->args = $args;
        
        $project_config = ProjectConfig::getInstance();
        $this->projectConfig = $project_config->getProjectConfig();
    }
    
    public function setup($input = '')
    {
        $args = array_unshift($this->args, 'git');
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
