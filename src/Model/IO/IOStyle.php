<?php

namespace Waffle\Model\IO;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Process\Process;
use Waffle\Model\Output\Runner;

class IOStyle extends SymfonyStyle implements StyleInterface
{
    // See https://github.com/symfony/console/blob/5.x/Style/SymfonyStyle.php.
}
