<?php

namespace Waffle\Exception\Config;

use Exception;

class MissingConfigFileException extends Exception
{

    private const MESSAGE = 'Error: Unable to load .waffle.yml file.';

    public function __construct()
    {
        parent::__construct(self::MESSAGE);
    }
}
