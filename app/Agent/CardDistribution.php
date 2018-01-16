<?php
/**
 * 运营卡分销
 */

namespace App\Agent;

use App\Admin;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CardDistribution extends Model
{
    const UPDATED_AT = null;
    protected $guarded = ['id'];
    protected $table = 'agent_card_distribution';

    /**
     * 对应的库存
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stock()
    {
        return $this->belongsTo(CardStock::class, 'stock_id');
    }

    /**
     * 运营
     */
    public function getOperatorAttribute($by)
    {
        return Admin::find($by);
    }

    /**
     * 推广员
     */
    public function promoter()
    {
        return $this->belongsTo(User::class, 'to_promoter');
    }
}
