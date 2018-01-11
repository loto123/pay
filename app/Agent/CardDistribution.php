<?php
/**
 * 运营卡分销
 */

namespace App\Agent;

use App\User;
use Illuminate\Database\Eloquent\Model;

class CardDistribution extends Model
{
    const UPDATED_AT = false;
    protected $guarded = ['id'];

    /**
     * 卡类型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cardType()
    {
        return $this->belongsTo(CardType::class, 'card_type');
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
