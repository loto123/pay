<?php
/**
 * Created by PhpStorm.
 * User: LIJUAN
 * Date: 2017/12/23
 * Time: 18:50
 */

namespace App\Pay\Impl\Heepay;


use App\Pay\Crypt3Des;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class Reality
{

    /*
     * 验证实名认证
     * @param $name 姓名
     * @param $cert_no 身份证号
     * */
    public static function identify($name, $cert_no)
    {
        $heepay = Heepay::getConfig();
        $agent_id = $heepay['agent_id'];
        $key = $heepay['key'];
        $dsc = new Crypt3Des();
        $dsc->key = $heepay['des_key'];
        $params = [
            'version' => 1,
            'agent_id' => $agent_id,
            'name' => $name,
            'cert_no' => $cert_no,
        ];
        //生成sign
        $sign_param = $params;
        $sign_param['key'] = $key;
        $sign_param = array_map('strtolower', $sign_param);
        ksort($sign_param);
        $params['sign'] = md5(self::arrayToParam($sign_param));
        //请求地址
        $name = (mb_convert_encoding($name, "GB2312", "UTF-8"));
        $params['name'] = $dsc->encrypt_cbc($name);
        $params['cert_no'] = $dsc->encrypt_cbc($cert_no);
        ksort($params);
        $request_url = $heepay['reality_rul'] . '?' . self::arrayToParam($params);
        //请求结果
        $res_xml = file_get_contents($request_url);
        $res_xml = iconv("gbk//IGNORE", "utf-8", $res_xml);
        $response = simplexml_load_string($res_xml);
        if (empty($response)) {
            return false;
        }
        if ($response->ret_code == '0000' && $response->status=='Success') {
            return true;
        } else {
            Log::info(['params'=>$params,'request_url'=>$request_url,'res_xml'=>$res_xml]);
            return false;
        }
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

}