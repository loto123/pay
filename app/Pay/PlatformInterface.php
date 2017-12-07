<?php
/**
 * 支付平台接口
 * Author: huangkaixuan
 * Date: 2017/12/4
 * Time: 16:14
 */

namespace App\Pay;

use Illuminate\Http\Request;

interface PlatformInterface
{
    /**
     * 接收支付平台的通知并解析结果
     *
     * @param $request
     * @config 接口配置
     * @return mixed Withdraw/Deposit,不合法或交易不存在返回null
     */
    public function acceptNotify(Request $request, array $config);
}