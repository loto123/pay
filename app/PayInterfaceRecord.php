<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PayInterfaceRecord extends Model
{
    //订单处理状态
    const UN_DEAL = 0;
    const DEAL_SUCCESS = 1;
    const UN_RESPONSE = 2;
    const DEAL_FAIL = 3;

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public static function get_type_name($type)
    {
        switch ($type) {
            case '1':
                return '实名认证';
            case '2':
                return '银行卡鉴权';
            default:
                return '';
        }
    }

    public static function get_status_name($status)
    {
        switch ($status) {
            //0：未提交，1：已提交未响应，2：处理成功，3：处理失败'
            case '0':
                return '未提交';
            case '1':
                return '匹配成功';
            case '2':
                return '已提交';
            case '3':
                return '匹配失败';
            default:
                return '';
        }
    }




}
