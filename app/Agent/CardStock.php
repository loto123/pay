<?php

/**
 * 运营的卡库存
 */

namespace App\Agent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CardStock extends Model
{
    public $timestamps = false;
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
     * 所属运营
     * @param $id
     * @return mixed
     */
    public function getOperatorAttribute($id)
    {
        return DB::table('admin_users')->find($id);
    }
}
