<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProxyWithdraw extends Model
{
    protected static $skip32_id = 'e2b311831c6ce9b5ab8a';
    protected $table = 'proxy_withdraw';

    use Skip32Trait;

    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
