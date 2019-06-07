<?php

namespace twsihan\extras\helpers;

/**
 * Trait StringHelperTrait
 *
 * @package twsihan\extras\helpers
 * @author twsihan <twsihan@gmail.com>
 */
trait StringHelperTrait
{


    /**
     * uuid
     * @param bool $trim
     * @param bool $hyphen
     * @return string
     */
    public static function uuid($trim = true, $hyphen = false)
    {
        mt_srand((double)microtime() * 10000); // optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = $hyphen === true ? chr(45) : ''; // "-"
        $uuid = chr(123) // "{"
            . substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12)
            . chr(125); // "}"

        return $trim === true ? trim($uuid, '{}') : $uuid;
    }

    public static function upperWords($string, $and = '_')
    {
        // 通过指定字符串拆分为数组
        $value = explode($and, $string);
        if ($value) {
            // 首字母大写，然后拼接
            $strReturn = '';
            foreach ($value as $val) {
                $strReturn .= ucfirst($val);
            }
        } else {
            $strReturn = ucfirst($string);
        }
        return $strReturn;
    }

    /**
     * 字符串脱敏
     * @param $string
     * @param $replace
     * @param $start
     * @param $length
     * @return mixed
     */
    public static function desensitization($string, $replace, $start, $length)
    {
        $padLength = strlen(substr($string, $start, $length));
        $padString = str_pad($replace, $padLength, $replace);

        return substr_replace($string, $padString, $start, $length);
    }

    public static function subBirthdayIdCard($idCard)
    {
        return $idCard ? (strlen($idCard) == 15 ? '19' . substr($idCard, 6, 6) : substr($idCard, 6, 8)) : '';
    }

    public static function subSexWithIdCard($idCard)
    {
        return $idCard ? ((strlen($idCard) == 15 ? substr($idCard, -1, 1) : substr($idCard, -2, 1)) % 2 ? 1 : 2) : 0;
    }

    public static function subAgeWithIdCard($idCard)
    {
        $age = 0;
        if ($idCard) {
            $birthday = static::subBirthdayIdCard($idCard);

            $today = strtotime('today');
            $diff = floor((strtotime('today') - strtotime($birthday)) / 86400 / 365); // 得到两个日期相差的大体年数

            // strtotime 加上这个年数后得到那日的时间戳后与今日的时间戳相比
            $age = strtotime($birthday . ' +' . $diff . 'years') > $today ? ($diff + 1) : $diff;
        }
        return $age;
    }
}
