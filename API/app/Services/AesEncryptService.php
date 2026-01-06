<?php
/**
 * 加密加密
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Services;

class AesEncryptService
{
    private $key = '';
    private $iv = '';
    private $cipher = 'AES-128-CBC';

    public function __construct($key, $iv)
    {
        $this->key = $key;
        $this->iv = $iv;
    }

    //加密
    public function encrypt($data)
    {
        return openssl_encrypt($data, $this->cipher, $this->key, OPENSSL_RAW_DATA, $this->iv);
    }

    //解密
    public function decrypt($data)
    {
        return openssl_decrypt($data, $this->cipher, $this->key, OPENSSL_RAW_DATA, $this->iv);
    }
}
