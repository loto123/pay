<?php

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Platform
 * @package App\Pay
 *
 * 支付平台
 *
 * 例如微信，支付宝,汇付宝
 */
class Platform extends Model
{
    protected $table = 'pay_platform';

}
