<?php
/**
 * 汇付宝微信h5支付
 * Author: huangkaixuan
 * Date: 2017/12/4
 * Time: 15:32
 */

namespace App\Pay\Impl\Heepay;

use App\Pay\CashInterface;

class WechatH5 implements CashInterface
{

    public function deposit($deposit_id, $amount, $master_container, array $config, $notify_url)
    {
        // TODO: Implement deposit() method.
    }

    public function withdraw($withdraw_id, $amount, $receiver_info, array $config, $notify_url)
    {
        // TODO: Implement withdraw() method.
    }
}