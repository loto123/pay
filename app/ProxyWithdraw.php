<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProxyWithdraw extends Model
{
    protected $table = 'proxy_withdraw';

    use Skip32Trait;

    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
