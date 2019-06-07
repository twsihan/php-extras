<?php

namespace twsihan\extras\plugins;

/**
 * Class Rsa
 *
 * @package twsihan\extras\plugins
 * @author twsihan <twsihan@gmail.com>
 */
class Rsa
{
    /**
     * @const openssl 只能加密最大 64/128 位数据
     */
    const ENCODE_MAX_SIZE = 64;
    const DECODE_MAX_SIZE = 128;

    const BLOCK_SIZE = 8;

    const BLANK_STRING = '';

    public $publicKey;
    public $privateKey;
    public $password;


    /**
     * 初始化公私钥
     * @param null $privateKey
     * @param null $password
     * @param string $organizationName
     * @return array
     */
    public function genNew($privateKey = null, $password = null, $organizationName = '')
    {
        if ($privateKey === null && $password === null) {
            $configure = [
                'digest_alg' => 'sha1',
                'private_key_bits' => 1024,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
                'encrypt_key' => true,
            ];
            $private = openssl_pkey_new($configure);

            openssl_pkey_export($private, $privateKey, $password);
        } else {
            $private = openssl_pkey_get_private($privateKey, $password);
        }

        $publicKey = openssl_pkey_get_details($private);
        $publicKey = $publicKey['key'];

        $distinguishedName = [
            'countryName' => 'CN',
            'stateOrProvinceName' => 'shanghai',
            'localityName' => 'shanghai',
        ];

        if (!empty($organizationName)) {
            $distinguishedName['organizationName'] = $organizationName . ' Co.,Ltd.';
        }

        $expire = 3650; // 十年
        $csr = openssl_csr_new($distinguishedName, $private);
        $csrSign = openssl_csr_sign($csr, null, $private, $expire);

        openssl_csr_export($csr, $csr);
        openssl_x509_export($csrSign, $csrSign);

        return [
            'csr' => $csr,
            'csr_sign' => $csrSign,
            'public_key' => $publicKey,
            'private_key' => $privateKey,
            'password' => $password,
        ];
    }

    /**
     * 初始化加解密参数
     * @param array $params init params
     * @return void
     */
    public function initParams($params = [])
    {
        if (!empty($params) && is_array($params)) {
            $this->publicKey = isset($params['publicKey']) ? $params['publicKey'] : null;
            $this->privateKey = isset($params['privateKey']) ? $params['privateKey'] : null;
            $this->password = isset($params['password']) ? $params['password'] : null;
        }
    }

    /**
     * 公钥加密
     * @param $string
     * @return bool|string
     */
    public function pubEncode($string)
    {
        if (strlen($string) == 0) return self::BLANK_STRING;

        $array = str_split($string, self::ENCODE_MAX_SIZE);
        $buffer = self::BLANK_STRING;
        $keyid = openssl_pkey_get_public($this->publicKey);
        foreach ($array as $trunk) {
            $temp = self::BLANK_STRING;
            if (openssl_public_encrypt($trunk, $temp, $keyid, OPENSSL_PKCS1_PADDING)) {
                $buffer .= $temp;
            } else {
                return false;
            }
        }

        return base64_encode($buffer);
    }

    /**
     * 公钥解密
     * @param $strBase64
     * @return bool|string
     */
    public function pubDecode($strBase64)
    {
        if (empty($strBase64)) return self::BLANK_STRING;

        $string = base64_decode($strBase64);
        if ($string === false) {
            return false;
        }

        if (empty($string)) return self::BLANK_STRING;

        $array = str_split($string, self::DECODE_MAX_SIZE);
        $buffer = self::BLANK_STRING;
        $keyid = openssl_pkey_get_public($this->publicKey);
        foreach ($array as $trunk) {
            $temp = self::BLANK_STRING;
            if (openssl_public_decrypt($trunk, $temp, $keyid, OPENSSL_PKCS1_PADDING)) {
                $buffer .= $temp;
            } else {
                return false;
            }
        }

        return $buffer;
    }

    /**
     * 私钥加密
     * @param $string
     * @return bool|string
     */
    public function privEncode($string)
    {
        if (strlen($string) == 0) return self::BLANK_STRING;

        $array = str_split($string, self::ENCODE_MAX_SIZE);
        $buffer = self::BLANK_STRING;
        $keyid = openssl_pkey_get_private($this->privateKey, $this->password);

        foreach ($array as $trunk) {
            $temp = self::BLANK_STRING;

            if (openssl_private_encrypt($trunk, $temp, $keyid, OPENSSL_PKCS1_PADDING)) {
                $buffer .= $temp;
            } else {
                return false;
            }
        }

        return base64_encode($buffer);
    }

    /**
     * 私钥解密
     * @param $strBase64
     * @return bool|string
     */
    public function privDecode($strBase64)
    {
        if (empty($strBase64)) return self::BLANK_STRING;

        $string = base64_decode($strBase64);
        if ($string === false) {
            return false;
        }

        if (empty($string)) return self::BLANK_STRING;

        $array = str_split($string, self::DECODE_MAX_SIZE);
        $buffer = self::BLANK_STRING;
        $keyId = openssl_pkey_get_private($this->privateKey, $this->password);

        foreach ($array as $trunk) {
            $temp = self::BLANK_STRING;

            if (openssl_private_decrypt($trunk, $temp, $keyId, OPENSSL_PKCS1_PADDING)) {
                $buffer .= $temp;
            } else {
                return false;
            }
        }
        return $buffer;
    }
}
