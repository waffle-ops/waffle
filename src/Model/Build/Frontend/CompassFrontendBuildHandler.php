<?php

namespace Waffle\Model\Build\Frontend;

use Waffle\Helper\CliHelper;
use Waffle\Model\Build\BuildHandlerInterface;
use Waffle\Model\Cli\Runner\Compass;
use Waffle\Model\Cli\Runner\Npm;

class CompassFrontendBuildHandler implements BuildHandlerInterface
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
     * @var Compass
     */
    private $compass;

    /**
     * Constructor
     *
     * @param CliHelper $cliHelper
     * @param Npm $npm
     * @param Compass $compass
     */
    public function __construct(
        CliHelper $cliHelper,
        Npm $npm,
        Compass $compass
    ) {
        $this->cliHelper = $cliHelper;
        $this->npm = $npm;
        $this->compass = $compass;
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        // @todo: install dependencies (gem install, etc)

        $process = $this->compass->compile();
        $this->cliHelper->outputOrFail($process, 'Running compass compile.');
    }
}
