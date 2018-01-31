<?php
/**
 *
 * @transaction safe
 * 结算容器资金提取
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

class MoneyExtract extends Model
{
    const CREATED_AT = 'extract_time';
    const UPDATED_AT = null;
    protected $table = 'pay_money_extract';
    protected $casts = [
        'amount' => 'float',
        'created_at' => 'datetime'
    ];
    protected $fillable = ['amount'];

    /**
     * 所属结算容器
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function settleContainer()
    {
        return $this->belongsTo(SettleContainer::class, 'settle_container');
    }
}
