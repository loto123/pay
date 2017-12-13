<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransferRecord extends Model
{
    protected $table = 'transfer_record';

    //记录所属交易
    public function transfer() {
        return $this->belongsTo('App\Transfer', 'id', 'transfer_id');
    }

    //用户
    public function user()
    {
        return $this->belongsTo('App\User', 'id', 'user_id');
    }

    //茶水费
    public function tip() {
        return $this->hasOne('App\TipRecord');
    }
}
