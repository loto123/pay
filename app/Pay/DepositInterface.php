<?php

namespace App\Pay;

use App\Pay\Model\Deposit;
use App\Pay\Model\DepositMethod;
use App\Pay\Model\DepositResult;

/**
 * 支付接口
 * Author: huangkaixuan
 * Date: 2017/12/4
 * Time: 14:49
 */
interface DepositInterface
{
    const GOOD_NAME = "余额充值";

    /**
     * 向支付平台发出充值订单,返回支付信息给客户端
     *
     * @param $deposit_id string 充值id
     * @param $amount float
     * @param $config array 接口配置
     * @param $notify_url string 通知地址
     * @param $return_url
     * @return mixed 返回储值信息,失败返回null
     * @param $timeout int 订单超时秒数,null不设
     */
    public function deposit($deposit_id, $amount, array $config, $notify_url, $return_url, $timeout);

    /**
     * 接收支付平台的通知并解析结果
     *
     * @param $request
     * @config 接口配置
     * @return Deposit,不合法或交易不存在返回null
     */
    public function acceptNotify(array $config);

    /**
     * @param $method DepositMethod 所属支付方式
     * 解析同步跳转的充值信息,展示用,无需验证
     * @return DepositResult
     */
    public function parseReturn(DepositMethod $method);

    /**
     * 混淆充值id给外部接口
     * @return string
     */
    public function mixUpDepositId($depositId);
}