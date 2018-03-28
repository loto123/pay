<?php
/**
 * 原生苹果APP内购
 * Author: huangkaixuan
 * Date: 2018/3/26
 * Time: 13:50
 */

namespace App\Pay\Impl\Heepay;


use App\Pay\DepositInterface;
use App\Pay\IdConfuse;
use App\Pay\Model\Deposit;
use App\Pay\Model\DepositMethod;
use App\Pay\PayLogger;

class ApplePay implements DepositInterface
{
    const SIGN_KEY = 'T3AwZBw!z9]E';

    public function deposit($deposit_id, $amount, array $config, $notify_url, $return_url, $timeout)
    {
        //苹果支付由客户端自行完成,只需提供验证地址
        $order_id = $this->mixUpDepositId($deposit_id);
        $query = [
            'order_id' => $order_id,
            'sign' => self::makeSign($order_id),
        ];
        return ['notify_url' => $notify_url . '?' . http_build_query($query), 'product_name' => $config['product_name']];
    }

    public function mixUpDepositId($depositId)
    {
        return IdConfuse::mixUpId($depositId, 20, false);
    }

    private static function makeSign($order_id)
    {
        return md5('APPLEPAY_' . $order_id . self::SIGN_KEY);
    }

    public function acceptNotify(array $config)
    {
        $request = request();

        if (!$request->has('order_id') || !$request->has('sign')) {
            echo 'invalid request';
            return;
        }

        //合法性校验
        if (self::makeSign($request->get('order_id')) !== $request->get('sign')) {
            echo 'sign error';
            return;
        }

        $signature_base64data = base64_decode(file_get_contents('php://input'));
        $order_id = IdConfuse::recoveryId($request->get('order_id'), false);

        //充值到账
        $token = base64_decode($signature_base64data);

        if (strpos($token, 'Sandbox') > -1) {
            $is_sandbox = true;
        } else {
            $is_sandbox = false;
        }

        $iap = new ItunesReceiptValidator($is_sandbox, $signature_base64data);
        $info = $iap->validateReceipt();
        $info = array_pop($info->in_app);
        if (!(is_object($info) && property_exists($info, 'product_id')
            && property_exists($info, 'transaction_id'))
        ) {
            PayLogger::deposit()->error('苹果支付响应异常', ['info' => $info]);
            return null;
        }

        $product_id = $info->product_id;
        $transaction_id = $info->transaction_id;

        //取得订单

        /**
         * @var $order Deposit
         */
        $order = Deposit::where([['id', $order_id], ['state', Deposit::STATE_UNPAID]])->lockForUpdate()->first();//取出订单

        if (!$order) {
            PayLogger::deposit()->error('Apple 订单不存在' . $order_id, ['order' => $order]);
            return;
        }

        PayLogger::deposit()->debug('苹果响应', ['info' => $info]);

        if (Deposit::where([['method_id', $order->method_id], ['out_batch_no', $transaction_id]])->count() > 0) {
            //Receipt已使用
            PayLogger::deposit()->error('Apple Receipt已使用');
            return;
        }

        $replace_str = $config['product_name'] . '.';
        $amount_str = str_replace($replace_str, '', $product_id);    // 替换产品内容，得到充值金额
        $amount = floatval($amount_str);

        $order->out_batch_no = $transaction_id;

        if ($amount > 0 && $order->amount > $amount) {
            $order->state = Deposit::STATE_PART_PAID;
        } else {
            $order->state = Deposit::STATE_COMPLETE;
        }
        echo 'ok';


        return $order;
    }

    public function parseReturn(DepositMethod $method)
    {
        // TODO: Implement parseReturn() method.
    }

    public function benefitShare(array $config, Deposit $deposit)
    {
        return true;
    }
}