<?php

namespace Waffle\Model\IO;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Style\StyleInterface;

class IOStyle extends SymfonyStyle implements StyleInterface
{
    // Intentionally blank for now. We can use this class to add any new helper
    // methods we want other than what is provided by SymfonyStyle. Further, we
    // can also override SymfonyStyle. Ideally, all IO wlil be routed through
    // this class so that we can have consistent output styles.

    // See https://github.com/symfony/console/blob/5.x/Style/SymfonyStyle.php.
}
