<?php
/**
 * 支付请求报文
 * Author: huangkaixuan
 * Date: 2018/3/9
 * Time: 17:02
 */

namespace App\Pay\Impl\TimesData;


class PayRequest extends Request
{
    public function __construct($reqType, $reqNo, $mechId, $channel, $url = 'https://bp.timesdata.net/payserver/x2xpay/doRequest.action')
    {
        parent::__construct('DEFAULT', '1.0', $reqType, $url, $reqNo);
        $this->appendHead('mchid', $mechId);
        $this->appendHead('channel', $channel);
    }

    /**
     * 设置异步通知地址
     * @param $url
     */
    public function setNotifyUrl($url)
    {
        $this->appendHead('backURL', $url);
    }
}