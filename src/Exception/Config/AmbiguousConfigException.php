<?php

namespace Waffle\Exception\Config;

use Exception;

class AmbiguousConfigException extends Exception
{

    private const MESSAGE = <<<MSG
        Error: Multiple .waffle.yml files have been detected.
        The .waffle.yml file should be located in the docroot of the project or in repository root of the project.
    MSG;

    public function __construct()
    {
        parent::__construct(self::MESSAGE);
    }
}
