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

    /**
     * Helper method to highlight text.
     *
     * @param $text
     *   The string that contains text that should be highlighted as %s. Text
     *   will be green in color.
     * @param $replacements
     *   The replacement text to be highlighted. Text will be yellow in color.
     */
    public function highlightText($text, $replacements)
    {
        $message = '<info>' . $text . '</info>';
        $tokens = [];

        foreach ($replacements as $replacement) {
            $tokens[] = '<comment>' . $replacement . '</comment>';
        }

        $this->writeln(vsprintf($message, $tokens));
    }
}
