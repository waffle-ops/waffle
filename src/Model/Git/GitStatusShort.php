<?php

namespace Waffle\Model\Git;

class GitStatusShort extends GitCommand
{

    public function __construct()
    {
        parent::__construct(['status', '--short']);
    }
}
