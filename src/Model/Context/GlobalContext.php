<?php

namespace Waffle\Model\Context;

use Waffle\Model\Config\GlobalConfigLoader;

class GlobalContext extends BaseContext
{

    /**
     * Constructor
     *
     * @param GlobalConfigLoader
     */
    public function __construct(GlobalConfigLoader $globalConfigLoader)
    {
        parent::__construct($globalConfigLoader);
    }
}
