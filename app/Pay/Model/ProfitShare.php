<?php
/**
 * 转账分润
 * 手续费是一种特殊的分润形式
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

class ProfitShare extends Model
{
    protected $table = 'pay_profit_share';
}
