<?php

namespace Waffle\Command\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\ListCommand as ParentListCommand;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Command\DiscoverableCommandInterface;
use Waffle\Model\IO\IOStyle;

class ListCommand extends ParentListCommand implements DiscoverableCommandInterface
{
    public const COMMAND_KEY = 'list';

    /**
     * Defines the Input/Output helper object.
     *
     * @var IOStyle
     */
    protected $io;

    /**
     * Constructor
     *
     * @param IOStyle $io
     */
    public function __construct(IOStyle $io)
    {
        $this->io = $io;
        parent::__construct();
    }

    protected function configure()
    {
        parent::configure();
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Lists commands, tasks, and recipes.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();

        $this->io->newLine();
        $this->io->highlightText('Waffle (%s)', [$application->getVersion()], 'comment', 'info');
        $this->io->newLine();

        global $argv;
        $waffle_bin = basename(realpath($argv[0]));

        $this->io->styledText('Usage:', 'comment');
        $this->io->writeln(sprintf(
            '  %s [command|task|recipe] [options] [arguments]',
            $waffle_bin
        ));

        // We are injecting commands in the DI via manager classes that are
        // referenced in the command loader class. Injecting the command loader
        // in a constructor basically creates an infinite loop. So, will just
        // grab it from the parent application.
        $commandLoader = $application->getCommandLoader();

        $commandManager = $commandLoader->getCommandManager();
        $taskManager = $commandLoader->getTaskManager();
        $recipeManager = $commandLoader->getRecipeManager();

        $commands = $commandManager->getCommands();
        $tasks = $taskManager->getCommands();
        $recipes = $recipeManager->getCommands();

        $lists = [
            $this->buildList($commands, 'Available commands:'),
            $this->buildList($tasks, 'Available tasks:'),
            $this->buildList($recipes, 'Available recipes:'),
        ];

        $rows = $this->buildRows($lists);

        $this->io->table([], $rows, 'borderless');

        $this->io->highlightText(
            'For more information about the commands, tasks, and recipes, try running the %s command.',
            ['help']
        );

        $example = sprintf('%s help list', $waffle_bin);
        $this->io->highlightText(
            'Example: %s',
            [$example]
        );

        $this->io->newLine();

        return Command::SUCCESS;
    }

    /**
     * buildList
     *
     * Helper method to construct lists of commands, tasks, and recipes.
     *
     * @param array $commands
     *   Keyed array of command names => descriptions.
     * @param string $title
     *   The title of the cell.
     */
    private function buildList($commands, $title)
    {
        ksort($commands);

        $header = sprintf('<comment>%s</comment>', $title);

        $data = [];

        $data[] = [$header, ''];

        foreach ($commands as $commandKey => $command) {
            if (!$command->isEnabled()) {
                continue;
            }

            $data[] = [
                sprintf('<info>%s</info>', $commandKey),
                $command->getDescription()
            ];
        }

        // Return nothing if commands were disabled.
        if (count($data) == 1) {
            return [];
        }

        return $data;
    }

    /**
     * buildRows
     *
     * Helper method to build the rows to display in the table.
     *
     * @param array
     *   List of arrays to condense into a single array with table seperator
     *   elements in between.
     *
     * @return array
     */
    private function buildRows($lists)
    {
        $rows = [];

        foreach ($lists as $group) {
            // We may have empty groups if there is no config file.
            if (empty($group)) {
                continue;
            }

            foreach ($group as $element) {
                $rows[] = $element;
            }

            $rows[] = new TableSeparator();
        }

        // Need to remove extra table divide.
        array_pop($rows);

        return $rows;
    }
}
