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
            sort($quota_list);
        } catch (\Exception $e){
            $quota_list = ['100','200','500','1000','5000'];
        }

        switch ($type) {
            case 1:
                $withdraw_method = WithdrawMethod::find($pay_method_id);
                if(!empty($withdraw_method) && isset($withdraw_method['max_quota'])) {
                    if(floor($withdraw_method['max_quota']) > 0) {
                        $data = [];
                        foreach ($quota_list as $_quota) {
                            if($_quota <= $withdraw_method['max_quota']) {
                                $data[] = $_quota;
                            }
                        }
                        return $data;
                    } else {
                        return $quota_list;
                    }
                } else {
                    return false;
                }

            case 2:
                return $quota_list;

            default:
                return false;
        }
    }
}