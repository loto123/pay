<?php

namespace App\Pay\Model;

use App\Pay\PlatformInterface;
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
    public $timestamps = false;
    protected $table = 'pay_platform';
    /**
     * @var $interface PlatformInterface
     */
    private $interface;

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
     * 获取平台接口实现
     *
     * @return PlatformInterface
     */
    public function getImplInstance()
    {
        if (!$this->interface) {
            $this->interface = new $this->impl;
        }

        return $this->interface;
    }
}
