<?php

namespace Waffle\Helper;

class WaffleHelper
{

    /**
     * Gets the path for the Waffle home directory.
     *
     * @return string
     */
    public function getWaffleHomeDirectory()
    {
        // Enviornment variable that allows users to choose where Waffle stores
        // things.
        $waffle_home = getenv('WAFFLE_HOME'); // TODO Document this.

        if (empty($waffle_home)) {
            $home = rtrim(getenv('HOME'), '/');
            $waffle_home = $home . '/.waffle';
        }

        $waffle_home = rtrim($waffle_home, '/');

        // It's greedy, but if we need to know where the homedirectory is,
        // chances are we need to read or write something there.
        $this->ensureDirectory($waffle_home);

        return $waffle_home;
    }

    /**
     * Creates directory if it does not exist.
     *
     * @return void
     *
     * @throws Exception
     */
    private static function ensureDirectory($path)
    {
        if (is_dir($path)) {
            return;
        }

        if (mkdir($path, 0777, true)) {
            return;
        }

        // TODO - Throw a custom exception.
        throw new \Exception('Unable to create directory %s', $path);
    }
}
