<?php
/**
 * 余额转账
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Transfer extends Model
{
    /**
     * 转账状态
     */
    const STATE_COMPLETE = 1;
    const STATE_CHARGEBACK = 2;//完成
    /**
     * 撤回结果
     */
    const CHARGE_BACK_SUCCESS = 1;//已撤回
    const CHARGE_BACK_OUT_OF_BALANCE = 2;//成功
    const CHARGE_BACK_ERR = 3;//余额不足
    protected $table = 'pay_transfer';//错误

    /**
     * 转账分润
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function profitShares()
    {
        return $this->hasMany('App\Pay\Model\ProfitShare');
    }

    /**
     * 撤回
     *
     * @return int 1撤回成功,2金额不足以撤回,3其它失败
     */
    public function chargeback()
    {
        $result = self::CHARGE_BACK_ERR;

        //开始事务
        $commit = false;
        DB::beginTransaction();
        do {
            //撤回分润
            $transfer = Transfer::with('profitShares.receiveContainer')->find($this->getKey());
            $profit_share_sum = 0;
            foreach ($transfer->profitShares as $profitShare) {
                if (!$profitShare->receiveContainer->changeBalance($profitShare->is_frozen ? 0 : -$profitShare->amount, $profitShare->is_frozen ? -$profitShare->amount : 0)) {
                    $result = self::CHARGE_BACK_OUT_OF_BALANCE;
                    break 2;
                }
                $profit_share_sum += $profitShare->amount;
            }

            //撤回实收资金
            $actual_received = $transfer->amount - $transfer->fee - $profit_share_sum;
            if (!$transfer->containerTo()->changeBalance($transfer->to_frozen ? 0 : -$actual_received, $transfer->to_frozen ? -$actual_received : 0)) {
                $result = self::CHARGE_BACK_OUT_OF_BALANCE;
                break;
            }

            //资金打回
            if (!$transfer->containerFrom()->changeBalance($transfer->from_frozen ? 0 : $transfer->amount, $transfer->from_frozen ? $transfer->amount : 0)) {
                break;
            }

            //变更状态
            $transfer->state = self::STATE_CHARGEBACK;
            if (!$transfer->save()) {
                break;
            }

            $commit = true;
            $result = self::CHARGE_BACK_SUCCESS;

        } while (false);

        //结束事务
        $commit ? DB::commit() : DB::rollBack();
        return $result;
    }

    /**
     * 转入容器
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function containerTo()
    {
        return $this->belongsTo('App\Pay\Model\Container', 'container_to');
    }

    /**
     * 转出容器
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function containerFrom()
    {
        return $this->belongsTo('App\Pay\Model\Container', 'container_from');
    }
}
