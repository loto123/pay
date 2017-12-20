<?php

namespace App\Jobs;

use App\Pay\Model\Withdraw;
use App\Pay\Model\WithdrawException;
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
        $this->job->delete();//失败禁止重试

        $withdraw = $this->withdraw;
        $prev_except = null;
        $result = [];

        try {
            $result = $withdraw->method->withdraw($withdraw);
            if (is_array($result) && array_key_exists('state', $result) && array_key_exists('raw_response', $result)) {
                $withdraw->state = $result['state'];
                //通道交易号
                if (isset($result['out_batch_no'])) {
                    $withdraw->out_batch_no = $result['out_batch_no'];
                }

                //通道手续费
                if (isset($result['fee'])) {
                    $withdraw->channel_fee = $result['fee'];
                }
            }

        } catch (\Exception $e) {
            $result['raw_response'] = 'App exception';
            $withdraw->state = Withdraw::STATE_SEND_FAIL;
            $prev_except = $e;
        }

        $withdraw->save();

        if ($withdraw->state == Withdraw::STATE_SEND_FAIL || $withdraw->state == Withdraw::STATE_PROCESS_FAIL) {
            $this->withdraw->exceptions()->save(new WithdrawException([
                'message' => (string)$result['raw_response'],
                'state' => $withdraw->state,
                'exception' => $prev_except ? $prev_except->getMessage() : ''
            ]));
        }

    }
}

