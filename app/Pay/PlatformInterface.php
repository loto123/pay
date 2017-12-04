<?php
/**
 * 支付平台接口
 * Author: huangkaixuan
 * Date: 2017/12/4
 * Time: 16:14
 */

namespace App\Pay;

interface PlatformInterface
{
    /**
     * 根据收款人信息提现
     *
     * @param $amount float
     * @param $receiver_info string
     * @return mixed 提交成功返回外部交易号和状态
     */
    public function withdraw($amount, $receiver_info);


    /**
     * 接收支付平台的通知并解析结果
     *
     * @return mixed Withdraw/Deposit
     */
    public function acceptNotify();


    /**
     * 搜集接口配置
     * @param array $args
     * @return void
     */
    public function gatherConfigure(array $args);
}