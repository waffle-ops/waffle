<?php

namespace Waffle\Model\Config;

use Symfony\Component\Finder\Finder;
use Waffle\Exception\Config\MissingConfigFileException;
use Waffle\Helper\WaffleHelper;

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
     * Constructor
     *
     * @param WaffleHelper
     */
    public function __construct(WaffleHelper $waffleHelper)
    {
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
}
