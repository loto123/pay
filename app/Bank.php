<?php

namespace App;

use App\Pay\Model\Platform;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'banks';

    /**
     * 支持的支付平台
     */
    public function payPlatformSupport()
    {
        return $this->belongsToMany(Platform::class, 'pay_banks_support');
    }
}
