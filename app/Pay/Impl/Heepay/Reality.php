<?php
/**
 * Created by PhpStorm.
 * User: LIJUAN
 * Date: 2017/12/23
 * Time: 18:50
 */

namespace App\Pay\Impl\Heepay;


use App\Pay\Crypt3Des;
use App\PayInterfaceRecord;
use function GuzzleHttp\Psr7\parse_response;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class Reality
{

    /*
     * 验证实名认证
     * @param $name 姓名
     * @param $cert_no 身份证号
     * */
    public static function identify($record_id,$name, $cert_no)
    {
        $record = PayInterfaceRecord::find($record_id);
        if(empty($record)) {
            Log::info([
                'heepay_identify_fail'=>'找不到PayInterfaceRecord记录',
                'record_id'=> $record_id,
            ]);
            return false;
        }
        $heepay = Heepay::getConfig();
        $agent_id = $heepay['agent_id'];
        $key = $heepay['reality']['key'];
        $dsc = new Crypt3Des();
        $dsc->key = $heepay['reality']['des_key'];
        $params = [
            'version' => $heepay['version'],
            'agent_id' => $agent_id,
            'name' => $name,
            'cert_no' => $cert_no,
        ];
        //生成sign
        $sign_params = $params;
        $sign_params['key'] = $key;
        $sign_filed = ['version','agent_id','name','cert_no','key'];
        $params['sign'] = self::sign($sign_params,$sign_filed,'',true,true);

        //请求地址
        $params['name'] = $dsc->encrypt_cbc(mb_convert_encoding($name, "GB2312", "UTF-8"));
        $params['cert_no'] = $dsc->encrypt_cbc($cert_no);
        ksort($params);
        $request_url = $heepay['reality']['url'] . '?' . self::arrayToParam($params);
        //请求结果
        $res_xml = file_get_contents($request_url);
        $res_xml = iconv("gbk//IGNORE", "utf-8", $res_xml);
        $response = simplexml_load_string($res_xml);
        $result = false;
        if (!empty($response) && $response->ret_code == '0000' && $response->status=='Success') {
            $result = true;
        }
        //完善记录
        $record->request = json_encode([
            'params' => $params,
            'request_url' => $request_url,
        ]);
        $record->response = $res_xml;
        if($result === true) {
            $record->status = PayInterfaceRecord::DEAL_SUCCESS;
        } else {
            $record->status = PayInterfaceRecord::DEAL_FAIL;
        }
        $record->save();

        return $result;
    }


    /*
     * 银行卡鉴权
     * */
    public static function authentication($record_id,$bill_id,$bill_time,$card_no,$cert_no,$name)
    {
        $record = PayInterfaceRecord::find($record_id);
        if(empty($record)) {
            Log::info([
                'heepay_authentication_fail'=>'找不到PayInterfaceRecord记录',
                'record_id'=> $record_id,
            ]);
            return false;
        }
        $heepay = Heepay::getConfig();
        $dsc = new Crypt3Des();
        $dsc->key = $heepay['auth']['des_key'];
        $bank_card_info = $card_no . '|' . $cert_no . '|' . mb_convert_encoding($name, "GB2312", "UTF-8");
        $params = [
            'agent_id' => $heepay['agent_id'],
            'bill_id' => $bill_id,
            'bill_time' => $bill_time,
            'bank_card_type' => '1',
            'bank_card_info' => $dsc->encrypt($bank_card_info),
//            'cvv2' => '',
//            'expire_date'=>'',
            'client_ip' => request()->getClientIp(),
//            'desc' => '',
            'time_stamp' =>date('YmdHis'),
            'version_id' => $heepay['version'],
//            'is_test' => '1',
        ];

        //签名
        $sign_filed = ['agent_id','bill_id','bill_time','bank_card_type','bank_card_info','time_stamp'];
        $sign_key = '|||'.$heepay['auth']['key'];
        $params['sign'] = self::sign($params,$sign_filed,$sign_key);
        //请求地址
        $request_url = $heepay['auth']['url'] . '?' . self::arrayToParam($params);
        //请求结果
        $res = iconv("gbk//IGNORE", "utf-8", file_get_contents($request_url));
        parse_str($res,$data);
        if (isset($data['ret_code'])&& isset($data['status']) && $data['ret_code']==0 && $data['status']==1) {
            $result = true;
        } else {
            $ret_msg= $data['ret_msg']??'失败';
            $result = $ret_msg;
        }
        //完善记录
        $record->request = json_encode([
            'params' => $params,
            'request_url' => $request_url,
        ]);
        $record->response = $res;
        if($result === true) {
            $record->status = PayInterfaceRecord::DEAL_SUCCESS;
        } else {
            $record->status = PayInterfaceRecord::DEAL_FAIL;
        }
        $record->save();
        return $result;
    }

    //将数组以 键=值& 的方式拼接成字符串
    public static function arrayToParam(array $params)
    {
        $string = '';
        foreach($params as $item => $value) {
            $string .= $item . '=' . $value . '&';
        }
        return rtrim($string,'&');
    }

    /*
     * 生成签名
     * $params array 签名数据源
     * $fields array 需要签名的参数的字段名
     * $key 签名参数额外需要的数据
     * $ksort 签名参数是否需要按照参数的key排序
     * $lower 签名参数是否需要转成小写
     * */
    public static function sign(array $params, array $fields, $key, $sort=false, $lower=false)
    {
        if($sort){
            sort($fields);
        }
        if($lower){
            $params = array_map('strtolower', $params);
        }
        $string = '';
        foreach ($fields as $field) {
            $string .= "&$field={$params[$field]}";
        }
        $string = ltrim($string, '&') . $key;
        return md5($string);
    }
}