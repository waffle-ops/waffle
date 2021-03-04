<?php

namespace Waffle\Model\Config\Loader;

use Symfony\Component\Finder\Finder;
use Waffle\Exception\Config\MissingConfigFileException;
use Waffle\Helper\WaffleHelper;
use Waffle\Model\Config\BaseConfigLoader;
use Waffle\Model\Config\ConfigTreeService;

class GlobalConfigLoader extends BaseConfigLoader
{

    /**
     * @var string
     *
     * Constant for config file name.
     */
    public const CONFIG_FILE = '.waffle';

    /**
     * @var WaffleHelper
     */
    protected $waffleHelper;

    /**
     * @var ConfigTreeService
     */
    protected $configTreeService;

    /**
     * Constructor
     *
     * @param ConfigTreeService
     * @param WaffleHelper
     */
    public function __construct(
        ConfigTreeService $configTreeService,
        WaffleHelper $waffleHelper
    ) {
        $this->configTreeService = $configTreeService;
        $this->waffleHelper = $waffleHelper;
    }

    /**
     * Gets the project config path.
     *
     * @throws MissingConfigFileException
     *
     * @return string
     */
    protected function getConfigFile()
    {
        $finder = new Finder();
        $finder->ignoreDotFiles(false);
        $finder->files();
        $finder->in([
            $this->waffleHelper->getWaffleHomeDirectory()
        ]);
        $finder->depth('== 0');
        $finder->name(self::CONFIG_FILE);

        if (!$finder->hasResults()) {
            throw new MissingConfigFileException();
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
        return $this->configTreeService->getGlobalConfigDefinition();
    }
}
