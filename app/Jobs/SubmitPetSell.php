<?php

namespace App\Jobs;

use App\Pay\Model\BillMatch;
use App\Pay\Model\SellBill;
use App\Pay\Model\WithdrawRetry;
use App\Pay\PayLogger;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SubmitPetSell implements ShouldQueue
{
    const RETRY_INTERVAL_MINUTES = 5;//重试间隔

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var $sellBill SellBill
     */
    private $sellBill;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SellBill $bill)
    {
        $this->sellBill = $bill;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->job) {
            $this->job->delete();//失败禁止重试
        }

        DB::beginTransaction();
        $commit = false;

        /**
         * @var $bill SellBill
         */
        $bill = SellBill::lockForUpdate()->find($this->sellBill->getKey());

        do {
            if (!$bill) {
                break;
            }

            if ($bill->deal_closed) {
                //已成交
                break;
            }

            if ($bill->locked) {
                //等待成交,稍后再试
                $this->retry();
                break;
            }

            //与系统交易商成交

            /**
             * @var $dealer User
             */
            $dealer = User::whereHas('roles', function ($query) {
                $query->where('name', '=', \App\Pet::DEALER_ROLE_NAME);
            })->inRandomOrder()->first();
            if (!$dealer) {
                $this->retry();
                PayLogger::withdraw()->emergency('没有交易商,系统无法回收宠物');
                break;
            }

            $match = new BillMatch([
                'expired_at' => date('Y-m-d H:i:s', time() + BillMatch::BILL_PAY_TIMEOUT * 60),
                'state' => BillMatch::STATE_DEAL_CLOSED,
                'by_dealer' => 1
            ]);

            $match->sellBill()->associate($bill);
            $match->createdBy()->associate($dealer);
            $bill->locked = 1;
            $bill->deal_closed = 1;
            if (!$bill->pet->transfer($dealer->getKey())) {
                $match->state = BillMatch::STATE_DEAL_FAIL;//没有收到宠物
                $bill->deal_closed = 0;//没有给宠物
                PayLogger::withdraw()->error('系统宠物交割失败', ['dealer' => $dealer->getKey(), 'sell_bill' => $bill->getKey()]);
            }

            if (!$bill->save() || !$match->save()) {
                break;
            }

            $commit = true;
        } while (false);

        $commit ? DB::commit() : DB::rollBack();

        //卖家转出了宠物给其提现
        if ($commit && $bill->deal_closed) {
            if (WithdrawRetry::isWithdrawFailed((new SubmitWithdrawRequest($bill->withdraw))->handle()->state)) {
                PayLogger::withdraw()->error('系统自动提现失败', ['sell_bill_id' => $bill->getKey()]);
            }
        }
    }

    //稍后重试
    private function retry()
    {
        self::dispatch($this->sellBill)->delay(Carbon::now()->addMinutes(self::RETRY_INTERVAL_MINUTES))->onQueue('withdraw');
    }
}
