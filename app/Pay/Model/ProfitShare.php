<?php
/**
 * 转账分润
 * 手续费是一种特殊的分润形式
 *
 * @transaction safe
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

class ProfitShare extends Model
{
    public $timestamps = false;
    protected $table = 'pay_profit_share';
    protected $casts = [
        'amount' => 'float',
        'is_frozen' => 'boolean'
    ];
    protected $guarded = ['id', 'transfer_id'];

    /**
     * 转账来源
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transfer()
    {
        return $this->belongsTo(Transfer::class);
    }

    /**
     * 接收容器
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function receiveContainer()
    {
        return $this->morphTo(null, 'container_type', 'receive_container');
    }
}
