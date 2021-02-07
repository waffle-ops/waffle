<?php

namespace Waffle\Helper;

use Symfony\Component\Yaml\Yaml;

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
    private function ensureDirectory($path)
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

    /**
     * Gets the cache directory location.
     *
     * @return string
     */
    private function getCacheDirectory()
    {
        $cache = $this->getWaffleHomeDirectory() . '/cache';
        $this->ensureDirectory($cache);
        return $cache;
    }

    /**
     * Gets the cache file for the provided key.
     *
     * @param string $key
     *   The name of the desired cache file.
     */
    private function getCacheFile(string $key)
    {
        return sprintf(
            '%s/%s',
            $this->getCacheDirectory(),
            $key
        );
    }

    /**
     * Gets fata from cache directory.
     *
     * @param string $key
     *   Name of the cache bin that is to be loaded.
     *
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     *
     * @return array
     */
    public function getCacheData(string $key)
    {
        $cache = $this->getCacheFile($key);

        if (!file_exists($cache)) {
            // Throw exception?
            return [];
        }

        $data = Yaml::parseFile($cache);

        return $data;
    }

    /**
     * Writes data to cache directory.
     *
     * @param string $key
     *   Name of cache bin where $value will be stored.
     * @param array $value
     *   Array of data that is stored as a Yaml file for future use.
     */
    public function setCacheData(string $key, array $value)
    {
        $cache = $this->getCacheFile($key);
        $yaml = Yaml::dump($value);
        file_put_contents($cache, $yaml);
    }
}
