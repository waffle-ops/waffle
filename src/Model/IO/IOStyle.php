<?php

namespace Waffle\Model\IO;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IOStyle extends SymfonyStyle implements StyleInterface
{
    // See https://github.com/symfony/console/blob/5.x/Style/SymfonyStyle.php.
    // @todo: Make some pretty output options: https://symfony.com/doc/current/console/coloring.html

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Constructor
     */
    public function __construct()
    {
        $input = new ArgvInput();
        $this->output = new ConsoleOutput();

        // TODO -- We can update in the future to use hex colors once we upgrade
        // to Symfony 5.2.
        $formatters = [
            // Below is a list of custom output formatters used to display
            // text in a consistent way. This list will grow over time as the
            // boundaries of the type of output we will provide are more
            // defined.

            // These are used for displaying simple status messages.
            'status_success' => new OutputFormatterStyle('green', 'default', ['bold']),
            'status_warning' => new OutputFormatterStyle('yellow', 'default', ['bold']),
            'status_error' => new OutputFormatterStyle('red', 'default', ['bold']),
        ];

        foreach ($formatters as $formatKey => $formatStyle) {
            $this->output->getFormatter()->setStyle($formatKey, $formatStyle);
        }

        parent::__construct($input, $this->output);
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

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
    public function styleText($text, $style)
    {
        if ($style === 'none') {
            return $text;
        }

        return sprintf('<%s>%s</%s>', $style, $text, $style);
    }

    /**
     * {@inheritdoc}
     */
    public function text($message)
    {
        // Override of the default Symfony Style behavior to remove the leading
        // space.

        $messages = \is_array($message) ? array_values($message) : [$message];
        foreach ($messages as $message) {
            $this->writeln(sprintf('%s', $message));
        }
    }

    /**
     * Method to print simple status messages to the console.
     *
     * Examples:
     *  [OK] - Task completed successfully.
     *  [WARNING] - Recipe exited with a non-zero status code.
     *  [ERROR] - Drush is not installed.
     *
     * @param string $style
     *   The output formatter style to be used to display the message.
     * @param string $prefix
     *   The prefix of the message (ie, OK, WARN, etc...).
     * @param string $message
     *   The message to be displayed.
     */
    public function status($style, $prefix, $message)
    {
        $this->writeln(sprintf(
            '[%s] - %s',
            $this->styleText($prefix, $style),
            $this->styleText($message, $style),
        ));
    }

    /**
     * Helper method to display simple success status messages to the console.
     *
     * @param string $message
     *   The message to be displayed.
     * @param string $prefix
     *   The prefix of the message (ie, OK, WARN, etc...).
     */
    public function statusSuccess($message, $prefix = 'OK')
    {
        $this->status('status_success', $prefix, $message);
    }

    /**
     * Helper method to display simple warning status messages to the console.
     *
     * Not intended to display 'major' warnings such as code deprecations.
     * Those types of situations would fare better with the warning() method,
     * which is more prominently displayed.
     *
     * @param string $message
     *   The message to be displayed.
     * @param string $prefix
     *   The prefix of the message (ie, OK, WARN, etc...).
     */
    public function statusWarning($message, $prefix = 'WARNING')
    {
        $this->status('status_warning', $prefix, $message);
    }

    /**
     * Helper method to display simple error status messages to the console.
     *
     * Not intended for actual system failures. This is intended for reporting
     * small failures such as a failed audit check, or a task exiting with a
     * non-zero exit code.
     *
     * @param string $message
     *   The message to be displayed.
     * @param string $prefix
     *   The prefix of the message (ie, OK, WARN, etc...).
     */
    public function statusError($message, $prefix = 'ERROR')
    {
        $this->status('status_error', $prefix, $message);
    }
}
