<?php

namespace Waffle\Command\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Application as Waffle;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableCommandInterface;
use Waffle\Helper\BrowserHelper;

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
     * @param BrowserHelper $browserHelper
     */
    public function __construct(BrowserHelper $browserHelper)
    {
        $this->browserHelper = $browserHelper;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Opens a web browser to the Waffle documentation.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->browserHelper->openBrowser(Waffle::DOCS_URL);
        } catch (\Exception $e) {
            $this->io->warning($e->getMessage());
        }

        $this->io->styledText('Go check out the Waffle documentation!');
        $this->io->highlightText('Here is a link: %s', [Waffle::DOCS_URL]);

        return Command::SUCCESS;
    }
}
