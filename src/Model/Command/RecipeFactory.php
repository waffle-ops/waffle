<?php

namespace Waffle\Model\Command;

use Waffle\Command\Recipe;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

class RecipeFactory
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var IOStyle
     */
    private $io;

    /**
     * Constructor
     *
     * @param Context $context
     * @param IOStyle $io
     *
     */
    public function __construct(
        Context $context,
        IOStyle $io
    ) {
        $this->context = $context;
        $this->io = $io;
    }

    /**
     * Creates a new Recipe.
     *
     * @param string $recipeKey
     *   The recipe key.
     *
     * @return Recipe
     */
    public function create(string $recipeKey)
    {
        return new Recipe(
            $this->context,
            $this->io,
            $recipeKey
        );
    }
}
