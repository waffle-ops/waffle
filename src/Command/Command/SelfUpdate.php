<?php

namespace Waffle\Command\Command;

use SelfUpdate\SelfUpdateCommand;
use Waffle\Application as Waffle;
use Waffle\Helper\PharHelper;
use Waffle\Command\DiscoverableCommandInterface;

class SelfUpdate extends SelfUpdateCommand implements DiscoverableCommandInterface
{
    // We need to keep this as 'self:update' as the parent class has it
    // hardcoded.
    public const COMMAND_KEY = 'self:update';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(Waffle::NAME, Waffle::VERSION, Waffle::REPOSITORY);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();
        $this->setName(self::COMMAND_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        // This command should only work for .phar files.
        return PharHelper::isPhar();
    }
}
