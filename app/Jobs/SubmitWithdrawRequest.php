<?php

namespace App\Jobs;

use App\Pay\Model\Withdraw;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * 提现队列任务
 * Class SubmitWithdrawRequest
 * @package App\Jobs
 */
class SubmitWithdrawRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 禁止重试
     * @var int
     */
    public $tries = 0;
    /**
     * @var Withdraw
     */
    private $withdraw;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Withdraw $withdraw)
    {
        $this->withdraw = $withdraw;
    }

    /**
     * 处理提现
     *
     * @return void
     */
    public function handle()
    {
        $withdraw = $this->withdraw;
        if ($withdraw->state == Withdraw::STATE_QUEUED) {
            $state = Withdraw::STATE_QUEUED;
            $actual_withdraw_amount = round($withdraw->amount - $withdraw->system_fee, 2);
            $prev_except = null;
            $result = [];
            try {
                $result = $withdraw->method()->getInterface()->withdraw($withdraw->getKey(), $actual_withdraw_amount, $withdraw->receiver_info, $withdraw->channel()->getInterfaceConfigure());
                if (is_array($result) && array_key_exists('state', $result) && array_key_exists('raw_respon', $result)) {
                    $state = $result['state'];
                    //通道交易号
                    if (isset($result['out_batch_no'])) {
                        $withdraw->out_batch_no = $result['out_batch_no'];
                    }

                    //通道手续费
                    if (isset($result['fee'])) {
                        $withdraw->channel_fee = $result['fee'];
                    }
                }
                $withdraw->sate = $state;

            } catch (\Exception $e) {
                $result['raw_respon'] = 'Uncaught queue execution exception';
                $state = Withdraw::STATE_SEND_FAIL;
                $prev_except = $e;
            }

            $withdraw->save();

            if ($state == Withdraw::STATE_SEND_FAIL || $state == Withdraw::STATE_PROCESS_FAIL) {
                throw new WithdrawException($result['raw_respon'], $state, $prev_except);
            }
        }
    }


    /**
     * 处理提现异常
     * @param WithdrawException $e
     */
    public function fail(WithdrawException $e)
    {
        $this->withdraw->exceptions()->save(new \App\Pay\Model\WithdrawException([
            'message' => $e->getMessage(),
            'state' => $e->getCode(),
            'exception' => $e->getPrevious() ? $e->getPrevious()->getMessage() : ''
        ]));
    }
}

class WithdrawException extends \Exception
{
}
