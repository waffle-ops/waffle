<?php

namespace Waffle\Model\IO;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Waffle\Model\IO\IOStyle;

class IO
{

    /**
     * @var IO
     *
     * $instance The IO instance.
     */
    private static $instance = null;

    /**
     * @var ArgvInput
     *
     * $input Application input object.
     */
    private $input;

    /**
     * @var ConsoleOutput
     *
     * $output Console output object.
     */
    private $output;

    /**
     * @var SymfonyStyle
     *
     * $io Helper object for easier IO operations.
     */
    private $io;

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->input = new ArgvInput();
        $this->output = new ConsoleOutput();
        $this->io = new IoStyle($this->input, $this->output);
    }

    /**
     * Gets the IO singleton.
     *
     * @return IO
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new IO();
        }

        return self::$instance;
    }

    /**
     * Gets the input.
     *
     * @return ArgvInput
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Gets the output.
     *
     * @return ConsoleOutput
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Gets the io helper.
     *
     * @return IOStyle
     */
    public function getIO()
    {
        return $this->io;
    }
}
