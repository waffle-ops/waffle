<?php

namespace Waffle\Model\Build\Frontend;

use Waffle\Helper\CliHelper;
use Waffle\Model\Build\BuildHandlerInterface;
use Waffle\Model\Cli\Runner\Npm;

class GulpFrontendBuildHandler implements BuildHandlerInterface
{
    /**
     * @var CliHelper
     */
    private $cliHelper;

    /**
     * @var Npm
     */
    private $npm;

    /**
     * Constructor
     *
     * @param CliHelper $cliHelper
     * @param Npm $npm
     */
    public function __construct(
        CliHelper $cliHelper,
        Npm $npm
    )
    {
        $this->cliHelper = $cliHelper;
        $this->npm = $npm;
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $process = $this->npm->install();
        $this->cliHelper->outputOrFail($process, 'Running npm install.');

        // TODO Run 'gulp build'
    }
}
