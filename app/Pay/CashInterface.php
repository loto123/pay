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
    /**
     * 根据收款人信息提现
     *
     * *@param $withdraw_id string 提现id
     * @param $amount float
     * @param $receiver_info string
     * @param $config array 接口配置
     * @return array|null 提交成功返回外部交易号和状态:['out_batch_no' => xxx(可选), 'state' => Withdraw::STATE_*, 'raw_respon'=> xxx, 'fee' => xxx(可选)]
     */
    public function withdraw($withdraw_id, $amount, $receiver_info, array $config);

    /**
     * 向支付平台发出充值订单,返回支付信息给客户端
     *
     * @param $deposit_id string 充值id
     * @param $amount float
     * @param $master_container int
     * @param $config array 接口配置
     * @return mixed 返回储值信息,失败返回null
     */
    public function deposit($deposit_id, $amount, $master_container, array $config);
}