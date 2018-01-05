<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShopFund extends Model
{
    //
    const TYPE_TRANAFER = 0;

    const TYPE_TRANAFER_MEMBER = 1;

    const TYPE_TRANAFER_IN = 2;

    const MODE_IN = 0;

    const MODE_OUT = 1;

    const STATUS_PENDING = 0;

    const STATUS_SUCCESS = 1;

    const STATUS_FAIL = 2;

    use Skip32Trait;

    protected static $skip32_id = '0123456789abcdef0123';

    public function shop() {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }
}
