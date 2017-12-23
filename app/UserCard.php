<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserCard extends Model
{
    //

    public function bank() {
        return $this->hasOne(Bank::class, 'id', 'bank_id');
    }
}
