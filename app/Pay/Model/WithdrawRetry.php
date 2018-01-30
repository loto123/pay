<?php
/**
 * 尝试手动提现
 * Author: huangkaixuan
 * Date: 2017/12/23
 * Time: 19:45
 */

namespace App\Pay\Model;


use App\Jobs\SubmitWithdrawRequest;
use Illuminate\Support\Facades\DB;

class WithdrawRetry extends PayRetry
{
    public static $abnormal_states = [Withdraw::STATE_SEND_FAIL, Withdraw::STATE_PROCESS_FAIL];
    protected static $type = 'withdraw';

    /**
     * 提现是否失败
     * @param $state
     * @return bool
     */
    public static function isWithdrawFailed($state)
    {
        return in_array($state, self::$abnormal_states);
    }

    function reDo()
    {
        // 手动提现
        $commit = false;
        DB::beginTransaction();
        $result = new WithdrawResult(Withdraw::STATE_SEND_FAIL);
        do {
            $withdraw = Withdraw::where('id', $this->id)->whereIn('state', self::$abnormal_states)->lockForUpdate()->first();
            if (!$withdraw) {
                $result->raw_response = '该笔提现不能重试';
                break;
            }

            $request = new SubmitWithdrawRequest($withdraw);
            $result = $request->handle();
            $commit = true;
        } while (false);

        $commit ? DB::commit() : DB::rollBack();
        $success = !in_array($result->state, self::$abnormal_states);
        if ($success) {
            //卖单状态变为成交
            SellBill::where('withdraw_id', $this->id)->update(['deal_closed' => 1]);
        }
        return $this->response($success, $success ? Withdraw::getStateText($result->state) : "错误:" . $result->raw_response);
    }
}