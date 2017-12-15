<?php
/**
 * 汇付宝
 * Author: huangkaixuan
 * Date: 2017/12/4
 * Time: 16:20
 */

namespace App\Pay\Impl\Heepay;

use App\Pay\Model\Deposit;
use App\Pay\Model\Withdraw;
use App\Pay\PlatformInterface;
use Illuminate\Support\Facades\Log;

class Heepay implements PlatformInterface
{
    /**
     * POST数据
     * @param $url
     * @param $data
     * @param string $send_type
     * @return bool|mixed
     */
    public static function send_post($url, $data, $cacert_url, $send_type = 'POST')
    {
        $cacert_url = __DIR__ . $cacert_url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $send_type);
        curl_setopt($ch, CURLOPT_CAINFO, $cacert_url);     //证书地址
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);
        if (curl_error($ch)) {
            Log::write([
                'curl_url' => $url,
                'curl_errno' => curl_errno($ch),
                'curl_error' => curl_error($ch),
            ]);
            curl_close($ch);
            return false;
        }
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $http_code_int = intval($http_code);
        Log::info('http_code:' . $http_code);

        if ($http_code_int === 200) {       // 只接收 200 返回的数据
            return $server_output;
        }

        Log::info('http_code_error:' . $url);
        return false;
    }

    public function acceptDepositNotify(array $config)
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
     * 提现通知
     * @param array $config
     * @return null|Withdraw
     */
    public function acceptWithdrawNotify(array $config)
    {
        //充值通知
        do {
            $params = request()->query();

            //参数转为小写
            array_walk($params, function (&$val) {
                $val = strtolower($val);
            });

            $params['detail_data'] = iconv("gbk//IGNORE", "utf-8", request()->query('detail_data'));

            //验证签名
            $validSign = self::makeSign($params, ['ret_code', 'ret_msg', 'agent_id', 'hy_bill_no', 'status', 'batch_no', 'batch_amt', 'batch_num', 'detail_data', 'ext_param1'], $config['key']);
            if ($validSign !== $params['sign']) {
                break;
            }

            $withdraw = Withdraw::where([['id', $params['ext_param1']], ['state', Withdraw::STATE_SUBMIT]])->lockForUpdate()->first();
            if (!$withdraw) {
                break;
            }

            if ($params['ret_code'] == '0000' && $params['status'] != -1) {
                if ($params['batch_num'] > 0) {
                    //付款成功
                    $withdraw->state = Withdraw::STATE_COMPLETE;
                }
            } else {
                $withdraw->state = Withdraw::STATE_PROCESS_FAIL;

            }
            echo 'ok';
            return $withdraw;
        } while (false);

        echo 'error';
        return null;
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
}