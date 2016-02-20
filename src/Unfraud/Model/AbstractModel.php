<?php

namespace Unfraud\Model;

/**
 * Class AbstractModel
 * @package Unfraud\Unfraud\Model
 * @internal
 */
abstract class AbstractModel
{

    /**
     * @param array $response The array corresponding to the object in the
     * minFraud Insights response.
     * @param array $locales List of locale codes to use in name property from
     * most preferred to least preferred.
     */
    public function __construct($response)
    {
    }

    /**
     * Convenience method to safely get value from array that might be null
     * @param $var
     * @param mixed $default
     * @return mixed
     * @internal
     */
    protected function safeArrayLookup(&$var, $default = null)
    {
        return isset($var) ? $var : $default;
    }

    /**
     * @internal
     * @param $attr The attribute to get
     * @return The value for the attribute
     */
    public function __get($attr)
    {
        if ($attr != "instance" && property_exists($this, $attr)) {
            return $this->$attr;
        }

        throw new \RuntimeException("Unknown attribute: $attr");
    }
}
