<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProxyWithdraw extends Model
{
    protected $table = 'proxy_withdraw';

    protected static $skip32_id = '0123456789abcdef0123';

    use Skip32Trait;

    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
