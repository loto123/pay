<?php
/**
 *卡分配
 */

namespace App\Agent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CardAllocate extends Model
{
    const UPDATED_AT = false;
    protected $guarded = ['id'];

    /**
     * 配卡类型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cardType()
    {
        return $this->belongsTo(CardType::class, 'card_type');
    }

    /**
     * 配卡人
     */
    public function getAllocateByAttribute($by)
    {
        return DB::table('admin_users')->find($by);
    }

    /**
     * 被配卡人
     * @param $to
     */
    public function getAllocateToAttribute($to)
    {
        return DB::table('admin_users')->find($to);
    }
}
