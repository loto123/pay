<?php
/**
 * 小额批量付款
 * Author: huangkaixuan
 * Date: 2017/12/15
 * Time: 14:58
 */

namespace App\Pay\Impl\Heepay;


use App\Admin\Model\UploadFile;
use App\Pay\Crypt3Des;
use App\Pay\IdConfuse;
use App\Pay\Model\Withdraw;
use App\Pay\Model\WithdrawResult;
use App\Pay\PayLogger;
use App\Pay\WithdrawInterface;
use Illuminate\Support\Facades\Cache;

class SmallBatchTransfer implements WithdrawInterface
{
    /**
     * 查询省市
     */
    public static function queryProvincesAndCities($force = false)
    {
        $cacheKey = 'heepay_areas';
        if ($force) {
            Cache::forget($cacheKey);
        }
        $options = Cache::get($cacheKey, function () use ($cacheKey) {
            $xmlObj = simplexml_load_string(iconv("gbk//IGNORE", "utf-8", file_get_contents('https://pay.heepay.com/API/PayTransit/QueryProvincesAndCities.aspx')));
            $xmlObj = self::xmlToArr($xmlObj);
            $options = array_column(array_map(function ($area) {
                //dump($area);
                return ['city' => (array)$area['city'], 'province' => $area['@attributes']['name']];

            }, $xmlObj['province']), 'city', 'province');
            $options = json_encode($options, JSON_UNESCAPED_UNICODE);
            Cache::forever($cacheKey, $options);
            return $options;
        });
        return json_decode($options, true);

    }

    public static function xmlToArr(\SimpleXMLElement $xml)
    {
        return json_decode(json_encode($xml, JSON_UNESCAPED_UNICODE), true);
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
        //Log::info($notify_url);
        $result = new WithdrawResult();
        $card = $receiver_info['bank_card'];

        do {
            //网银提现
            $batch_no = $this->mixUpWithdrawId($withdraw_id);
            $sub_batch = substr($batch_no, 0, 20);
            $params = [
                'version' => 3,
                'agent_id' => $config['agent_id'],
                'batch_no' => $batch_no,
                'batch_amt' => $amount,
                'batch_num' => 1,
                'detail_data' => "$sub_batch^{$receiver_info['bank_no']}^0^{$card->card_num}^{$card->holder_name}^$amount^{$config['transfer_reason']}^{$card->province}^{$card->city}^{$card->bank->name}",
                'notify_url' => $notify_url,
                'ext_param1' => $batch_no,
            ];
            PayLogger::withdraw()->debug('汇付宝提现', $params);
            $params['key'] = $config['key_v3'];
            ksort($params);
            $params['sign'] = $this->makeSign($params);
            PayLogger::withdraw()->debug('md5', [$params['sign']]);
            unset($params['key']);

            $rep = new Crypt3Des(); // 初始化一个3des加密对象
            $rep->key = $config['3DES_key'];
            $detail_data_gbk = iconv("utf-8", "gbk//IGNORE", $params['detail_data']);
            $detail_data_des = $rep->encrypt($detail_data_gbk);
            $params['detail_data'] = $detail_data_des;
            $params['sign_type'] = 'MD5';

            $res_xml = self::send_post($config['url'], http_build_query($params), $config['cert_path']);
            $res_xml = iconv("gbk//IGNORE", "utf-8", $res_xml);
            PayLogger::withdraw()->debug('提现返回', [$res_xml]);
            $result->raw_response = $res_xml;
            $response = simplexml_load_string($res_xml);

            PayLogger::withdraw()->debug('xml对象', ['xml' => json_encode($response, JSON_UNESCAPED_UNICODE)]);
            if (empty($response)) {
                if ($response === false) {
                    PayLogger::withdraw()->error('xml解析错误!', [libxml_get_errors()]);
                } else {
                    PayLogger::withdraw()->error('汇付宝提现返回空');
                }
                break;
            }

            if ($response->ret_code == '0000') {
                $result->state = Withdraw::STATE_SUBMIT;
            } else {
                $result->raw_response = json_encode($response->ret_msg, JSON_UNESCAPED_UNICODE);
                break;
            }


        } while (false);

        return $result;

    }

    public function mixUpWithdrawId($withdrawId)
    {
        return IdConfuse::mixUpId($withdrawId, 30); //对外统一30位长度
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
        return md5(strtolower($string));
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
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $send_type);
        if ($cacert_url) {
            curl_setopt($ch, CURLOPT_CAINFO, UploadFile::getFile($cacert_url));     //证书地址
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);
        if (curl_error($ch)) {
            PayLogger::common()->error("汇付宝{$send_type}请求错误", [
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
        PayLogger::withdraw()->debug("汇付宝响应$http_code");

        if ($http_code_int === 200) {       // 只接收 200 返回的数据
            return $server_output;
        }

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
            $params['ret_msg'] = iconv("gbk//IGNORE", "utf-8", request()->query('ret_msg'));
            $params['detail_data'] = iconv("gbk//IGNORE", "utf-8", request()->query('detail_data'));

            //参数转为小写
            array_walk($params, function (&$val) {
                $val = strtolower($val);
            });
            $key = strtolower($config['key_v3']);//key和下单不一样，必须小写

            //验证签名
            $validSign = WechatH5::makeSign($params, ['ret_code', 'ret_msg', 'agent_id', 'hy_bill_no', 'status', 'batch_no', 'batch_amt', 'batch_num', 'detail_data', 'ext_param1'], $key);
            if ($validSign !== $params['sign']) {
                PayLogger::withdraw()->error('签名错误,valid ' . $validSign . ',give:' . $params['sign']);
                break;
            }

            PayLogger::withdraw()->info('签名正确');

            $withdraw = Withdraw::where([['id', IdConfuse::recoveryId($params['ext_param1'])], ['state', Withdraw::STATE_SUBMIT]])->lockForUpdate()->first();
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
            'bank_card' => '银行卡对象,后端设置'
        ];
    }

    public function queryState(Withdraw $withdraw, array $config)
    {
        // TODO: Implement queryState() method.
    }
}