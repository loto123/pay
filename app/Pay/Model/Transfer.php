<?php
/**
 * 余额转账
 *
 * @transaction safe
 */

namespace App\Pay\Model;

use App\Pay\PayLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Transfer extends Model
{
    /**
     * 转账状态
     */
    const STATE_COMPLETE = 1;//完成
    const STATE_CHARGEBACK = 2;//被撤回
    /**
     * 撤回结果
     */
    const CHARGE_BACK_SUCCESS = 1;//成功
    const CHARGE_BACK_OUT_OF_BALANCE = 2;//余额不足
    const CHARGE_BACK_ERR = 3;//错误
    protected $table = 'pay_transfer';

    protected $casts = [
        'fee' => 'float',
        'amount' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'state' => 'integer',
        'from_frozen' => 'boolean',
        'to_frozen' => 'boolean'
    ];

    protected $guarded = ['id'];

    /**
     * 转账分润
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function profitShares()
    {
        return $this->hasMany(ProfitShare::class);
    }

    /**
     * 撤回
     *
     * @return int 1撤回成功,2金额不足以撤回,3其它失败
     */
    public function chargeback()
    {
        $result = self::CHARGE_BACK_ERR;
        $error_code = 0;
        $error_amount = null;
        //开始事务
        $commit = false;
        DB::beginTransaction();
        do {
            //状态检查
            $transfer = Transfer::where('state', self::STATE_COMPLETE)->with(['profitShares.receiveContainer', 'containerFrom', 'containerTo'])->lockForUpdate()->find($this->getKey());
            if (!$transfer) {
                $error_code = 1;
                break;
            }

            //撤回分润
            $profit_share_sum = 0;
            foreach ($transfer->profitShares as $profitShare) {
                if (!$profitShare->receiveContainer->changeBalance($profitShare->is_frozen ? 0 : -$profitShare->amount, $profitShare->is_frozen ? -$profitShare->amount : 0)) {
                    $result = self::CHARGE_BACK_OUT_OF_BALANCE;
                    $error_code = 2;
                    $error_amount = $profitShare->amount;
                    break 2;
                }
                $profit_share_sum += $profitShare->amount;
            }

            //撤回实收资金
            $actual_received = bcsub(bcsub($transfer->amount, $transfer->fee, 2), $profit_share_sum, 2);
            if (!$transfer->containerTo->changeBalance($transfer->to_frozen ? 0 : -$actual_received, $transfer->to_frozen ? -$actual_received : 0)) {
                $result = self::CHARGE_BACK_OUT_OF_BALANCE;
                $error_code = 3;
                $error_amount = $actual_received;
                break;
            }

            //资金打回
            if (!$transfer->containerFrom->changeBalance($transfer->from_frozen ? 0 : $transfer->amount, $transfer->from_frozen ? $transfer->amount : 0)) {
                $error_code = 4;
                $error_amount = $transfer->amount;
                break;
            }

            //变更状态
            $transfer->state = self::STATE_CHARGEBACK;
            if (!$transfer->save()) {
                $error_code = 5;
                break;
            }

            $commit = true;
            $result = self::CHARGE_BACK_SUCCESS;

        } while (false);

        //结束事务
        $commit ? DB::commit() : DB::rollBack();
        if (!$commit) {
            PayLogger::common()->error('转账撤回失败', ['code' => $error_code, 'amount' => $error_amount]);
        }
        return $result;
    }

    /**
     * 转入容器
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function containerTo()
    {
        return $this->morphTo(null, 'to_type', 'container_to');
    }

    /**
     * 转出容器
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function containerFrom()
    {
        return $this->morphTo(null, 'from_type', 'container_from');
    }
}
