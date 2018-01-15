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

    const SALE = 0; //待售
    const SOLD = 1; //已售
//    /**
//     * 配卡人
//     * @param $id
//     * @return mixed
//     */
//    public function getAllocateByAttribute($id)
//    {
//        return DB::table('admin_users')->find($id);
//    }
//
//    /**
//     * 所属运营
//     * @param $id
//     * @return mixed
//     */
//    public function getOperatorAttribute($id)
//    {
//        return DB::table('admin_users')->find($id);
//    }


    /**
     * 卡信息
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card()
    {
        return $this->belongsTo(Card::class);
    }

     //制卡人
    public function allocate_bys()
    {
        return $this->belongsTo('App\Admin','allocate_by','id');
    }

    //运营
    public function operators()
    {
        return $this->belongsTo('App\Admin','operator','id');
    }

    //推广员
    public function promoters()
    {
        return $this->belongsToMany('App\User',(new CardDistribution())->getTable(),'stock_id','to_promoter');
    }

    public function distributions()
    {
        return $this->hasOne('App\Agent\CardDistribution','stock_id','id');
    }
}
