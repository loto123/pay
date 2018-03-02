<?php
/**
 * 充值结果
 * Author: huangkaixuan
 * Date: 2017/12/20
 * Time: 17:16
 */

namespace App\Pay\Model;


class DepositResult
{
    public $state;
    public $amount;
    public $out_batch_no;
    public $id;

    /**
     * DepositResult constructor.
     * @param int $state 支付状态,Deposit::STATE_*
     * @param int $amount 充值金额,默认0
     * @param null $out_batch_no 可选,外部交易号
     */
    public function __construct($state = Deposit::STATE_PAY_FAIL, $id = null, $amount = 0, $out_batch_no = null)
    {
        $this->state = $state;
        $this->amount = $amount;
        $this->out_batch_no = $out_batch_no;
        $this->id = $id;
    }
}