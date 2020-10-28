<?php

namespace Waffles\Model\Drush;

use Symfony\Component\Process\Process;

class CacheClear extends DrushCommand
{

    public function __construct() {
        // TODO Handle D7 vs D8.
        parent::__construct(['cr']);
    }

}