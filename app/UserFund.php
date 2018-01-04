<?php

namespace App;

use App\Pay\Model\Deposit;
use App\Pay\Model\Withdraw;
use Illuminate\Database\Eloquent\Model;

class UserFund extends Model
{
    //
    const TYPE_CHARGE = 0;

    const TYPE_WITHDRAW = 1;

    const TYPE_TRANSFER = 4;

    const MODE_IN = 0;

    const MODE_OUT = 1;

    const STATUS_PENDING = 0;

    const STATUS_SUCCESS = 1;

    const STATUS_FAIL = 2;

    use Skip32Trait;

    protected static $skip32_id = '0123456789abcdef0123';

    public function charge_order() {
        return $this->hasOne(Deposit::class, 'id', 'no');
    }

    public function withdraw_order() {
        return $this->hasOne(Withdraw::class, 'id', 'no');
    }
}
