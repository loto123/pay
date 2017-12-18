<?php
/**
 * 小额批量付款
 * Author: huangkaixuan
 * Date: 2017/12/15
 * Time: 14:58
 */

namespace App\Pay\Impl\Heepay;


use App\Pay\Crypt3Des;
use App\Pay\Model\Withdraw;
use App\Pay\WithdrawInterface;
use Illuminate\Support\Facades\Log;

class SmallBatchTransfer implements WithdrawInterface
{

    public static function getBankNoMap()
    {
        return [
            0 => '汇付宝账户',
            1 => '工商银行',
            2 => '建设银行',
            3 => '农业银行',
            4 => '邮政储蓄银行',
            5 => '中国银行',
            6 => '交通银行',
            7 => '招商银行',
            8 => '光大银行',
            9 => '浦发银行',
            10 => '华夏银行',
            11 => '广东发展银行',
            12 => '中信银行',
            13 => '兴业银行',
            14 => '民生银行',
            15 => '杭州银行',
            16 => '上海银行',
            17 => '宁波银行',
            18 => '平安银行',
            38 => '浙江泰隆商业银行',
        ];
    }

    /**
     * 银行卡提现
     * @param string $withdraw_id
     * @param float $amount
     * @param array $receiver_info
     * @param array $config
     * @param string $notify_url
     */
    public function withdraw($withdraw_id, $amount, array $receiver_info, array $config, $notify_url)
    {
        Log::info($notify_url);
        $result = ['state' => Withdraw::STATE_SEND_FAIL];

        do {
            //网银提现
            $batch_no = sprintf('%030d', $withdraw_id); //对外统一30位长度
            $sub_batch = sprintf('%020d', $withdraw_id);
            $params = [
                'version' => 3,
                'agent_id' => $config['agent_id'],
                'batch_no' => $batch_no,
                'batch_amt' => $amount,
                'batch_num' => 1,
                'detail_data' => "$sub_batch^{$receiver_info['bank_no']}^{$receiver_info['to_public']}^{$receiver_info['receiver_account']}^{$receiver_info['receiver_name']}^$amount^余额提现^{$receiver_info['province']}^{$receiver_info['city']}^{$receiver_info['branch_bank']}",
                'notify_url' => $notify_url,
                'ext_param1' => $withdraw_id,
            ];
            Log::info($params);
            $params['key'] = $config['key'];
            ksort($params);
            $params['sign'] = $this->makeSign($params);
            unset($params['key']);

            $rep = new Crypt3Des(); // 初始化一个3des加密对象
            $rep->key = $config['3DES_key'];
            $detail_data_gbk = iconv("utf-8", "gbk//IGNORE", $params['detail_data']);
            $detail_data_des = $rep->encrypt($detail_data_gbk);
            $params['detail_data'] = $detail_data_des;
            $params['sign_type'] = 'MD5';

            $res_xml = self::send_post($config['url'], http_build_query($params), $config['cert_path']);
            $res_xml = iconv("gbk//IGNORE", "utf-8", $res_xml);
            $result['raw_response'] = $res_xml;
            $response = simplexml_load_string($res_xml);

            if (empty($response)) {
                break;
            }

            if ($response->ret_code == '0000') {
                $result['state'] = Withdraw::STATE_SUBMIT;
            } else {
                $result['raw_response'] = json_encode($response->ret_msg, JSON_UNESCAPED_UNICODE);
                break;
            }


        } while (false);

        return $result;

    }

    /**
     * 生成摘要
     * @param $params
     * @return string
     */
    private function makeSign($params)
    {
        $string = '';
        foreach ($params as $field => $val) {
            $string .= "&$field=$val";
        }
        $string = ltrim($string, '&');
        return md5($string);
    }

    /**
     * 带证书POST数据
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
            Log::info([
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

    /**
     * 提现通知
     * @param array $config
     * @return null|Withdraw
     */
    public function acceptNotify(array $config)
    {
        do {
            $params = request()->query();

            //参数转为小写
            array_walk($params, function (&$val) {
                $val = strtolower($val);
            });

            $params['detail_data'] = iconv("gbk//IGNORE", "utf-8", request()->query('detail_data'));

            //验证签名
            $validSign = WechatH5::makeSign($params, ['ret_code', 'ret_msg', 'agent_id', 'hy_bill_no', 'status', 'batch_no', 'batch_amt', 'batch_num', 'detail_data', 'ext_param1'], $config['key']);
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

    public function receiverInfoDescription()
    {
        return [
            'bank_no' => '银行编号',
            'to_public' => '是否对公,0/1',
            'receiver_account' => '收款账号',
            'receiver_name' => '收款人姓名',
            'province' => '省份',
            'city' => '城市',
            'branch_bank' => '支行名称'
        ];
    }
}