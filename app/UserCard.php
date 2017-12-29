<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserCard extends Model
{
    //

    const IDENTIFY_TYPE = 1; //实名认证
    const AUTH_TYPE = 2; //银行卡鉴权
    public function bank() {
        return $this->hasOne(Bank::class, 'id', 'bank_id');
    }
}
