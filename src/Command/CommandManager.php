<?php

namespace Waffle\Command;

use Waffle\Command\Dependency\CheckDependencies;
use Waffle\Command\Misc\HelloworldCommand;
use Waffle\Command\Site\Sync;
use Waffle\Command\Site\Db;
use Waffle\Command\Site\Files;
use Waffle\Command\Site\Login;
use Waffle\Command\Site\Release;

class CommandManager
{

    public function getCommands()
    {
        return [
            // Site sync commands.
            new Sync(),
            new Db(),
            new Files(),
            new Login(),
            new Release(),
        ];
    }
}
