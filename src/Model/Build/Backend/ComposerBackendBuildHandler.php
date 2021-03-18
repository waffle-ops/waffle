<?php

namespace Waffle\Model\Build\Backend;

use Waffle\Model\Build\BuildHandlerInterface;
use Waffle\Model\Cli\Runner\Composer;

class ComposerBackendBuildHandler implements BuildHandlerInterface
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
        // TODO Pass extra arguments here like --no-dev --prefer-dist when it comes time for CI. When the time comes,
        // perhaps we can add a 'type' flag or something and pass via Context to control this behavior. Will need to
        // think through it as options from the command are not passed to Context, so maybe it should work differently.
        $process = $this->composer->install();
        $process->run();
    }
}
