<?php

namespace App\Pay;

/**
 * RSA算法类
 * 支持编码,填充方式,签名算法
 */
class RSA
{
    private $pubKey = null;
    private $priKey = null;
    private $signAlgo;
    private $padding;
    private $encode;

    /**
     * RSA constructor.
     * @param $publicKeyFile string 公钥文件
     * @param $privateKeyFile string 私钥文件
     * @param string $encode base64,hexu 十六进制大写,hexl 十六进制小写,raw 原始二进制
     * @param int $padding 填充方式
     * $param int $sign_algo 签名算法
     */
    public function __construct($publicKeyFile, $privateKeyFile = null, $encode = 'base64', $padding = OPENSSL_PKCS1_PADDING, $sign_algo = OPENSSL_ALGO_SHA1)
    {
        $this->pubKey = openssl_get_publickey(file_get_contents($publicKeyFile));
        dump($publicKeyFile);

        if ($privateKeyFile) {
            //dump($privateKeyFile);
            $this->priKey = openssl_get_privatekey(file_get_contents($privateKeyFile));
            //dump($this->priKey);
        }
        $this->padding = $padding;
        $this->signAlgo = $sign_algo;
        $this->encode = $encode;
    }

    /**
     * 加密
     * @param $originalData
     * @return string
     */

    public function encrypt($originalData)
    {
        $crypto = '';

        foreach (str_split($originalData, 117) as $chunk) {

            openssl_public_encrypt($chunk, $encryptData, $this->pubKey, $this->padding);

            $crypto .= $encryptData;
        }
        return $this->encode($crypto);
    }

    /**
     * 编码数据
     * @param $data
     */
    private function encode($data)
    {
        switch ($this->encode) {
            case 'base64':
                return base64_encode($data);
            case 'hexu':
                return strtoupper(bin2hex($data));
            case 'hexl':
                return bin2hex($data);
            case 'raw':
                return $data;
            default:
                return false;
        }
    }

    /**
     * 私钥解密
     * @param $encryptData
     */
    public function decrypt($encryptData)
    {
        $crypto = $this->decode($encryptData);

        foreach (str_split($crypto, 128) as $chunk) {

            openssl_private_decrypt($chunk, $decryptData, $this->priKey, $this->padding);

            $crypto .= $decryptData;
        }

        return $crypto;
    }

    /**
     * 解码数据
     * @param $data
     */
    private function decode($data)
    {
        switch ($this->encode) {
            case 'base64':
                $data = base64_decode($data);
                break;
            case 'hexu':
            case 'hexl':
                $data = hex2bin($data);
        }
        return $data;

    }

    /**
     * 生成签名
     *
     * @param string
     * @return string 签名值
     */
    public function sign($data)
    {
        $ret = false;
        //dump($this->priKey);
        if (openssl_sign($data, $ret, $this->priKey, $this->signAlgo)) {
            $ret = $this->encode($ret);
        }
        return $ret;
    }

    /**
     * 验证签名
     *
     * @param string
     * @param string
     * @param string
     * @return bool
     */
    public function verify($data, $sign)
    {
        $ret = false;
        $sign = $this->decode($sign);
        if ($sign !== false) {
            dump($this->pubKey);
            switch (openssl_verify($data, $sign, $this->pubKey, $this->signAlgo)) {
                case 1:
                    $ret = true;
                    break;
                case 0:
                case -1:
                default:
                    $ret = false;
            }
        }
        return $ret;
    }
}
    
