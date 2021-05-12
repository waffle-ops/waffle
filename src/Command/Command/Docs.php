<?php

namespace Waffle\Command\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Waffle\Application as Waffle;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableCommandInterface;
use Waffle\Helper\BrowserHelper;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

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
     * @param IOStyle $io
     * @param BrowserHelper $browserHelper
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        BrowserHelper $browserHelper
    ) {
        $this->browserHelper = $browserHelper;
        parent::__construct($context, $io);
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
    protected function process(InputInterface $input)
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
