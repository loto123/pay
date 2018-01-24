<?php

namespace App\Pay\Model;

use App\Pay\PayLogger;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


/**
 * 订单撮合
 * Class BillMatch
 * @package App\Pay\Model\
 */
class BillMatch extends Model
{
    const BILL_PAY_TIMEOUT = 15; //支付超时,分钟

    /**
     * 撮合状态
     */
    const STATE_WAIT = 0; //待成交
    const STATE_FAIL = 1; //付款失败
    const STATE_TIMEOUT_PAY = 2;//超时付款
    const STATE_DEAL_CLOSED = 3; //已成交
    const STATE_CHARGE_BACK = 4; //已经退款
    const STATE_EXPIRED = 5;//已经超时
    const STATE_DEAL_FAIL = 6;//交割失败

    protected $table = 'pay_bill_match';
    protected $guarded = ['id'];

    /**
     * 交易过期,每分钟执行一次
     */
    public static function expire()
    {
        $affected = 0;
        $exception = '无';
        try {
            //将所有待成交且超时的撮合状态改为已超时。并将其对手单解锁。
            $affected = DB::table('pay_bill_match')->join('pay_sell_bill', function ($join) {
                $join->on('pay_bill_match.sell_bill_id', '=', 'pay_sell_bill.id')->where([
                    ['pay_bill_match.state', '=', self::STATE_WAIT],
                    ['expired_at', '<', date('Y-m-d H:i:s')]
                ]);
            })->update(['pay_bill_match.state' => self::STATE_EXPIRED, 'pay_sell_bill.locked' => 0]);
        } catch (\Exception $e) {
            $exception = $e;
        }
        PayLogger::common()->info('宠物订单过期任务', ['过期订单数' => $affected, '异常' => $exception]);
    }

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
