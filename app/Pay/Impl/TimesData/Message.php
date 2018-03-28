<?php

namespace App\Pay\Impl\TimesData;

use App\Pay\RSA;

/**
 * 报文结构
 * Author: huangkaixuan
 * Date: 2018/3/8
 * Time: 17:33
 */
abstract class Message
{
    const SIGN_RSA1 = 1;
    const SIGN_MD5 = 2;
    /**
     * 签名方式
     */
    protected $signType = self::SIGN_RSA1;
    /**
     * 报文头部
     * @var array
     */
    protected $headFields = [];
    protected $dataFields = [];
    /**
     * RSA加密实例
     * @var RSA
     */
    protected $RSAInstance;
    private $MD5Key;

    /**
     * 设置MD5 Key
     */
    public final function setMD5Key($key)
    {
        $this->MD5Key = $key;
    }

    /**
     * 设置RSA实例
     * @param RSA $instance
     */
    public function setRSAInstance(RSA $instance)
    {
        $this->RSAInstance = $instance;
    }

    /**
     * 签名
     * @return string
     * @throws \Exception
     */
    protected function sign()
    {
        $signature = '';
        $allFields = array_merge($this->headFields, $this->dataFields);
        ksort($allFields);
        $data = implode($allFields);
        switch ($this->signType) {
            case self::SIGN_RSA1:
                $signature = $this->RSAInstance->sign($data);
                break;
            case self::SIGN_MD5:
                if (!$this->MD5Key) {
                    throw new \Exception('MD5签名key为空');
                }
                $data .= $this->MD5Key;
                $signature = md5($data);
                break;
        }
        return $signature;
    }
}