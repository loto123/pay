<?php
/**
 * 安卓APP包装H5支付
 * Author: huangkaixuan
 * Date: 2018/2/1
 * Time: 14:46
 */

namespace App\Pay\Impl\Heepay;


use App\Pay\Model\DepositMethod;

class H5AndroidWrapper extends WechatH5
{
    protected $wrapper = DepositMethod::OS_ANDROID;
}