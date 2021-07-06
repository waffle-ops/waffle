<?php

namespace Waffle\Command\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Waffle\Command\BaseTask;
use Waffle\Command\DiscoverableTaskInterface;
use Waffle\Model\Build\FrontendBuildHandlerFactory;
use Waffle\Model\Config\Item\BuildFrontend as BuildFrontendConfig;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

class BuildFrontend extends BaseTask implements DiscoverableTaskInterface
{
    public const COMMAND_KEY = 'build-frontend';

    /**
     * @var FrontendBuildHandlerFactory
     */
    protected $frontendBuildHandlerFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param IOStyle $io
     * @param FrontendBuildHandlerFactory $frontendBuildHandlerFactory
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        FrontendBuildHandlerFactory $frontendBuildHandlerFactory
    ) {
        $this->frontendBuildHandlerFactory = $frontendBuildHandlerFactory;
        parent::__construct($context, $io);
    }

    protected function configure()
    {
        parent::configure();
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Builds the frontend of the project.');
        $this->setHelp('Builds the frontend of the project.');

        // TODO Expand the help section.

        $this->addOption(
            BuildFrontendConfig::STRATEGY_KEY,
            null,
            InputArgument::OPTIONAL,
            'The strategy used for building the frontend (none, gulp, compass, etc...)',
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
            $this->io->styledText('No frontend build strategy provided. Skipping build.', 'error');
            return COMMAND::FAILURE;
        }

        $this->io->highlightText('Running frontend build task with strategy %s', [$strategy]);

        $handler = $this->frontendBuildHandlerFactory->getHandler($strategy);
        $handler->handle();

        $this->io->styledText('Frontend build task complete.');

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

        if (isset($config[BuildFrontendConfig::STRATEGY_KEY])) {
            $strategy = $config[BuildFrontendConfig::STRATEGY_KEY];
        }

        $strategyOption = $input->getOption(BuildFrontendConfig::STRATEGY_KEY);

        if (!empty($strategyOption)) {
            $strategy = $strategyOption;
        }

        return $strategy;
    }
}
