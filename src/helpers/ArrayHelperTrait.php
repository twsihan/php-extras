<?php

namespace twsihan\extras\helpers;

/**
 * Trait ArrayHelperTrait
 *
 * @package twsihan\extras\helpers
 * @author twsihan <twsihan@gmail.com>
 */
trait ArrayHelperTrait
{


    public static function toString($array)
    {
        $str = '';
        if (!empty($array)) {
            foreach ($array as $value) {
                $str .= is_array($value) ? implode('', $value) : $value;
            }
        }
        return $str;
    }
}
