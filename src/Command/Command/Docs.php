<?php

namespace Waffle\Command\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Application as Waffle;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableCommandInterface;
use Waffle\Helper\BrowserHelper;
use Waffle\Model\Context\Context;

class Docs extends BaseCommand implements DiscoverableCommandInterface
{
    public const COMMAND_KEY = 'docs';

    /**
     * @var BrowserHelper
     */
    private $browserHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param BrowserHelper $browserHelper
     */
    public function __construct(Context $context, BrowserHelper $browserHelper)
    {
        $this->browserHelper = $browserHelper;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Opens a web browser to the Waffle documentation.');

        $this->addOption(
            'no-browser',
            null,
            InputOption::VALUE_NONE,
            'Prevents Waffle from attempting to open a browser tab to the docs page.'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $skipBrowser = $input->getOption('no-browser');

        if (!$skipBrowser) {
            try {
                $this->browserHelper->openBrowser(Waffle::DOCS_URL);
            } catch (\Exception $e) {
                $this->io->warning($e->getMessage());
            }
        }

        $this->io->styledText('Go check out the Waffle documentation!');
        $this->io->highlightText('Here is a link: %s', [Waffle::DOCS_URL]);

        return Command::SUCCESS;
    }
}
