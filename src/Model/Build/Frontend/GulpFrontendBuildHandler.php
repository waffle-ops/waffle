<?php

namespace Waffle\Model\Build\Frontend;

use Waffle\Helper\CliHelper;
use Waffle\Model\Build\BuildHandlerInterface;

class GulpFrontendBuildHandler implements BuildHandlerInterface
{
    /**
     * @var CliHelper
     */
    private $cliHelper;

    /**
     * Constructor
     *
     * @param CliHelper $cliHelper
     */
    public function __construct(
        CliHelper $cliHelper
    )
    {
        $this->cliHelper = $cliHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        // TODO
    }
}
