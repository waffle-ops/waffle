<?php

namespace Waffle\Model\Git;

class GitCommit extends GitCommand
{
    
    public function __construct($message)
    {
        $message = str_replace("'", "\'", $message);
        $message = str_replace('"', "\"", $message);
        parent::__construct(['commit', "--message={$message}"]);
    }
}
