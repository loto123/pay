<?php

namespace App\Pay\Model;

use App\Pet;
use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * 宠物卖单
 * Class SellBill
 * @package App\Pay\Model
 */
class SellBill extends Model
{
    const UPDATED_AT = false;
    protected $table = 'pay_sell_bill';
    protected $guarded = ['id'];

    /**
     * 下单用户
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function placeBy()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 对应提现
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function withdraw()
    {
        return $this->belongsTo(Withdraw::class);
    }

    /**
     * 所售宠物
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * 专属用户
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongToUser()
    {
        return $this->belongsTo(User::class, 'belong_to');
    }

}
