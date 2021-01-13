<?php

namespace Waffle\Model\Git;

class GitAddAll extends GitCommand
{

    public function __construct()
    {
        trigger_error(sprintf('Class %s is deprecated and will be removed in the next release.', __CLASS__));

        parent::__construct(['add', '-A']);
    }
}
