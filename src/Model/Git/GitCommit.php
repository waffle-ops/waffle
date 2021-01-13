<?php

namespace Waffle\Model\Git;

class GitCommit extends GitCommand
{

    public function __construct($message)
    {
        trigger_error(sprintf('Class %s is deprecated and will be removed in the next release.', __CLASS__));

        $message = str_replace("'", "\'", $message);
        $message = str_replace('"', "\"", $message);
        parent::__construct(['commit', "--message={$message}"]);
    }
}
