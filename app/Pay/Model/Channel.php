<?php
/**
 * 支付通道
 *
 * @transaction safe
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    public $timestamps = false;
    protected $table = 'pay_channel';
    protected $casts = [
        'limit_amount' => 'float',
        'used_amount' => 'float',
        'disabled' => 'boolean'
    ];


    /**
     * 获取备用通道
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function spareChannel()
    {
        return $this->belongsTo(self::class, 'spare_channel_id');
    }


    /**
     * 通道所属支付平台
     */
    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    /**
     * 关联的运营主体
     */
    public function businessEntity()
    {
        return $this->belongsTo(BusinessEntity::class, 'entity_id');
    }

    /**
     * 通道储值记录
     */
    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    /**
     * 通道提现记录
     */
    public function withdraws()
    {
        return $this->hasMany(Withdraw::class);
    }
}
