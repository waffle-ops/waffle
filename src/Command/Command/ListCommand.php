<?php

namespace Waffle\Command\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableCommandInterface;
use Waffle\Helper\CliHelper;
use Waffle\Model\Cli\WaffleCommand;
use Waffle\Model\Config\ProjectConfig;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;

class ListCommand extends BaseCommand implements DiscoverableCommandInterface
{
    public const COMMAND_KEY = 'list';

    private $processes = [];

    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Lists commands, tasks, and recipes.');
        $this->setHelp('TODO.'); // TODO
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();

        $this->io->newLine();
        $this->io->highlightText('Waffle (%s)', [$application->getVersion()], 'comment', 'info');
        $this->io->newLine();

        global $argv;
        $waffle_bin = basename(realpath($argv[0]));

        $this->io->styledText(' Usage:', 'comment');
        $this->io->writeln(sprintf(
            '   %s [command|task|recipe] [options] [arguments]',
            $waffle_bin
        ));

        // We are injecting commands in the DI via manager classes that are
        // referenced in the command loader class. Injectin the command loader
        // in a constructor basically creates an infinite loop. So, will just
        //grab it from the parent application.
        $commandLoader = $application->getCommandLoader();

        $commandManager = $commandLoader->getCommandManager();
        $taskManager = $commandLoader->getTaskManager();
        $recipeManager = $commandLoader->getRecipeManager();

        $commands = $commandManager->getCommands();
        $tasks = $taskManager->getCommands();
        $recipes = $recipeManager->getCommands();

        $lists = [
            $this->buildList($commands, 'Avaliable commands:'),
            $this->buildList($tasks, 'Avaliable tasks:'),
            $this->buildList($recipes, 'Avaliable recipes:'),
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
            $data[] = [
                sprintf('  <info>%s</info>', $commandKey),
                $command->getDescription()
            ];
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
