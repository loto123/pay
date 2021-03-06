<?php
/**
 *
 * @transaction safe
 * 提现方式
 *
 * 一个支付平台支持多种提现方式
 */

namespace App\Pay\Model;

use App\Jobs\WithdrawStatePoll;
use App\Pay\PayLogger;
use App\Pay\WithdrawInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WithdrawMethod extends Model
{
    public static $state_poll_delays = [30, 30, 60, 60, 900, 900, 900, 1800, 3600, 24 * 60 * 60];
    public $timestamps = false;
    protected $table = 'pay_withdraw_method';

    /**
     * 启动状态轮询
     * @param $withdraw_id
     * @param array $config
     */
    public static function pollState($withdraw_id, array $config)
    {
        WithdrawStatePoll::dispatch($withdraw_id, $config)->delay(Carbon::now()->addSeconds(self::$state_poll_delays[0]));
    }

    /**
     * 所属支付平台
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    /**
     * 提现目标
     */
    public function targetPlatform()
    {
        return $this->belongsTo(Platform::class, 'target_platform')->withDefault([
            'id' => 0,
            'name' => '银行卡',
        ]);
    }

    /**
     * 该方式的提现记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function withdraws()
    {
        return $this->hasMany(Withdraw::class, 'method_id');
    }

    /**
     * 执行提现
     * @param Withdraw $withdraw
     * @return WithdrawResult
     */
    public function withdraw(Withdraw $withdraw)
    {
        $result = new WithdrawResult($withdraw->state);

        //检查金额
        $actual_withdraw_amount = round($withdraw->amount - $withdraw->system_fee, 2);
        if ($actual_withdraw_amount <= 0) {
            $result->state = Withdraw::STATE_PROCESS_FAIL;
            $result->raw_response = '提现金额必须大于0';
            return $result;
        }

        /**
         * @var $impl WithdrawInterface
         */
        $impl = new $this->impl;

        //检查收款参数
        $diff = array_diff_key($impl->receiverInfoDescription(), $withdraw->receiver_info);
        if (!empty($diff)) {
            $result->state = Withdraw::STATE_PROCESS_FAIL;
            $result->state = '收款人信息缺失:' . implode(',', $diff);
            return $result;
        }

        return $impl->withdraw($withdraw->getKey(), $actual_withdraw_amount, $withdraw->receiver_info, array_merge((array)parse_ini_string($this->config), (array)parse_ini_string($withdraw->channel->config)), $this->getNotifyUrl($withdraw->channel));
    }

    /**
     * 获取通知地址
     */
    private function getNotifyUrl(Channel $channel)
    {
        return route('common_notify', ['notify_type' => 'withdraw', 'channel' => $channel->getKey(), 'method' => $this->getKey()]);
    }

    /**
     * 获取收款信息描述
     * @return array
     */
    public function getReceiverDescription()
    {
        return (new $this->impl)->receiverInfoDescription();
    }

    /**
     * 接收通知
     * @param Channel $channel
     * @return string
     */
    public function acceptNotify(Channel $channel)
    {
        PayLogger::withdraw()->info('提现通知', ['通道' => $channel->name, '请求' => request()]);
        //启动事务
        $commit = false;
        DB::beginTransaction();
        ob_start();

        do {
            /**
             * @var $result Withdraw
             */
            $result = (new $this->impl)->acceptNotify(array_merge((array)parse_ini_string($this->config), (array)parse_ini_string($channel->config)));

            if (!$result) {
                break;
            }

            /**
             * 更新相关状态
             */
            if (!$result->stateCallback(request()->query(), file_get_contents('php://input'))) {
                break;
            }

            $commit = true;
        } while (false);

        //结束事务
        if ($commit) {
            DB::commit();
            return ob_get_clean();
        } else {
            DB::rollBack();
            ob_end_clean();
            return 'error';
        }
    }

}
