<?php
/**
 * 汇付宝微信h5支付
 * Author: huangkaixuan
 * Date: 2017/12/4
 * Time: 15:32
 */

namespace App\Pay\Impl\Heepay;

use App\Pay\DepositInterface;
use App\Pay\Model\PayMethod;

class WechatH5 extends PayMethod implements DepositInterface
{

    public function deposit($amount, $master_container)
    {
        // TODO: Implement deposit() method.
    }
}