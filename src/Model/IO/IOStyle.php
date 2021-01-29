<?php

namespace Waffle\Model\IO;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Process\Process;
use Waffle\Model\Output\Runner;

class IOStyle extends SymfonyStyle implements StyleInterface
{
    // See https://github.com/symfony/console/blob/5.x/Style/SymfonyStyle.php.
    // @todo: Make some pretty output options: https://symfony.com/doc/current/console/coloring.html

    /**
     * {@inheritdoc}
     */
    public function note($message)
    {
        $this->block($message, null, 'fg=yellow', ' ! ');
    }
}
