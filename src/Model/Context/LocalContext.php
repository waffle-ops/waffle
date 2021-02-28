<?php

namespace Waffle\Model\Context;

use Waffle\Model\Config\LocalConfigLoader;

class LocalContext extends BaseContext
{

    /**
     * Constructor
     *
     * @param LocalConfigLoader
     */
    public function __construct(LocalConfigLoader $localConfigLoader)
    {
        parent::__construct($localConfigLoader);
    }
}
