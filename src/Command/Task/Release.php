<?php

namespace Waffle\Command\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableTaskInterface;
use Waffle\Model\Cli\Factory\GenericCommandFactory;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

class Release extends BaseCommand implements DiscoverableTaskInterface
{
    public const COMMAND_KEY = 'release-script';

    /**
     * @var GenericCommandFactory
     */
    private $genericCommandFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param IOStyle $io
     * @param GenericCommandFactory $genericCommandFactory
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        GenericCommandFactory $genericCommandFactory
    ) {
        $this->genericCommandFactory = $genericCommandFactory;
        parent::__construct($context, $io);
    }

    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Runs the release script after syncing from upstream.');
        $this->setHelp('Runs the release script after syncing from upstream.');

        $this->addOption(
            'script',
            null,
            InputArgument::OPTIONAL,
            'The script to execute.',
            '../private/scripts/release.sh'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $script = $input->getOption('script');

        if (!file_exists($script)) {
            $this->io->warning(sprintf('Script %s not found. Skipping.', $script));

            // Returning a success since this is not a critical failure.
            return Command::SUCCESS;
        }

        $command = $this->genericCommandFactory->create([$script]);
        $process = $command->getProcess();

        // Getting real time output from the script.
        $process->run(function ($type, $buffer) {
            $this->io->text($buffer);
        });

        if (!$process->isSuccessful()) {
            $this->io->error('Release script did not exit cleanly. See output above.');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
