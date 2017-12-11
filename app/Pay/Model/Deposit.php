<?php
/**
 * 储值模型
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    /**
     * 支付状态
     */
    const STATE_UNPAID = 0;
    const STATE_COMPLETE = 1; //未支付
    const STATE_FAIL = 2;//支付完成
    const STATE_PART_PAID = 3;//支付失败
    const STATE_API_ERR = 4;//部分支付
    protected $table = 'pay_deposit';//接口不通

    protected $casts = [
        'state' => 'integer',
        'amount' => 'float',
    ];

    /**
     * 储值通道
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel()
    {
        return $this->belongsTo('App\Pay\Model\Channel');
    }

    /**
     * 储值方式
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function method()
    {
        return $this->belongsTo('App\Pay\Model\PayMethod', 'method_id');
    }

    /**
     * 储值主容器
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function masterContainer()
    {
        return $this->belongsTo('App\Pay\Model\MasterContainer', 'master_container');
    }
}
