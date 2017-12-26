<?php

namespace App\Pay\Model;

use App\Bank;
use App\UserCard;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Platform
 * @package App\Pay
 *
 * 支付平台
 *
 * @transaction safe
 * 例如微信，支付宝,汇付宝
 */
class Platform extends Model
{
    const SUPPORTED_BANKS_TABLE = 'pay_banks_support';
    public $timestamps = false;
    protected $table = 'pay_platform';

    /**
     * 平台的充值方式
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function depositMethods()
    {
        return $this->hasMany(DepositMethod::class);
    }

    /**
     * 平台的提现方式
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function withdrawMethods()
    {
        return $this->hasMany(WithdrawMethod::class);
    }

    /**
     * 平台关联的支付通道
     */
    public function channels()
    {
        $this->hasMany(Channel::class);
    }

    /**
     * 是否支持银行卡
     * @param UserCard $card
     * @return bool
     */
    public function isCardSupport(UserCard $card)
    {
        return $this->getBankCode($card) != null;
    }

    /**
     * 根据银行卡获取银行内部编号
     * @param UserCard $card
     * @return mixed
     */
    public function getBankCode(UserCard $card)
    {
        return $this->banksSupport()->where('bank_id', $card->bank_id)->value('inner_code');
        //return DB::table(self::SUPPORTED_BANKS_TABLE)->where([['bank_id' , $card->bank_id], ['platform_id' , $this->getKey()]])->value('inner_code');
    }

    /**
     * 取得支持的银行
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function banksSupport()
    {
        return $this->belongsToMany(Bank::class, Platform::SUPPORTED_BANKS_TABLE)->withPivot('inner_code');
    }
}
