<?php

namespace Waffle\Model\Config\Loader;

use Symfony\Component\Finder\Finder;
use Waffle\Exception\Config\AmbiguousConfigException;
use Waffle\Exception\Config\MissingConfigFileException;
use Waffle\Model\Config\BaseConfigLoader;
use Waffle\Model\Config\ConfigTreeService;

class LocalConfigLoader extends BaseConfigLoader
{
    /**
     * @var string
     *
     * Constant for config file name.
     */
    public const CONFIG_FILE = '.waffle.local.yml';

    /**
     * @var ConfigTreeService
     */
    protected $configTreeService;

    /**
     * Constructor
     *
     * @param ConfigTreeService
     */
    public function __construct(ConfigTreeService $configTreeService)
    {
        $this->configTreeService = $configTreeService;
    }

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

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        return $this->configTreeService->getLocalConfigDefinition();
    }
}
