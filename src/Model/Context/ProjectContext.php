<?php

namespace Waffle\Model\Context;

use Waffle\Model\Config\ProjectConfigLoader;

class ProjectContext extends BaseContext
{

    /**
     * Constructor
     *
     * @param ProjectConfigLoader
     */
    public function __construct(ProjectConfigLoader $projectConfigLoader)
    {
        parent::__construct($projectConfigLoader);
    }
}
