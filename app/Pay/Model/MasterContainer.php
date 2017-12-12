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
use Illuminate\Support\Facades\DB;

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
        return $this->hasMany('App\Pay\Model\SettleContainer', 'master_container');
    }

    /**
     * 储值记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deposits()
    {
        return $this->hasMany('App\Pay\Model\Deposit', 'master_container');
    }


    /**
     * 提现记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function withdraws()
    {
        return $this->hasMany('App\Pay\Model\Withdraws', 'master_container');
    }


    /**
     * 发起充值
     *
     * @param $amount float
     * @param $byChannel Channel 支付通道
     * @param $byMethod PayMethod 支付方式
     * @return mixed 返回一个支付信息字符串或null
     */
    public function initiateDeposit($amount, Channel $byChannel, PayMethod $byMethod)
    {
        if ($byChannel->disabled) {
            $byChannel = $byChannel->spareChannel()->firstOrFail();
        }

        $order = new Deposit([
            'amount' => $amount,
            'channel' => $byChannel,
            'method' => $byMethod,
            'masterContainer' => $this,
            'state' => Deposit::STATE_UNPAID
        ]);

        DB::beginTransaction();
        $commit = false;
        $response = null;

        do {
            if (!$order->save()) {
                break;
            }

            $response = $byMethod->getImplInstance()->deposit($order->getKey(), $amount, $this->getKey(), $byChannel->getInterfaceConfigure(), $byChannel->getNotifyUrl());

            if ($response == null) {
                $order->state = Deposit::STATE_API_ERR;
                $order->save();
            }

            $commit = true;
        } while (false);

        $commit ? DB::commit() : DB::rollBack();
        return $response;
    }


    /**
     * 发起提现
     * 隐式容器独占
     *
     * @param $amount
     * @param $receiver_info
     * @param Channel $byChannel
     * @param PayMethod $byMethod
     * @param $system_fee
     * @return bool
     */
    public function initiateWithdraw($amount, $receiver_info, Channel $byChannel, PayMethod $byMethod, $system_fee)
    {
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
                'method' => $byMethod,
                'channel' => $byChannel,
                'masterContainer' => $this,
                'receiver_info' => $receiver_info
            ]);

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
        return $commit;
    }

}