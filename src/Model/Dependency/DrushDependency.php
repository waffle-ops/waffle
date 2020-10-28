<?php

namespace Waffles\Model\Dependency;

use Symfony\Component\Process\Process;

class DrushDependency extends CliDependency {

    public function __construct() {
        parent::__construct(['drush', '--version']);
    }

}