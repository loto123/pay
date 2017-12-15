<?php

namespace App\Pay;

/**
 * 支付接口
 * Author: huangkaixuan
 * Date: 2017/12/4
 * Time: 14:49
 */

interface CashInterface
{
    const GOOD_NAME = '余额充值';
    /**
     * 根据收款人信息提现
     *
     * *@param $withdraw_id string 提现id
     * @param $amount float
     * @param $receiver_info array 收款人信息
     * @param $config array 接口配置
     * @param $notify_url string 通知地址
     * @return array|null 提交成功返回外部交易号和状态:['out_batch_no' => xxx(可选), 'state' => Withdraw::STATE_*, 'raw_response'=> xxx, 'fee' => xxx(可选)]
     */
    public function withdraw($withdraw_id, $amount, array $receiver_info, array $config, $notify_url);

    /**
     * 收款信息说明
     * @return array field=>description
     */
    public function receiverInfoDescription();
    /**
     * 向支付平台发出充值订单,返回支付信息给客户端
     *
     * @param $deposit_id string 充值id
     * @param $amount float
     * @param $master_container int
     * @param $config array 接口配置
     * @param $notify_url string 通知地址
     * @return mixed 返回储值信息,失败返回null
     */
    public function deposit($deposit_id, $amount, $master_container, array $config, $notify_url, $return_url);


    /**
     * 解析同步跳转的充值信息,展示用,无需验证
     * @return array ['out_batch_no' => xxx(可选), 'state' => Deposit::STATE_*, 'amount' => 充值金额]
     */
    public function displayReturn();
}