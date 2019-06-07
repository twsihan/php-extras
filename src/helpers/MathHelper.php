<?php

namespace twsihan\extras\helpers;

/**
 * Class MathHelper
 *
 * @package twsihan\extras\helpers
 * @author twsihan <twsihan@gmail.com>
 */
class MathHelper
{


    /**
     * 保留小数位
     * @param $value
     * @param int $place 保留位数
     * @param bool $asRound 是否四舍五入[默认：不]
     * @return float
     */
    public static function decimalPlace($value, $place = 2, $asRound = false)
    {
        $offset = bcpow(10, $place);
        if ($asRound === true) {
            $value = round($value * $offset) / $offset;
        } else {
            $value = bcmul($value, $offset, 0) / $offset;
        }
        return $value;
    }
}
