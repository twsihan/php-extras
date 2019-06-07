<?php

namespace twsihan\extras\helpers;

/**
 * Trait FileHelperTrait
 *
 * @package twsihan\extras\helpers
 * @author twsihan <twsihan@gmail.com>
 */
trait FileHelperTrait
{


    public static function gets($filePath, $offset = 0, $mode = 'r')
    {
        $result = false;
        if (($handle = @fopen($filePath, $mode)) === $result) {
            return $result;
        }

        @fseek($handle, $offset);

        if (@feof($handle) === false) {
            $result = @fgets($handle);
        }

        fclose($handle);

        return $result;
    }

    public static function write($filePath, $string, $mode = 'w')
    {
        $result = false;

        if (($handle = @fopen($filePath, $mode)) === $result) {
            return $result;
        }

        @flock($handle, LOCK_EX);

        $result = @fwrite($handle, $string);

        @flock($handle, LOCK_UN);
        @fclose($handle);

        return $result;
    }
}
