<?php
/**
 * 汇付宝微信h5支付
 * Author: huangkaixuan
 * Date: 2017/12/4
 * Time: 15:32
 */

namespace App\Pay\Impl\Heepay;

use App\Pay\CashInterface;
use App\Pay\Model\Deposit;

class WechatH5 implements CashInterface
{

    public function deposit($deposit_id, $amount, $master_container, array $config, $notify_url, $return_url)
    {
        $params = [
            'version' => 1,
            'is_phone' => 1,
            'agent_id' => $config['agent_id'],
            'pay_type' => 30,
            'is_frame' => strpos(request()->header('User-Agent'), 'MicroMessenger') !== false,
            'goods_name' => CashInterface::GOOD_NAME,
            'agent_bill_time' => date('Ymdhis'),
            'agent_bill_id' => $deposit_id,
            'notify_url' => $notify_url,
            'pay_amt' => $amount,
            'return_url' => $return_url,
            'user_ip' => request()->getClientIp(),
            'remark' => '',
        ];

        //参数转为小写
        array_walk($params, function (&$val) {
            $val = strtolower($val);
        });

        //坑爹,汇付宝说参数全部转换为小写,这个参数不要
        $params['meta_option'] = urlencode(base64_encode(mb_convert_encoding('{"s":"WAP","n":"游戏宝","id":"' . route('home') . '"}', "GB2312", "UTF-8")));

        //签名
        $fieldsToSign = ['version', 'agent_id', 'agent_bill_id', 'agent_bill_time', 'pay_type', 'pay_amt', 'notify_url', 'return_url', 'user_ip'];
        Heepay::makeSign($params, $fieldsToSign, $config['key']);
        $queryString = http_build_query($params);
        return $config['order_url'] . '?' . $queryString;
    }

    /**
     * 微信H5提现
     * @param string $withdraw_id
     * @param float $amount
     * @param array $receiver_info
     * @param array $config
     * @param string $notify_url
     */
    public function withdraw($withdraw_id, $amount, array $receiver_info, array $config, $notify_url)
    {
        //TODO 微信提现
    }

    public function receiverInfoDescription()
    {
        // TODO: Implement receiverInfoDescription() method.
    }

    /**
     * 展示充值结果
     * @return array ['out_batch_no' => xxx(可选), 'state' => Deposit::STATE_*, 'amount' => 充值金额]
     */
    public function displayReturn()
    {
        $request = request();
        return ['out_batch_no' => $request->get('jnet_bill_no'), 'state' => $request->get('result') == 1 ? Deposit::STATE_COMPLETE : Deposit::STATE_FAIL, 'amount' => $request->get('pay_amt')];
    }



}