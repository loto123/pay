<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipRecord extends Model
{
    protected $table = 'tip_record';

    //记录所属交易
    public function transfer() {
        return $this->belongsTo('App\Transfer', 'id', 'transfer_id');
    }

    //记录所属交易
    public function user() {
        return $this->belongsTo('App\User', 'id', 'user_id');
    }
}
