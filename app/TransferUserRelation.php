<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransferUserRelation extends Model
{
    protected $table = 'transfer_user_relation';

    public function transfer() {
        return $this->belongsTo('App\Transfer', 'transfer_id', 'id');
    }

    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
