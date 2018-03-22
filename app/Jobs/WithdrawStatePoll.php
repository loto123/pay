<?php
/**
 * 提现状态轮询任务
 */
namespace App\Jobs;

use App\Pay\Model\Withdraw;
use App\Pay\Model\WithdrawMethod;
use App\Pay\PayLogger;
use App\Pay\WithdrawInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WithdrawStatePoll implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Withdraw
     */
    private $withdraw;

    /**
     * @var array
     */
    private $config;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($withdraw_id, array $config)
    {
        $this->withdraw = Withdraw::find($withdraw_id);
        $this->config = $config;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->withdraw->state === Withdraw::STATE_SUBMIT) {
            /**
             * @var $impl WithdrawInterface
             */
            $impl = new $this->withdraw->method->impl;
            $retry = false;
            try {
                $result = $impl->queryState($this->withdraw, $this->config);
                $this->withdraw->stateCallback('', $result->raw_response);
                if ($result->state === Withdraw::STATE_SUBMIT) {
                    $retry = true;
                }
            } catch (\Exception $e) {
                PayLogger::withdraw()->error('提现状态轮询异常', ['msg' => $e->getMessage(), 'code' => $e->getCode()]);
                $retry = true;
            }

            if ($retry) {
                //继续轮询
                $this->release(WithdrawMethod::$state_poll_delays[min($this->attempts(), count(WithdrawMethod::$state_poll_delays) - 1)]);
            }
        }

    }
}
