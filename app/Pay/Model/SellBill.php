<?php

namespace App\Pay\Model;

use App\Pet;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
     * 查询在出售的卖单
     * @return mixed
     */
    public static function onSale()
    {
        $where = [
            ['deal_closed', 0],//未成交
            ['locked', 0],//未锁定
        ];
        return self::where($where)->where(function ($query) {
            //专属或不定向卖单
            $query->whereNull('belong_to')->orWhere('belong_to', Auth::id());
        })->where(function ($query) {
            //没有有效期或在有效期内
            $query->whereNull('valid_time')->orWhere('valid_time', '>', date('Y-m-d H:i:s'));
        });
    }

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
