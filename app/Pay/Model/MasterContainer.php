<?php
/**
 * 主容器
 *
 * @transaction safe
 * Author: huangkaixuan
 * Date: 2017/12/5
 * Time: 10:55
 */

namespace App\Pay\Model;


use App\Jobs\SubmitWithdrawRequest;
use App\User;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class MasterContainer extends Container
{
    const UPDATED_AT = null;
    protected $table = 'pay_master_container';
    protected $casts = [
        'balance' => 'float',
        'frozen_balance' => 'float',
    ];

    /**
     * 发起新的结算
     *
     * @return SettleContainer
     */
    public function newSettlement()
    {
        $settlement = new SettleContainer();
        if ($this->settleContainers()->save($settlement)) {
            return $settlement;
        } else {
            return null;
        }
    }

    /**
     * 生成的结算容器
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function settleContainers()
    {
        return $this->hasMany(SettleContainer::class, 'master_container');
    }

    /**
     * 储值记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deposits()
    {
        return $this->hasMany(Deposit::class, 'master_container');
    }


    /**
     * 提现记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function withdraws()
    {
        return $this->hasMany(Withdraw::class, 'master_container');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'container_id');
    }


    /**
     * 发起充值
     *
     * @param $amount float
     * @param $byChannel Channel 支付通道
     * @param DepositMethod $byMethod WithdrawMethod 支付方式
     * @return array
     */
    public function initiateDeposit($amount, Channel $byChannel, DepositMethod $byMethod)
    {
        if ($byChannel->disabled) {
            throw new Exception('该支付通道不可用');
        }

        if ($byMethod->disabled) {
            throw new Exception('该支付方式不可用');
        }

        $order = new Deposit([
            'amount' => $amount,
            'state' => Deposit::STATE_UNPAID
        ]);

        $order->channel()->associate($byChannel);
        $order->method()->associate($byMethod);
        $order->masterContainer()->associate($this);

        DB::beginTransaction();
        $commit = false;
        $response = null;

        do {
            if (!$order->save()) {
                break;
            }

            $response = $byMethod->deposit($order);

            if ($response == null) {
                $order->state = Deposit::STATE_API_ERR;
                $order->save();
            }

            $commit = true;
        } while (false);

        $commit ? DB::commit() : DB::rollBack();
        if (!$response) {
            throw new Exception('当前支付方式异常');
        }
        return ['pay_info' => $response, 'deposit_id' => $order->getKey()];
    }


    /**
     * 发起提现
     * 隐式容器独占
     *
     * @param $amount
     * @param $receiver_info array
     * @param Channel $byChannel
     * @param WithdrawMethod $byMethod
     * @param $system_fee
     * @return array
     */
    public function initiateWithdraw($amount, array $receiver_info, Channel $byChannel, WithdrawMethod $byMethod, $system_fee)
    {
        if ($byMethod->disabled) {
            throw new Exception('该提现方式不可用');
        }

        //开始事务
        $commit = false;
        DB::beginTransaction();

        do {
            //扣除余额
            if (!$this->changeBalance(-$amount, 0)) {
                break;
            }

            //生成提现
            $withdraw = new Withdraw([
                'amount' => $amount,
                'system_fee' => $system_fee,
                'receiver_info' => $receiver_info
            ]);

            $withdraw->method()->associate($byMethod);
            $withdraw->channel()->associate($byChannel);
            $withdraw->masterContainer()->associate($this);
            //加入提现队列
            if ($withdraw->save()) {
                DB::commit();
                SubmitWithdrawRequest::dispatch($withdraw)->onQueue('withdraw');
                $commit = true;
            }
        } while (false);

        //结束事务
        if (!$commit) {
            DB::rollBack();
        }
        return ['success' => $commit, 'withdraw_id' => $commit ? $withdraw->getKey() : 0];
    }

}