<?php

namespace Waffle\Command\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableTaskInterface;
use Waffle\Model\Build\BackendBuildHandlerFactory;
use Waffle\Model\Config\Item\BuildBackend as BuildBackendConfig;
use Waffle\Model\Context\Context;

class BuildBackend extends BaseCommand implements DiscoverableTaskInterface
{
    public const COMMAND_KEY = 'build-backend';

    /**
     * @var BackendBuildHandlerFactory
     */
    protected $backendBuildHandlerFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param BackendBuildHandlerFactory $siteSyncFactory
     */
    public function __construct(
        Context $context,
        BackendBuildHandlerFactory $backendBuildHandlerFactory
    ) {
        $this->backendBuildHandlerFactory = $backendBuildHandlerFactory;
        parent::__construct($context);
    }

    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Builds the backend of the project.');
        $this->setHelp('Builds the backend of the project.');

        // TODO Expand the help section.

        $this->addOption(
            BuildBackendConfig::STRATEGY_KEY,
            null,
            InputArgument::OPTIONAL,
            'The cms used for this project (drupal7, wordpress, ect...)',
            null
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $strategy = trim($this->getStrategy($input));

        if (empty($strategy)) {
            $this->io->styledText('No backend build strategy provided. Skipping build.', 'error');
            return COMMAND::FAILURE;
        }

        $this->io->highlightText('Running backend build task with strategy %s', [$strategy]);

        $handler = $this->backendBuildHandlerFactory->getHandler($strategy);
        $handler->handle();

        $this->io->styledText('Backend build task complete.');

        return Command::SUCCESS;
    }

    /**
     * Helper method to get the build strategy for the run.
     */
    private function getStrategy(InputInterface $input)
    {
        $strategy = null;

        $config = $this->context->get(self::COMMAND_KEY);

        if (isset($config[BuildBackendConfig::STRATEGY_KEY])) {
            $strategy = $config[BuildBackendConfig::STRATEGY_KEY];
        }

        $strategyOption = $input->getOption(BuildBackendConfig::STRATEGY_KEY);

        if (!empty($strategyOption)) {
            $strategy = $strategyOption;
        }

        return $strategy;
    }
}
