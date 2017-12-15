<?php
/**
 *
 * @transaction safe
 * 提现模型
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

class Withdraw extends Model
{
    /**
     * 提现状态
     */
    const STATE_QUEUED = 1; //已进入提现队列
    const STATE_SUBMIT = 2; //已提交平台处理
    const STATE_COMPLETE = 3;//处理完成
    const STATE_PROCESS_FAIL = 4;//处理失败
    const STATE_SEND_FAIL = 5;//提交失败

    protected $table = 'pay_withdraw';
    protected $casts = [
        'amount' => 'float',
        'system_fee' => 'float',
        'channel_fee' => 'float',
        'create_at' => 'datetime',
        'update_at' => 'datetime',
        'state' => 'integer'
    ];

    public function getReceiverInfoAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setReceiverInfoAttribute(array $value)
    {
        $this->attributes['receiver_info'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 提现方式
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function method()
    {
        return $this->belongsTo(PayMethod::class, 'method_id');
    }

    /**
     * 提现通道
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * 主容器
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function masterContainer()
    {
        return $this->belongsTo(MasterContainer::class, 'master_container');
    }

    /**
     * 提现异常
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function exceptions()
    {
        return $this->hasMany(WithdrawException::class);
    }
}
