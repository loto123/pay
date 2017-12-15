<?php
/**
 *
 * @transaction safe
 * 支付方式
 *
 * 一个支付平台支持多种支付方式
 */

namespace App\Pay\Model;

use App\Pay\CashInterface;
use Illuminate\Database\Eloquent\Model;

class PayMethod extends Model
{
    public $timestamps = false;
    protected $table = 'pay_method';
    /**
     * @var $interface CashInterface
     */
    private $interface;

    /**
     * 获取支付返回地址
     * @return string
     */
    public function getReturnUrl()
    {
        return route('pay_result', ['method' => $this->getKey()]);
    }

    /**
     * 所属支付平台
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    /**
     * 该方式的储值记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deposits()
    {
        return $this->hasMany(Deposit::class, 'method_id');
    }

    /**
     * 该方式的提现记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function withdraws()
    {
        return $this->hasMany(Withdraw::class, 'method_id');
    }


    /**
     * 获取支付方式实现
     * @return CashInterface
     */
    public function getImplInstance()
    {
        if (!$this->interface) {
            $this->interface = new $this->impl;
        }

        return $this->interface;
    }

}
