<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserCard extends Model
{
    //

    const IDENTIFY_TYPE = 1; //实名认证
    const AUTH_TYPE = 2; //银行卡鉴权

    public function bank()
    {
        return $this->hasOne(Bank::class, 'id', 'bank_id');
    }

    //生成订单号
    public static function createUniqueId()
    {
        return date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /*
    16-19 位卡号校验位采用 Luhm 校验方法计算：
    1，将未带校验位的 15 位卡号从右依次编号 1 到 15，位于奇数位号上的数字乘以 2
    2，将奇位乘积的个十位全部相加，再加上所有偶数位上的数字
    3，将加法和加上校验位能被 10 整除。
    */
    function luhm($s)
    {
        $n = 0;
        for ($i = strlen($s); $i >= 1; $i--) {
            $index = $i - 1;
            //偶数位
            if ($i % 2 == 0) {
                $n += $s{$index};
            } else {//奇数位
                $t = $s{$index} * 2;
                if ($t > 9) {
                    $t = (int)($t / 10) + $t % 10;
                }
                $n += $t;
            }
        }
        return ($n % 10) == 0;
    }

}
