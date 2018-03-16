<?php

namespace App\Pay\Impl\TimesData;

/**
 * 响应报文结构
 * Author: huangkaixuan
 * Date: 2018/3/8
 * Time: 17:49
 */
class Response extends Message
{
    public function __construct($code, $msg, $reqNo, $respNo)
    {
        $this->headFields = [
            'respCd' => $code,
            'respMsg' => $msg,
            'reqNo' => $reqNo,
            'respNo' => $respNo
        ];
    }

    /**
     * 是否成功
     * @return bool
     */
    public final function isOk()
    {
        return $this->headFields['respCd'] === '0000';
    }

    /**
     * 取得报文体属性
     * @param $name
     * @return mixed
     */
    public final function __get($name)
    {
        return $this->dataFields[$name];
    }

    /**
     * 获取响应码
     * @return mixed
     */
    public final function getCode()
    {
        return $this->headFields['respCd'];
    }

    /**
     * 获取消息
     * @return mixed
     */
    public final function getMessage()
    {
        return $this->headFields['respMsg'];
    }

    /**
     * 获取请求流水
     * @return mixed
     */
    public final function getReqNo()
    {
        return $this->headFields['reqNo'];
    }

    /**
     * 获取响应流水
     * @return mixed
     */
    public final function getResponseNo()
    {
        return $this->headFields['respNo'];
    }
}