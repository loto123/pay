<?php
/**
 * 结算容器资金提取
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

class MoneyExtract extends Model
{
    const CREATED_AT = 'extract_time';
    const UPDATED_AT = null;
    protected $table = 'pay_money_extract';

    /**
     * 所属结算容器
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function settleContainer()
    {
        return $this->belongsTo('App\Pay\Model\SettleContainer', 'settle_container');
    }
}
