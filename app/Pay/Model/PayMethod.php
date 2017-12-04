<?php
/**
 * 支付方式
 *
 * 一个支付平台支持多种支付方式
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

class PayMethod extends Model
{
    protected $table = 'pay_method';
}
