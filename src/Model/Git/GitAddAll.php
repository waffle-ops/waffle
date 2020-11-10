<?php

namespace Waffle\Model\Git;

class GitAddAll extends GitCommand
{

    public function __construct()
    {
        parent::__construct(['add', '-A']);
    }
}
