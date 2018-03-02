<?php
/**
 * 汇付宝微信h5支付
 * Author: huangkaixuan
 * Date: 2017/12/4
 * Time: 15:32
 */

namespace App\Pay\Impl\Heepay;

use App\Pay\DepositInterface;
use App\Pay\IdConfuse;
use App\Pay\Model\Deposit;
use App\Pay\Model\DepositMethod;
use App\Pay\Model\DepositResult;
use App\Pay\PayLogger;

class WechatH5 implements DepositInterface
{
    protected $wrapper = null;
    private $meta_option; //包装平台,DepositMethod::OS_*

    public function deposit($deposit_id, $amount, array $config, $notify_url, $return_url, $timeout)
    {
        $params = [
            'version' => 1,
            'is_phone' => 1,
            'agent_id' => $config['agent_id'],
            'pay_type' => 30,
            'is_frame' => (int)(strpos(request()->header('User-Agent'), 'MicroMessenger') !== false),
            'goods_name' => mb_convert_encoding(DepositInterface::GOOD_NAME, 'GB2312', 'UTF-8'),
            'agent_bill_time' => date('Ymdhis'),
            'agent_bill_id' => $this->mixUpDepositId($deposit_id),
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

        //订单提交时间
        $params['timestamp'] = time() * 1000;
        $config['key_v1'] = "{$config['key_v1']}&timestamp={$params['timestamp']}";

        switch ($this->wrapper) {
            case null:
                //原生H5
                $this->setMetaOption('WAP', $config['website_name'], route('home'));
                break;
            case DepositMethod::OS_ANDROID:
                //安卓
                $this->setMetaOption('Android', $config['android_app_name'], $config['android_apk_key']);
                break;
            case DepositMethod::OS_IOS:
                //苹果
                $this->setMetaOption('IOS', $config['ios_app_name'], $config['ios_app_key']);
                break;
            default:
                return null;
        }
        //坑爹,汇付宝说参数全部转换为小写,这个参数不要
        $params['meta_option'] = urlencode(base64_encode(mb_convert_encoding($this->meta_option, "GB2312", "UTF-8")));

        //签名
        $fieldsToSign = ['version', 'agent_id', 'agent_bill_id', 'agent_bill_time', 'pay_type', 'pay_amt', 'notify_url', 'return_url', 'user_ip'];
        self::appendSign($params, $fieldsToSign, $config['key_v1']);
        $queryString = http_build_query($params);
        return $config['url'] . '?' . $queryString;
    }

    public function mixUpDepositId($depositId)
    {
        return IdConfuse::mixUpId($depositId, 30);
    }

    /**
     * 微信H5APP包装仅有meta_option不同
     */
    private function setMetaOption($s, $n, $id)
    {
        $this->meta_option = "{\"s\":\"$s\",\"n\":\"$n\",\"id\":\"$id\"}";
    }

    /**
     * 附加签名
     * @param $params
     * @param $fieldsToSign array 签名字段,保持顺序
     */
    public static function appendSign(&$params, $fieldsToSign, $key)
    {
        $params['sign'] = self::makeSign($params, $fieldsToSign, $key);
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
        PayLogger::deposit()->debug('签名原消息:' . $string);
        return md5($string);
    }

    /**
     * 展示充值结果
     * @return DepositResult
     */
    public function parseReturn(DepositMethod $method)
    {
        $request = request();
        return new DepositResult($request->get('result') == 1 ? Deposit::STATE_COMPLETE : Deposit::STATE_PAY_FAIL, IdConfuse::recoveryId($request->get('agent_bill_id')), $request->get('pay_amt'), $request->get('jnet_bill_no'));
    }

    public function acceptNotify(array $config)
    {
        //充值通知
        do {
            $params = request()->query();
            //验证签名
            $validSign = self::makeSign($params, ['result', 'agent_id', 'jnet_bill_no', 'agent_bill_id', 'pay_type', 'pay_amt', 'remark'], $config['key_v1']);
            if ($validSign !== $params['sign']) {
                PayLogger::deposit()->critical('汇付宝通知md5计算错误', ['答案' => $validSign, '当前' => $params['sign']]);
                break;
            }

            //支付成功
            if ($params['result'] == 1) {
                /*
                 *@var $deposit Deposit
                 */
                $deposit = Deposit::where([['id', IdConfuse::recoveryId($params['agent_bill_id'])], ['state', Deposit::STATE_UNPAID]])->lockForUpdate()->first();//取出订单
                if (!$deposit) {
                    return null;
                }

                $deposit->out_batch_no = $params['jnet_bill_no'];

                if ($params['pay_amt'] > 0 && $deposit->amount > $params['pay_amt']) {
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

    public function benefitShare(array $config, Deposit $deposit)
    {
        $url = 'https://pay.heepay.com/API/Payment/GuaranteeAllotSubmit.aspx';
        if (!$deposit->out_batch_no) {
            throw new \Exception('汇付宝返回的单据号为空');
        }
        $allot_data = $config['allot_data'];
        $allot_result_arr = [];
        foreach (explode('|', $allot_data) as $allot_item) {
            $splits = explode('^', $allot_item);
            $share_percent = $splits[1];
            if ($share_percent !== 'remain_amt') {
                $splits[1] = round(floatval($share_percent) * $deposit->amount, 4);
                if ($splits[1] <= 0) {
                    continue;
                }
            }
            $allot_result_arr [] = implode('^', $splits);
        }

        if (!$allot_result_arr) {
            throw new \Exception("分润配置无效:$allot_data");
        }

        $allot_result_str = implode('|', $allot_result_arr);
        $allot_result_str = iconv('UTF-8', 'GB2312//IGNORE', $allot_result_str);
        $params = [
            'version' => 1,
            'agent_id' => $config['agent_id'],
            'agent_bill_id' => $this->mixUpDepositId($deposit->getKey()),
            'jnet_bill_no' => $deposit->out_batch_no,
            'allot_data' => $allot_result_str,
            'timestamp' => time() * 1000,
        ];

        //参数转为小写
        array_walk($params, function (&$val) {
            $val = strtolower($val);
        });

        $key = $config['key_v1'];

        //签名
        $fieldsToSign = ['version', 'agent_id', 'agent_bill_id', 'jnet_bill_no', 'allot_data', 'timestamp'];
        self::appendSign($params, $fieldsToSign, $key);

        $response = SmallBatchTransfer::send_post($url, $params, $config['cert_path']);

        $response = strtolower($response);

        $response_arr = [];
        foreach (explode('|', $response) as $kv_pair) {
            $response_arr[strtok($kv_pair, '=')] = strtok($kv_pair);
        }
        if (self::makeSign($response_arr, ['ret_code', 'ret_msg', 'agent_bill_id', 'jnet_bill_no', 'total_amt', 'timestamp'], $key) != $response_arr['sign']) {
            throw new \Exception('分润签名验证错误');
        }
        $response = mb_convert_encoding($response, 'utf-8', 'gb2312');

        if ($response_arr['ret_code'] === '0001') {
            return true;
        }

        throw new \Exception("分润失败:$response,allot_data:$allot_data");
    }
}