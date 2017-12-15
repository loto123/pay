<?php
/**
 * 网银支付
 * Author: huangkaixuan
 * Date: 2017/12/15
 * Time: 14:58
 */

namespace App\Pay\Impl\Heepay;


use App\Pay\CashInterface;
use App\Pay\Crypt3Des;
use App\Pay\Model\Withdraw;

class InternetBank implements CashInterface
{

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
        $result = ['state' => Withdraw::STATE_SEND_FAIL];

        do {
            //网银提现
            $withdraw_id = sprintf('%030d', $withdraw_id); //对外统一30位长度
            $batch_no = "{$withdraw_id}0";
            $params = [
                'version' => 3,
                'agent_id' => $config['agent_id'],
                'batch_no' => $withdraw_id,
                'batch_amt' => $amount,
                'batch_num' => 1,
                'detail_data' => "$batch_no^{$receiver_info['bank_no']}^{$receiver_info['to_public']}^{$receiver_info['receiver_account']}^{$receiver_info['receiver_name']}^$amount^余额提现^{$receiver_info['province']}^{$receiver_info['city']}^{$receiver_info['bank']}",
                'notify_url' => $notify_url,
                'ext_param1' => $withdraw_id,
            ];
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

            $res_xml = Heepay::send_post($config['batch_transfer_small_url'], http_build_query($params), $config['cert_path']);
            $res_xml = iconv("gbk//IGNORE", "utf-8", $res_xml);
            $response = simplexml_load_string($res_xml);

            $result['raw_response'] = $res_xml;
            if (empty($response)) {
                break;
            }

            if ($response->ret_code == '0000') {
                $result['state'] = Withdraw::STATE_SUBMIT;
            } else {
                $result['raw_response'] = $response->ret_msg;
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

    public function receiverInfoDescription()
    {
        return [
            'bank_no' => '银行编号',
            'to_public' => '是否对公,0/1',
            'receiver_account' => '收款账号',
            'receiver_name' => '收款人姓名',
            'province' => '省份',
            'city' => '城市',
            'bank' => '支行名称'
        ];
    }

    public function deposit($deposit_id, $amount, $master_container, array $config, $notify_url, $return_url)
    {
        //TODO 网银充值
    }

    public function displayReturn()
    {
        //TODO 充值返回
    }


}