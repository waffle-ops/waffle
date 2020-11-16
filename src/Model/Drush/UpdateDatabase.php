<?php

namespace Waffle\Model\Drush;

class UpdateDatabase extends DrushCommand
{

    public function __construct()
    {
        trigger_error('Warning: Drush command classes have been deprecated. Use DrushCommandRunner instead.');
        parent::__construct(['updb', '-y']);
    }
}
