<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipRecord extends Model
{
    protected $table = 'tip_record';
    //可提现已到账店铺收入
    const USEABLE_STATUS = 1;
    //未到账冻结状态的店铺收入
    const FROZEN_STATUS = 0;

    //记录所属交易
    public function transfer()
    {
        return $this->belongsTo('App\Transfer', 'transfer_id', 'id');
    }

    //记录所属交易
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
