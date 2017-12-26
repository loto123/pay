<?php
/**
 * Created by PhpStorm.
 * User: LIJUAN
 * Date: 2017/12/23
 * Time: 19:00
 */

namespace App\Pay\Impl\Heepay;


class Heepay
{
    public static function getConfig()
    {
        return [
            'agent_id'  =>  '1664502',
            'version' => 1,
            'key' => '5716FE2DEE9C495F98C710F2',
            'des_key' => '6044BDC53A814294A275579B',
            'reality_rul' => 'https://www.heepay.com/API/Merchant/Reality.aspx',
        ];
    }
}