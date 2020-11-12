<?php

namespace Waffle;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Model\Command\CommandManager;

class Application extends SymfonyApplication
{
    public const NAME = 'Waffle';
    
    public const VERSION = '1.0.0-alpha';
        
    public const EMOJI_POOL = [
        // Older emojis that should be supported everywhere.
        "\u{1F353}", // Strawberry
        "\u{1F953}", // Bacon
        "\u{1F95A}", // Egg

        // TODO: The below emojis were added in 2019, but are not widely
        // supported yet. Eventually I would like to have these two replace the
        // pool above (or at the very least add to the pool).
        // "\u{1F9C7}", // Waffle
        // "\u{1F9C8}", // Butter
    ];

    public function __construct()
    {
        // Adding some emoji flair for fun.
        $emoji = array_rand(array_flip(self::EMOJI_POOL), 1);
        $name = sprintf('%s %s', $emoji, self::NAME);
        parent::__construct($name, self::VERSION);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $command_manager = new CommandManager();
        $this->addCommands($command_manager->getCommands());

        parent::run();
    }

    /**
     * Overrides the find method to install call get.
     *
     * The default behavior of the application is to call a best guess if it is
     * reasonibly sure it is correct. For example, if a 'phpcs' command existed
     * and the user only typed 'phpc', the base code is smart enough to run
     * 'phpcs' instead.
     *
     * We are suppressing the above behavior because we are creating tasks in a
     * non-standard way. The tasks and recipes defined in the config file do
     * not get picked up properly. This may be revisited, but for now, I think
     * it best to force a hard fail if a command is mistyped.
     *
     * @return Command A Command instance
     *
     * @throws CommandNotFoundException When command does not exist.
     */
    public function find(string $name)
    {
        return $this->get($name);
    }
}
