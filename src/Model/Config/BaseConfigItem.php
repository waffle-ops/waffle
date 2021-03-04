<?php

namespace Waffle\Model\Config;

abstract class BaseConfigItem implements ConfigItemInterface
{

    /**
     * @var string
     *
     * The config key for this config item.
     */
    protected $key;

    /**
     * @var string[]
     *
     * Scopes that this config key applies to.
     */
    protected $scopes;

    /**
     * Constructor
     *
     * @param string $key
     *   The key in the config file this item is associated with.
     * @param string[] $scopes
     *   An array of avaliable scopes for this config key.
     */
    public function __construct($key, $scopes)
    {
        $this->setKey($key);
        $this->validateScopes($scopes);
        $this->setScopes($scopes);
    }

    /**
     * Validates the scopes to ensure they are expected values.
     *
     * @param string[] $scopes
     *   The list of scopes to validate.
     */
    private function validateScopes($scopes)
    {
        if (empty($scopes)) {
            throw new \Exception(sprintf(
                'No scopes provided \'%s\'. Valid scopes must be provided.',
                $this->getKey()
            ));
        }

        $invalid = [];

        foreach ($scopes as $scope) {
            if (!in_array($scope, ConfigItemInterface::ALLOWED_SCOPES)) {
                $invalid[] = $scope;
            }
        }

        if (!empty($invalid)) {
            throw new \Exception(sprintf(
                'Invalid scope for config key \'%s\'. Invalid scopes: [%s]',
                $this->getKey(),
                implode(',', $invalid)
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Sets the key this config item is associated with.
     *
     * @param string $key
     *   The key this config item is associated with.
     */
    public function setKey($key)
    {
        return $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Sets the scopes for this config item.
     *
     * @param string[] $scopes
     *   The scopes of which thie config key is avaliable.
     */
    public function setScopes($scopes)
    {
        return $this->scopes = $scopes;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getDefinition();

    /**
     * {@inheritdoc}
     */
    public function isGlobalScope()
    {
        return in_array(
            ConfigItemInterface::SCOPE_GLOBAL,
            $this->getScopes()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isProjectScope()
    {
        return in_array(
            ConfigItemInterface::SCOPE_PROJECT,
            $this->getScopes()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isLocalScope()
    {
        return in_array(
            ConfigItemInterface::SCOPE_LOCAL,
            $this->getScopes()
        );
    }
}
