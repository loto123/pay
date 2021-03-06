<?php
/**
 * 余额容器基类
 * @transaction safe
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class Container extends Model
{
    /**
     * 汇入转账
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function transfersIn()
    {
        return $this->morphMany(Transfer::class, 'containerTo', 'to_type', 'container_to');
    }


    /**
     * 汇出转账
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function transfersOut()
    {
        return $this->morphMany(Transfer::class, 'containerFrom', 'from_type', 'container_from');
    }


    /**
     * 收到分润
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function profitShares()
    {
        return $this->morphMany(ProfitShare::class, 'receiveContainer', 'container_type', 'receive_container');
    }

    /**
     * 冻结/解冻记录
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function fronzens()
    {
        return $this->morphMany(Freeze::class, 'container');
    }


    /**
     * 冻结资金
     *
     * @param $amount float
     * @return bool
     */
    public function freeze($amount)
    {
        if ($amount <= 0) {
            return false;
        }

        DB::beginTransaction();
        $commit = false;

        if ($this->changeBalance(-$amount, $amount)) {
            $freeze = new Freeze([
                'amount' => $amount,
                'operation' => Freeze::OPERATION_FREEZE,
                'memo' => '',
            ]);
            $freeze->container()->associate($this);
            if ($freeze->save()) {
                $commit = true;
            }
        }

        $commit ? DB::commit() : DB::rollBack();
        return $commit;
    }

    /**
     * 变更余额
     * 隐式独占容器
     *
     * @param $balance float 可用余额,正数增加,负数减少
     * @param $frozen_balance float 冻结余额,正数增加,负数减少
     * @return bool
     */
    public function changeBalance($balance, $frozen_balance)
    {
        if (is_numeric($balance) && is_numeric($frozen_balance) && ($balance != 0 || $frozen_balance != 0)) {
            $balance_opt = $balance < 0 ? '-' : '+';
            $balance = abs($balance);
            $frozen_opt = $frozen_balance < 0 ? '-' : '+';
            $frozen_balance = abs($frozen_balance);

            $bindings = [$balance, $frozen_balance, $this->getKey()];
            $sql = "UPDATE `{$this->table}` SET `balance` = `balance` $balance_opt ?,
                `frozen_balance` = `frozen_balance` $frozen_opt ? WHERE `{$this->getKeyName()}` = ?";

            if (is_a($this, SettleContainer::class)) {
                $sql .= ' AND `state` = ?';
                $bindings[] = SettleContainer::STATE_NORMAL;
            }

            if ($balance_opt === '-') {
                $sql .= ' AND `balance` >= ?';
                $bindings[] = $balance;
            }

            if ($frozen_opt === '-') {
                $sql .= ' AND `frozen_balance` >= ?';
                $bindings[] = $frozen_balance;
            }

            return DB::update($sql, $bindings) > 0;
        }
        return false;

    }

    /**
     * 解冻资金
     *
     * @param $amount float
     * @return bool
     */
    public function unfreeze($amount)
    {
        if ($amount <= 0) {
            return false;
        }

        DB::beginTransaction();
        $commit = false;
        if ($this->changeBalance($amount, -$amount)) {
            $freeze = new Freeze([
                'amount' => $amount,
                'operation' => Freeze::OPERATION_UNFREEZE,
                'memo' => '',
            ]);
            $freeze->container()->associate($this);
            if ($freeze->save()) {
                $commit = true;
            }
        }

        $commit ? DB::commit() : DB::rollBack();
        return $commit;
    }

    /**
     * 容器转账
     *
     * 独占所有容器
     *
     * 约束：
     * 1.转入与转出容器状态必须正常
     * 2.手续费 + 分润 < 金额
     *
     * @param Container $to_container 目标容器
     * @param $amount float 金额
     * @param $fee float 系统手续费
     * @param $from_frozen bool 使用冻结or可用余额
     * @param $to_frozen bool 汇入冻结or可用余额
     * @param array ProfitShare $profit_shares 分润列表
     *
     * @return Transfer 失败返回false
     */
    public function transfer(Container $to_container, $amount, $fee, $from_frozen, $to_frozen, array $profit_shares = [])
    {
        //开始事务
        $commit = false;
        $transfer = null;
        DB::beginTransaction();

        do {
            /**
             * 检查分润
             */
            $share_sum = 0;//分润总额
            if ($profit_shares != []) {
                if (array_filter($profit_shares, function ($profit_share) use (&$share_sum) {
                        if ($profit_share instanceof ProfitShare) {
                            $share_sum = bcadd($share_sum, $profit_share->amount, 2);
                        } else {
                            return false;
                        }
                        return true;

                    }) !== $profit_shares
                ) {
                    break;
                }
            }


            /**
             * 检查金额
             */

            $actual_received = bcsub(bcsub($amount, $fee, 2), $share_sum, 2); //实收金额
            if ($actual_received <= 0) {
                break;
            }

            //汇出
            if (!$this->changeBalance($from_frozen ? 0 : -$amount, $from_frozen ? -$amount : 0)) {
                break;//余额不足
            }

            //汇入
            if (!$to_container->changeBalance($to_frozen ? 0 : $actual_received, $to_frozen ? $actual_received : 0)) {
                break;
            }

            //分润
            foreach ($profit_shares as $profit_share) {
                if (!$profit_share->receiveContainer->changeBalance(
                    $profit_share->is_frozen ? 0 : $profit_share->amount,
                    $profit_share->is_frozen ? $profit_share->amount : 0
                )
                ) {
                    break 2;//分润失败
                }
            }

            //生成转账
            $transfer = new Transfer([
                'fee' => $fee,
                'from_frozen' => $from_frozen,
                'to_frozen' => $to_frozen,
                'amount' => $amount,
                'state' => Transfer::STATE_COMPLETE,
            ]);
            $transfer->containerFrom()->associate($this);
            $transfer->containerTo()->associate($to_container);

            if (!$transfer->save()) {
                break;
            }
            $transfer->profitShares()->saveMany($profit_shares);

            $commit = true;

        } while (false);

        //结束事务
        if ($commit) {
            DB::commit();
            return $transfer;
        } else {
            DB::rollBack();
            return false;
        }
    }
}
