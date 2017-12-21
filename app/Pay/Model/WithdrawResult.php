<?php
/**
 * 提现结果
 * Author: huangkaixuan
 * Date: 2017/12/20
 * Time: 17:16
 */

namespace App\Pay\Model;


class WithdrawResult
{
    public $state;
    public $raw_response;
    public $out_batch_no;
    public $fee;

    /**
     * WithdrawResult constructor.
     * @param int $state 提现状态 Withdraw::STATE_*
     * @param null $raw_response 接口返回值
     * @param null $out_batch_num 可选,外部交易号
     * @param null $fee 可选,通道手续费
     */
    public function __construct($state = Withdraw::STATE_SEND_FAIL, $raw_response = null, $out_batch_num = null, $fee = null)
    {
        $this->state = $state;
        $this->raw_response = $raw_response;
        $this->out_batch_no = $out_batch_num;
        $this->fee = $fee;
    }
}