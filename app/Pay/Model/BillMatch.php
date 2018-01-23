<?php

namespace App\Pay\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;


/**
 * 订单撮合
 * Class BillMatch
 * @package App\Pay\Model\
 */
class BillMatch extends Model
{
    protected $table = 'pay_bill_match';
    protected $guarded = ['id'];

    /**
     * 配对卖单
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sellBill()
    {
        return $this->belongsTo(SellBill::class);
    }

    /**
     * 发起用户
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 关联充值
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deposit()
    {
        return $this->belongsTo(Deposit::class);
    }
}
