<?php
/**
 * 转账分润
 * 手续费是一种特殊的分润形式
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

class ProfitShare extends Model
{
    public $timestamps = false;
    protected $table = 'pay_profit_share';

    /**
     * 转账来源
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transfer()
    {
        return $this->belongsTo('App\Pay\Model\Transfer');
    }

    /**
     * 接收分润容器
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receiveContainer()
    {
        return $this->belongsTo('App\Pay\Model\Container', 'receive_container');
    }
}
