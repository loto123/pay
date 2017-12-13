<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransferUserRelation extends Model
{
    protected $table = 'transfer_user_relation';

    public function transfer() {
        return $this->belongsTo('App\Transfer', 'id', 'transfer_id');
    }

    public function user() {
        return $this->belongsTo('App\User', 'id', 'user_id');
    }
}
