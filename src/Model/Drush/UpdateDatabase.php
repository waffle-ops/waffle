<?php

namespace Waffle\Model\Drush;

class UpdateDatabase extends DrushCommand
{

    public function __construct()
    {
        // TODO Handle D7 vs D8.
        parent::__construct(['updb', '-y']);
    }
}
