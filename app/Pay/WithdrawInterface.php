<?php

namespace App\Pay;

use App\Pay\Model\Withdraw;
use App\Pay\Model\WithdrawResult;

/**
 * 提现接口
 * Author: huangkaixuan
 * Date: 2017/12/16
 * Time: 12:00
 */
interface WithdrawInterface
{
    const TRANSACTION_NAME = '电子宠物出售';
    /**
     * 根据收款人信息提现
     *
     * *@param $withdraw_id string 提现id
     * @param $amount float
     * @param $receiver_info array 收款人信息
     * @param $config array 接口配置
     * @param $notify_url string 通知地址
     * @return WithdrawResult
     */
    public function withdraw($withdraw_id, $amount, array $receiver_info, array $config, $notify_url);

    /**
     * 收款信息说明
     * @return array field=>description
     */
    public function receiverInfoDescription();

    /**
     * 接收提现通知
     * @param array $config
     * @return Withdraw,不合法或交易不存在返回null
     */
    public function acceptNotify(array $config);

    /**
     * 混淆提现id给外部接口
     * @return string
     */
    public function mixUpWithdrawId($withdrawId);
}
