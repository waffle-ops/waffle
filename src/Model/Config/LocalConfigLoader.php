<?php

namespace Waffle\Model\Config;

use Symfony\Component\Finder\Finder;
use Waffle\Exception\Config\AmbiguousConfigException;
use Waffle\Exception\Config\MissingConfigFileException;

class LocalConfigLoader extends BaseConfigLoader
{
    /**
     * @var string
     *
     * Constant for config file name.
     */
    public const CONFIG_FILE = '.waffle.local.yml';

    /**
     * Gets the project config path.
     *
     * @throws MissingConfigFileException
     * @throws AmbiguousConfigException
     *
     * @return string
     */
    protected function getConfigFile()
    {
        $finder = new Finder();
        $finder->ignoreDotFiles(false);
        $finder->files();
        $finder->in([
            getcwd(),
            dirname(getcwd() . '..'),
        ]);
        $finder->depth('== 0');
        $finder->name(self::CONFIG_FILE);

        if (!$finder->hasResults()) {
            throw new MissingConfigFileException();
        }

        // We should never have more than one local config.
        if ($finder->count() > 1) {
            throw new AmbiguousConfigException();
        }

        $iterator = $finder->getIterator();
        $iterator->rewind();
        $file = $iterator->current();

        return $file->getRealPath();
    }
}
