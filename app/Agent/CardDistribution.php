<?php
/**
 * 运营卡分销
 */

namespace App\Agent;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CardDistribution extends Model
{
    const UPDATED_AT = false;
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
        return DB::table('admin_users')->find($by);
    }

    /**
     * 推广员
     */
    public function promoter()
    {
        return $this->belongsTo(User::class, 'to_promoter');
    }
}
