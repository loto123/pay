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
     * 平台的支付方式
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function methods()
    {
        return $this->hasMany('App\Pay\Model\PayMethod');
    }

    /**
     * 平台关联的支付通道
     */
    public function channels()
    {
        $this->hasMany('App\Pay\Model\Channel');
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
