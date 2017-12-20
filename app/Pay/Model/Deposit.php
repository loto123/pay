<?php
/**
 * 储值模型
 *
 * @transaction safe
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    /**
     * 支付状态
     */
    const STATE_UNPAID = 0;//未支付
    const STATE_COMPLETE = 1; //未支完成
    const STATE_FAIL = 2;//支付失败
    const STATE_PART_PAID = 3;//部分支付
    const STATE_API_ERR = 4;//接口不通
    protected $table = 'pay_deposit';

    protected $casts = [
        'state' => 'integer',
        'amount' => 'float',
    ];

    protected $guarded = ['id'];


    /**
     * 获取充值状态可读文本
     * @param $state int
     * @return string
     */
    public static function getStateText($state)
    {
        switch ($state) {
            case self::STATE_UNPAID:
                return '未完成';
            case self::STATE_COMPLETE:
                return '成功';
            case self::STATE_FAIL:
                return '失败';
            case self::STATE_PART_PAID:
                return '金额不足';
            default:
                return '异常';
        }
    }

    /**
     * 储值通道
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * 储值方式
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function method()
    {
        return $this->belongsTo(Deposit::class, 'method_id');
    }

    /**
     * 储值主容器
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function masterContainer()
    {
        return $this->belongsTo(MasterContainer::class, 'master_container');
    }
}