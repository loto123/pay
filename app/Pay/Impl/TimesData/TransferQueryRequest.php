<?php
/**
 * 代付订单查询报文
 * Author: huangkaixuan
 * Date: 2018/3/21
 * Time: 16:00
 */

namespace App\Pay\Impl\TimesData;


class TransferQueryRequest extends Request
{
    public function __construct($reqNo, $mchid, $url)
    {
        parent::__construct('1001', '1.0', 'transfer_query_order_request', $url, $reqNo, $mchid, Message::SIGN_RSA1);
        $this->setEncryptAlgo(Request::ENCRYPT_RSA);
    }
}