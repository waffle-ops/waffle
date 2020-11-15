<?php

namespace Waffle\Model\Drush;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Output\ConsoleOutput;
use Waffle\Traits\ConfigTrait;

class PmSecurity extends DrushCommand
{
    use ConfigTrait;

    public function __construct()
    {
        parent::__construct();

        $command = ['pm:security', '--no-ansi'];

        $config = $this->getConfig()->getProjectConfig();
        if ($config['drush_major_version'] == '8') {
            $command = ['ups', '--check-disabled'];
        }

        $this->setArgs($command);
    }
}
