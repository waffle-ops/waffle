<?php

namespace Waffle\Command\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Waffle\Command\BaseTask;
use Waffle\Command\DiscoverableTaskInterface;
use Waffle\Model\Build\BackendBuildHandlerFactory;
use Waffle\Model\Config\Item\BuildBackend as BuildBackendConfig;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

class BuildBackend extends BaseTask implements DiscoverableTaskInterface
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
     * @param IOStyle $io
     * @param BackendBuildHandlerFactory $backendBuildHandlerFactory
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        BackendBuildHandlerFactory $backendBuildHandlerFactory
    ) {
        $this->backendBuildHandlerFactory = $backendBuildHandlerFactory;
        parent::__construct($context, $io);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Builds the backend of the project.');
        $this->setHelp('Builds the backend of the project.');

        // TODO Expand the help section.

        $this->addOption(
            BuildBackendConfig::STRATEGY_KEY,
            null,
            InputArgument::OPTIONAL,
            'The strategy used for building the backend (none, composer, ect...)',
            null
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function process(InputInterface $input)
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
     *
     * @param InputInterface $input
     * @return bool|mixed|string|string[]|null
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
