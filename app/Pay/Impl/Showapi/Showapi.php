<?php

namespace App\Pay\Impl\Showapi;

use App\PayInterfaceRecord;
use Illuminate\Support\Facades\Log;

/**
 * Created by PhpStorm.
 * User: LIJUAN
 * Date: 2018/1/31
 * Time: 16:37
 */
class Showapi
{

    const PLATFORM = 'showapi';

    /*
     * 验证实名认证
     * @param $name 姓名
     * @param $cert_no 身份证号
     * */
    public static function identify($record_id, $name, $cert_no)
    {
        $record = PayInterfaceRecord::find($record_id);
        if(empty($record)) {
            Log::info([
                'showapi_identify_fail'=>'找不到PayInterfaceRecord记录',
                'record_id'=> $record_id,
            ]);
            return false;
        }

        if(!self::getConfig()) {
            Log::info([
                'showapi_identify_fail'=>'showapi配置错误',
                'record_id'=> $record_id,
            ]);
            return false;
        }

        //请求接口
        $paramArr = [
            'showapi_appid'=> self::getConfig()['appid'],
            'idcard'=> $cert_no,
            'name'=> $name
        ];
        $param = self::createParam($paramArr, self::getConfig()['secret']);
        $url = 'http://route.showapi.com/1072-1?';
        $request_url = $url . $param;
        $res_json = file_get_contents($request_url);
        $res = json_decode($res_json,true);
        Log::info($res);

        //处理结果
        if(isset($res['showapi_res_code']) && $res['showapi_res_code']===0
            && isset($res['showapi_res_body']['code']) && $res['showapi_res_body']['code']===0) {
            $result = true;
        } else {
            $result = isset($res['showapi_res_body']['error']) ? $res['showapi_res_body']['error'] : '失败';
        }

        //完善记录
        $record->request = json_encode([
            'params' => $param,
            'request_url' => $url,
        ]);
        $record->response = $res_json;
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
    public static function authentication($record_id,$card_no,$cert_no,$name)
    {
        $record = PayInterfaceRecord::find($record_id);
        if(empty($record)) {
            Log::info([
                'showapi_authentication_fail'=>'找不到PayInterfaceRecord记录',
                'record_id'=> $record_id,
            ]);
            return '请求失败';
        }

        if(!self::getConfig()) {
            Log::info([
                'showapi_identify_fail'=>'showapi配置错误',
                'record_id'=> $record_id,
            ]);
            return '请求失败！';
        }

        $paramArr = [
            'showapi_appid'=> self::getConfig()['appid'],
            'acct_pan'=> $card_no,
            'acct_name'=> $name,
            'cert_type'=> '01',
            'cert_id'=> $cert_no,
            'needBelongArea'=> true
        ];
        $param = self::createParam($paramArr, self::getConfig()['secret']);
        $url = 'http://route.showapi.com/1072-4?';
        $request_url = $url . $param;
        $res_json = file_get_contents($request_url);
        $res = json_decode($res_json,true);
//        Log::info($res);

        //处理结果
        if(isset($res['showapi_res_code']) && $res['showapi_res_code']===0
            && isset($res['showapi_res_body']['code']) && $res['showapi_res_body']['code']===0) {
            $result = true;
        } else {
            $result = isset($res['showapi_res_body']['error']) ? $res['showapi_res_body']['error'] : '失败';
        }

        //完善记录
        $record->request = json_encode([
            'params' => $param,
            'request_url' => $request_url,
        ]);
        $record->response = $res_json;
        if($result === true) {
            $record->status = PayInterfaceRecord::DEAL_SUCCESS;
        } else {
            $record->status = PayInterfaceRecord::DEAL_FAIL;
        }
        $record->save();

        return $result;
    }

    //创建参数(包括签名的处理)
    public static function createParam ($paramArr,$showapi_secret) {
        $paraStr = "";
        $signStr = "";
        ksort($paramArr);
        foreach ($paramArr as $key => $val) {
            if ($key != '' && $val != '') {
                $signStr .= $key.$val;
                $paraStr .= $key.'='.urlencode($val).'&';
            }
        }
        $signStr .= $showapi_secret;//排好序的参数加上secret,进行md5
        $sign = strtolower(md5($signStr));
        $paraStr .= 'showapi_sign='.$sign;//将md5后的值作为参数,便于服务器的效验
        return $paraStr;
    }

    private static function getConfig()
    {
        if(config('showapi_config')) {
            try{
                $config = json_decode(config('showapi_config'),true);
            }catch (\Exception $e) {
                return false;
            }
            if(!empty($config['appid']) && !empty($config['secret'])) {
                return $config;
            }
        }
        return false;
    }
}