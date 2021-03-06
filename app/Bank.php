<?php

namespace App;

use App\Pay\Model\Platform;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'banks';

    const LOGO_PRE = '/storage/';
    /**
     * 支持的支付平台
     */
    public function payPlatformSupport()
    {
        return $this->belongsToMany(Platform::class, 'pay_banks_support');
    }

    public function getLogoAttribute($value)
    {
        return $value ? url(self::LOGO_PRE.$value) : asset("images/bank.jpg");
    }
}
