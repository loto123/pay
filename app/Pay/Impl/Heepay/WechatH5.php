<?php
/**
 * 汇付宝微信h5支付
 * Author: huangkaixuan
 * Date: 2017/12/4
 * Time: 15:32
 */

namespace App\Pay\Impl\Heepay;

use App\Pay\DepositInterface;
use App\Pay\Model\Deposit;

class WechatH5 implements DepositInterface
{

    public function deposit($deposit_id, $amount, $master_container, array $config, $notify_url, $return_url)
    {
        $params = [
            'version' => 1,
            'is_phone' => 1,
            'agent_id' => $config['agent_id'],
            'pay_type' => 30,
            'is_frame' => (int)(strpos(request()->header('User-Agent'), 'MicroMessenger') !== false),
            'goods_name' => DepositInterface::GOOD_NAME,
            'agent_bill_time' => date('Ymdhis'),
            'agent_bill_id' => $deposit_id,
            'notify_url' => $notify_url,
            'pay_amt' => $amount,
            'return_url' => $return_url,
            'user_ip' => str_replace('.', '_', request()->getClientIp()),
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
        self::appendSign($params, $fieldsToSign, $config['key']);
        $queryString = http_build_query($params);
        return $config['url'] . '?' . $queryString;
    }

    /**
     * 附加签名
     * @param $params
     * @param $fieldsToSign array 签名字段,保持顺序
     */
    public function appendSign(&$params, $fieldsToSign, $key)
    {
        $params['sign'] = $this->makeSign($params, $fieldsToSign, $key);

    }

    /**
     * 生成签名
     * @param $params array 传入参数
     * @param $fields array 签名字段,保持顺序
     * @return string
     */
    public static function makeSign(array $params, array $fields, $key)
    {
        $string = '';
        foreach ($fields as $field) {
            $string .= "&$field={$params[$field]}";
        }
        $string = ltrim($string, '&') . "&key=$key";
        return md5($string);
    }

    /**
     * 展示充值结果
     * @return array ['out_batch_no' => xxx(可选), 'state' => Deposit::STATE_*, 'amount' => 充值金额]
     */
    public function parseReturn()
    {
        $request = request();
        return ['out_batch_no' => $request->get('jnet_bill_no'), 'state' => $request->get('result') == 1 ? Deposit::STATE_COMPLETE : Deposit::STATE_FAIL, 'amount' => $request->get('pay_amt')];
    }

    public function acceptNotify(array $config)
    {
        //充值通知
        do {
            $params = request()->query();
            //验证签名
            $validSign = self::makeSign($params, ['result', 'agent_id', 'jnet_bill_no', 'agent_bill_id', 'pay_type', 'pay_amt', 'remark'], $config['key']);
            if ($validSign !== $params['sign']) {
                if (config('debug')) {
                    echo $validSign;
                }
                break;
            }

            //支付成功
            if ($params['result'] == 1) {
                /*
                 *@var $deposit Deposit
                 */
                $deposit = Deposit::where([['id', $params['agent_bill_id']], ['state', Deposit::STATE_UNPAID]])->lockForUpdate()->first();//取出订单
                if (!$deposit) {
                    return null;
                }

                $deposit->out_batch_no = $params['jnet_bill_no'];

                if ($params['pay_amt'] > 0 && $deposit->amount > $deposit['pay_amt']) {
                    $deposit->state = Deposit::STATE_PART_PAID;
                } else {
                    $deposit->state = Deposit::STATE_COMPLETE;
                }
                echo 'ok';
                return $deposit;

            }
        } while (false);

        echo 'error';
        return null;
    }



}