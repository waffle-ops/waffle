<?php

namespace Waffle\Model\Config;

interface ConfigItemInterface
{
    /**
     * @var string
     *
     * Constant for denoting global scope.
     */
    public const SCOPE_GLOBAL = 'global';

    /**
     * @var string
     *
     * Constant for denoting proect scope.
     */
    public const SCOPE_PROJECT = 'project';

    /**
     * @var string
     *
     * Constant for denoting local scope.
     */
    public const SCOPE_LOCAL = 'local';

    /**
     * @var string[]
     *
     * Constant for allowed scopes.
     */
    public const ALLOWED_SCOPES = [
        self::SCOPE_GLOBAL,
        self::SCOPE_PROJECT,
        self::SCOPE_LOCAL,
    ];

    /**
     * The key of which this config item is associated with.
     *
     * @return string
     */
    public function getKey();

    /**
     * Gets tree builder definition for this config key.
     *
     * @return TreeBuilder
     */
    public function getDefinition();

    /**
     * Gets relavent scopes for this config key.
     *
     * @return string[]
     */
    public function getScopes();

    /**
     * Checks if this config key is globally scoped.
     *
     * @return bool
     */
    public function isGlobalScope();

    /**
     * Check if this config key is scoped for a project.
     *
     * @return bool
     */
    public function isProjectScope();

    /**
     * Check if this config key is locally scoped.
     *
     * @return bool
     */
    public function isLocalScope();
}
