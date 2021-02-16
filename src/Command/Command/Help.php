<?php

namespace Waffle\Command\Command;

use Symfony\Component\Console\Command\HelpCommand;
use Waffle\Command\DiscoverableCommandInterface;

class Help extends HelpCommand implements DiscoverableCommandInterface
{
    public const COMMAND_KEY = 'help';
}
