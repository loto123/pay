<?php
/**
 * 支付平台接口
 * Author: huangkaixuan
 * Date: 2017/12/4
 * Time: 16:14
 */

namespace App\Pay;


use App\Pay\Model\Deposit;
use App\Pay\Model\Withdraw;

interface PlatformInterface
{
    /**
     * 接收支付平台的通知并解析结果
     *
     * @param $request
     * @config 接口配置
     * @return Deposit,不合法或交易不存在返回null
     */
    public function acceptDepositNotify(array $config);

    /**
     * 接收提现通知
     * @param array $config
     * @return Withdraw,不合法或交易不存在返回null
     */
    public function acceptWithdrawNotify(array $config);
}