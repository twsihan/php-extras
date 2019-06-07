<?php

namespace twsihan\extras\plugins;

/**
 * Class AuthCode
 *
 * @package twsihan\extras\plugins
 * @author twsihan <twsihan@gmail.com>
 */
class AuthCode
{
    // 密匙
    private $_key;
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
    // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
    // 取值越大，密文变动规律越大，密文变化 = 16 的 $cKeyLength 次方
    // 当此值为 0 时，则不产生随机密钥
    private $_cKeyLength = 4;
    private $_randomKey = '';


    /**
     * AuthCode constructor.
     * @param string $key 加解密 Key
     * @param bool $confusion 密文是否变更，默认变更
     */
    public function __construct($key = '', $confusion = true)
    {
        $this->_key = md5($key);
        if ($confusion === false) {
            $this->_cKeyLength = 0;
        } else if ($confusion === true) {
            $this->setRandomKey(microtime());
        } else {
            $this->setRandomKey($confusion);
        }
    }

    public function getCKeyLength()
    {
        return $this->_cKeyLength;
    }

    public function setCKeyLength($length)
    {
        $this->_cKeyLength = $length;

        return $this;
    }

    public function getRandomKey()
    {
        return $this->_randomKey;
    }

    public function setRandomKey($randomKey)
    {
        $this->_randomKey = $randomKey;

        return $this;
    }

    /**
     * 加密
     * @param $string
     * @param int $expiry
     * @return string
     */
    public function encode($string, $expiry = 0)
    {
        // 密匙a会参与加解密
        $keyA = md5(substr($this->_key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyB = md5(substr($this->_key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyC = $this->_cKeyLength ? substr(md5($this->_randomKey), -$this->_cKeyLength) : '';
        // 参与运算的密匙
        $cryptKey = $keyA . md5($keyA . $keyC);
        $key_length = strlen($cryptKey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $string = sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyB), 0, 16) . $string;
        $stringLength = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndKey = array();
        // 产生密匙簿
        for ($i = 0; $i <= 255; $i++) {
            $rndKey[$i] = ord($cryptKey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上并不会增加密文的强度
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndKey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for ($a = $j = $i = 0; $i < $stringLength; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
        return $keyC . str_replace('=', '', base64_encode($result));
    }

    /**
     * 解密
     * @param $string
     * @return string
     */
    public function decode($string)
    {
        // 密匙a会参与加解密
        $keyA = md5(substr($this->_key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyB = md5(substr($this->_key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyC = $this->_cKeyLength ? substr($string, 0, $this->_cKeyLength) : '';
        // 参与运算的密匙
        $cryptKey = $keyA . md5($keyA . $keyC);
        $keyLength = strlen($cryptKey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $string = base64_decode(substr($string, $this->_cKeyLength));
        $stringLength = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndKey = array();
        // 产生密匙簿
        for ($i = 0; $i <= 255; $i++) {
            $rndKey[$i] = ord($cryptKey[$i % $keyLength]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上并不会增加密文的强度
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndKey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for ($a = $j = $i = 0; $i < $stringLength; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        // 验证数据有效性，请看未加密明文的格式
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyB), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    }
}
