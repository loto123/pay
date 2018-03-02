<?php
/**
 * Created by PhpStorm.
 * User: LIJUAN
 * Date: 2018/1/23
 * Time: 9:31
 */

namespace App\Pay\Model;


class PayQuota
{
    /*
     * 获取支付方式对应额度
     * @param $type integer 1：提现，2：充值
     * @param $pay_method_id integer 支付方式id
     * @return array 成功则返回额度数组，失败返回false
     * */
    public static function getPayQuotas($type, $pay_method_id=NULL)
    {
        try{
            $quota_list = json_decode(config('pay_quota_list'),true);
        } catch (\Exception $e){
            $quota_list = ['100','200','500','1000','5000'];
        }
        sort($quota_list);

        switch ($type) {
            case 1:
                $withdraw_method = WithdrawMethod::find($pay_method_id);
                if(!empty($withdraw_method) && isset($withdraw_method['max_quota'])) {
                    //后台配置中的提现最大最小限制
                    $tixian_min = config('tixian_min');
                    $tixian_max = config('tixian_max');
                    if($tixian_min || $tixian_max) {
                        foreach ($quota_list as $key => $_quota) {
                            if(($_quota < $tixian_min) || ($_quota > $tixian_max)) {
                                unset($quota_list[$key]);
                            }
                        }
                    }

                    //提现方式设置的最大值限制
                    if(floor($withdraw_method['max_quota']) > 0) {
                        $data = [];
                        foreach ($quota_list as $_quota) {
                            if($_quota <= $withdraw_method['max_quota']) {
                                $data[] = $_quota;
                            }
                        }
                        return $data;
                    } else {
                        //数组下标从0开始的是数组，从1开始的会被解析成对象
                        return array_values($quota_list);
                    }
                } else {
                    return false;
                }

            case 2:
                return array_values($quota_list);

            default:
                return false;
        }
    }
}