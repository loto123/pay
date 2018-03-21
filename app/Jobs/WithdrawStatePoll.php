<?php

namespace App\Jobs;

use App\Pay\Model\Withdraw;
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
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($withdraw_id)
    {
        $this->withdraw = Withdraw::find($withdraw_id);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * @var $impl WithdrawInterface
         */
        $impl = new $this->withdraw->method->impl;
        //$impl->queryState($this->withdraw, )

    }
}
