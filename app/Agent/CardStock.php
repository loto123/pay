<?php

/**
 * 运营的卡库存
 */

namespace App\Agent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CardStock extends Model
{
    protected $table = 'agent_card_stock';
    public $timestamps = false;
    protected $guarded = ['id'];

    /**
     * 配卡人
     * @param $id
     * @return mixed
     */
    public function getAllocateByAttribute($id)
    {
        return DB::table('admin_users')->find($id);
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

    /**
     * 卡信息
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}
