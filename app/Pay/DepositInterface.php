<?php

namespace App\Pay;

/**
 * 支付接口
 * Author: huangkaixuan
 * Date: 2017/12/4
 * Time: 14:49
 */

interface DepositInterface
{


    /**
     * 向支付平台发出充值订单,返回支付信息给客户端
     *
     * @param $amount float
     * @param $master_container int
     * @return mixed 返回储值信息,失败返回null
     */
    public function deposit($amount, $master_container);
}