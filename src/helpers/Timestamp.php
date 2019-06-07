<?php

namespace twsihan\extras\helpers;

/**
 * Class Timestamp
 *
 * @package twsihan\extras\helpers
 * @author twsihan <twsihan@gmail.com>
 */
class Timestamp
{


    public static function datetime($datetime, $now = true)
    {
        return ($datetime && $datetime != '0000-00-00 00:00:00' && $datetime != '0000-00-00') ? strtotime($datetime) : ($now === true ? time() : $now);
    }

    public static function date($date, $now = true)
    {
        return ($date && $date != '0000-00-00') ? strtotime($date) : ($now === true ? time() : $now);
    }
}
