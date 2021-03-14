<?php

namespace Waffle\Model\Build\Backend;

use Waffle\Model\Build\BackendHandlerInterface;
use Waffle\Model\Cli\Runner\Composer;

class ComposerBackendHandler implements BackendHandlerInterface
{
    /**
     * @var Composer
     */
    protected $composer;

    /**
     * Constructor
     *
     * @param Composer $composer
     */
    public function __construct(
        Composer $composer
    ) {
        $this->composer = $composer;
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        // TODO Pass extra arguments here like --no-dev --prefer-dist.
        $process = $this->composer->install();
        $process->run();
    }
}
