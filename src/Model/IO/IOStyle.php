<?php

namespace Waffle\Model\IO;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
     * @param string $text
     *   The string that contains text that should be highlighted as %s. Text
     *   will be green in color.
     * @param string[] $replacements
     *   The replacement text to be highlighted. Text will be yellow in color.
     * @param string $baseStyle
     *   The base style for the text.
     * @param string $highlightStyle
     *   The style for the highlighted portion of text.
     */
    public function highlightText($text, $replacements, $baseStyle = 'info', $highlightStyle = 'comment')
    {
        $message = $this->styleText($text, $baseStyle);
        $tokens = [];

        foreach ($replacements as $replacement) {
            $tokens[] = $this->styleText($replacement, $highlightStyle);
        }

        $this->writeln(vsprintf($message, $tokens));
    }

    /**
     * Helper method to highlight text.
     *
     * @param string $text
     *   The string that contains text that should be highlighted as %s. Text
     *   will be green in color.
     * @param string[] $replacements
     *   The replacement text to be highlighted. Text will be yellow in color.
     * @param string $baseStyle
     *   The base style for the text.
     * @param string $highlightStyle
     *   The style for the highlighted portion of text.
     */
    public function styledText($text, $style = 'info')
    {
        $message = $this->styleText($text, $style);
        $this->writeln($message);
    }

    /**
     * {@inheritdoc}
     */
    public function table($headers, $rows, $style = null)
    {
        $table = new Table($this);
        $table->setHeaders($headers);
        $table->setRows($rows);

        if (!empty($style)) {
            if ($style === 'borderless') {
                // The default borderless style still has borders for table
                // seperators.
                $style = new TableStyle();
                $style->setHorizontalBorderChars(' ');
                $style->setVerticalBorderChars('');
                $style->setDefaultCrossingChar('');
            }

            $table->setStyle($style);
        }

        $table->render();
    }

    /**
     * styleText
     *
     * Helper method for formatting text.
     *
     * @param string $text
     *   The text to format.
     * @param string $style
     *   The style of the text (ie, info, comment, etc.)
     */
    private function styleText($text, $style)
    {
        if ($style === 'none') {
            return $text;
        }

        return sprintf('<%s>%s</%s>', $style, $text, $style);
    }
}
