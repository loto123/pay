<?php
/**
 *
 * @transaction safe
 * 充值方式
 *
 * 一个支付平台支持多种充值方式
 */

namespace App\Pay\Model;

use App\Jobs\SubmitWithdrawRequest;
use App\Pay\PayLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepositMethod extends Model
{
    /**
     * 适用操作系统
     */
    const OS_IOS = 1;
    const OS_ANDROID = 2;
    const OS_ANY = 3;
    public $timestamps = false;
    protected $table = 'pay_deposit_method';

    /**
     * 取得支付场景
     * @param $value
     * @return array
     */
    public function getSceneAttribute($value)
    {
        return explode(',', $value);
    }

    /**
     * 设置支付场景
     * @param array $options
     */
    public function setSceneAttribute(array $options)
    {
        $this->attributes['scene'] = implode(',', $options);

    }

    /**
     * 发起充值
     */
    public function deposit(Deposit $order, $timeout)
    {
        return (new $this->impl)->deposit(
            $order->getKey(),
            $order->amount,
            array_merge((array)parse_ini_string($this->config), (array)parse_ini_string($order->channel->config)),
            $this->getNotifyUrl($order->channel),
            $this->getReturnUrl(),
            $timeout
        );
    }

    /**
     * 获取通知地址
     */
    private function getNotifyUrl(Channel $channel)
    {
        return route('common_notify', ['notify_type' => 'pay', 'channel' => $channel->getKey(), 'method' => $this->getKey()]);
    }

    /**
     * 获取支付返回地址
     * @return string
     */
    private function getReturnUrl()
    {
        return route('pay_result', ['method' => $this->getKey()]);
    }

    /**
     * 显示充值结果
     */
    public function showDepositResult()
    {
        /**
         * @var $result DepositResult
         */
        $result = (new $this->impl)->parseReturn($this);


        //获取购买的宠物图片及获得钻石
        $petPic = '';
        $diamonds = 0;

        try {
            if ($result->state === Deposit::STATE_PAY_FAIL) {
                //支付失败,更改交易状态解锁订单
                DB::table('pay_bill_match')->join('pay_sell_bill', function ($join) {
                    $join->on('pay_bill_match.sell_bill_id', '=', 'pay_sell_bill.id')->where([
                        ['pay_bill_match.state', '=', BillMatch::STATE_WAIT],
                        ['pay_bill_match.user_id', '=', Auth::id()]
                    ]);
                })->update(['pay_bill_match.state' => BillMatch::STATE_FAIL, 'pay_sell_bill.locked' => 0]);
            }

            if ($result->state === Deposit::STATE_COMPLETE) {
                $sellBill = BillMatch::where('deposit_id', $result->id)->first()->sellBill;
                $diamonds = $sellBill->price;
                $petPic = $sellBill->pet->image;
            }
        } catch (\Exception $e) {
            PayLogger::deposit()->error('支付回跳页面错误', ['exception' => $e->getMessage()]);
        }

        $msg = Deposit::getStateText($result->state);
        return view('pay_result', ['result' => $result, 'status_text' => $msg, 'diamonds' => $diamonds, 'pet' => $petPic]);
    }

    /**
     * 接收充值通知
     * @param Channel $channel
     * @return mixed
     */
    public function acceptNotify(Channel $channel)
    {
        PayLogger::deposit()->info('支付通知', ['通道' => $channel->name, '请求' => request()]);
        //启动事务
        $commit = false;
        $result = null;
        DB::beginTransaction();
        ob_start();

        do {
            try {
                $result = (new $this->impl)->acceptNotify(array_merge((array)parse_ini_string($this->config), (array)parse_ini_string($channel->config)));
                if (!$result) {
                    break;
                }

                //获取宠物撮合订单
                $match = BillMatch::where('deposit_id', $result->getKey())->where(function ($query) {
                    $query->where('state', BillMatch::STATE_WAIT)->orWhere('state', BillMatch::STATE_EXPIRED);
                })->lockForUpdate()->first();

                if (!$match) {
                    PayLogger::deposit()->error('支付通知异常', ['说明' => '充值撮合订单不存在或状态异常', '充值id' => $result->getKey()]);
                    break;
                }

                $sellBill = $match->sellBill;
                if ($result->state === Deposit::STATE_COMPLETE) {
                    if ($match->state == BillMatch::STATE_EXPIRED) {
                        //超时支付
                        $result->state = Deposit::STATE_TIMEOUT_PAY;
                        $match->state = BillMatch::STATE_TIMEOUT_PAY;
                    } else {
                        //成交,执行交割:充值入账,宠物转移
                        $result->state = Deposit::STATE_CHARGE_FAIL;
                        $match->state = BillMatch::STATE_DEAL_FAIL;
                        $moneyAddSuccess = $result->masterContainer->changeBalance($result->amount, 0);
                        $petTransferSuccess = $moneyAddSuccess ? $sellBill->pet->transfer($match->user_id) : false;

                        if ($moneyAddSuccess && $petTransferSuccess) {
                            $result->state = Deposit::STATE_COMPLETE;
                            $match->state = BillMatch::STATE_DEAL_CLOSED;
                            $sellBill->deal_closed = 1;
                        } else {
                            PayLogger::deposit()->error('购买交割失败', ['sell_bill_id' => $sellBill->getKey(), 'match_id' => $match->getKey(), 'fund_suc' => $moneyAddSuccess, 'pet_transfer' => $petTransferSuccess]);
                        }
                    }
                } else {
                    //交易失败
                    $match->state = BillMatch::STATE_FAIL;
                    $sellBill->locked = 0;
                    if ($result->state === Deposit::STATE_PAY_FAIL) {

                    } else {
                        PayLogger::deposit()->error('支付通知异常', ['result' => $result]);
                    }
                }
                $sellBill->save();
                if (!($match->save() && $result->save())) {
                    break;
                }

                $commit = true;
            } catch (\Exception $e) {
                PayLogger::deposit()->error('支付通知异常', ['exception' => $e->getMessage()]);
            }
        } while (false);

        //结束事务
        if ($commit) {
            DB::commit();
            //处理卖单提现
            if ($match->state == BillMatch::STATE_DEAL_CLOSED) {
                if (WithdrawRetry::isWithdrawFailed((new SubmitWithdrawRequest($sellBill->withdraw))->handle()->state)) {
                    PayLogger::withdraw()->error('用户出售提现失败', ['sell_bill_di' => $sellBill->getKey()]);
                }
            }
            return ob_get_clean();
        } else {
            DB::rollBack();
            ob_end_clean();
        }
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
     * 该方式的储值记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deposits()
    {
        return $this->hasMany(Deposit::class, 'method_id');
    }
}
