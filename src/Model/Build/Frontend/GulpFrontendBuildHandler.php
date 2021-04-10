<?php

namespace Waffle\Model\Build\Frontend;

use Waffle\Helper\CliHelper;
use Waffle\Model\Build\BuildHandlerInterface;
use Waffle\Model\Cli\Runner\Gulp;
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
     * @var Gulp
     */
    private $gulp;

    /**
     * Constructor
     *
     * @param CliHelper $cliHelper
     * @param Npm $npm
     * @param Gulp $gulp
     */
    public function __construct(
        CliHelper $cliHelper,
        Npm $npm,
        Gulp $gulp
    ) {
        $this->cliHelper = $cliHelper;
        $this->npm = $npm;
        $this->gulp = $gulp;
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $process = $this->npm->install();
        $this->cliHelper->outputOrFail($process, 'Running npm install.');

        $process = $this->gulp->build();
        $this->cliHelper->outputOrFail($process, 'Running gulp build.');
    }
}
