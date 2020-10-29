<?php

namespace Waffles\Command;

use Waffles\Command\Dependency\CheckDependencies;
use Waffles\Command\Misc\HelloworldCommand;
use Waffles\Command\Site\Sync;
use Waffles\Command\Site\Db;
use Waffles\Command\Site\Files;
use Waffles\Command\Site\Login;
use Waffles\Command\Site\Release;

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
