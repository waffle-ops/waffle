<?php

namespace Waffle\Traits;

use Waffle\Model\Config\Item\Cms;

trait DrupalContextTrait
{
    /**
     * isDrupal
     *
     * Checks to see if the current context is Drupal based.
     *
     * @return boolean
     *   Returns true if current cms is Drupal, false otherwise.
     */
    private function isDrupal()
    {
        $cms = $this->context->getCms();

        $drupal = [
            Cms::OPTION_DRUPAL_7,
            Cms::OPTION_DRUPAL_8,
        ];

        return in_array($cms, $drupal);
    }

    /**
     * isDrupal7
     *
     * Checks to see if the current context is Drupal 7 based.
     *
     * @return boolean
     *   Returns true if current cms is Drupal 7, false otherwise.
     */
    private function isDrupal7()
    {
        $cms = $this->context->getCms();
        return $cms === Cms::OPTION_DRUPAL_7;
    }

    /**
     * isDrupal8
     *
     * Checks to see if the current context is Drupal 8 based.
     *
     * @return boolean
     *   Returns true if current cms is Drupal 8, false otherwise.
     */
    private function isDrupal8()
    {
        $cms = $this->context->getCms();
        return $cms === Cms::OPTION_DRUPAL_8;
    }
}
