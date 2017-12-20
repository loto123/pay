<?php
/**
 *
 * @transaction safe
 * 充值方式
 *
 * 一个支付平台支持多种充值方式
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

class DepositMethod extends Model
{
    public $timestamps = false;
    protected $table = 'pay_deposit_method';

    /**
     * 发起充值
     */
    public function deposit(Deposit $order)
    {
        return (new $this->impl)->deposit(
            $order->getKey(),
            $order->amount,
            array_merge((array)parse_ini_string($this->config), (array)parse_ini_string($order->channel->config)),
            $this->getNotifyUrl($order->channel),
            $this->getReturnUrl()
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
        $result->state = Deposit::getStateText($result['state']);
        return view('pay_result', (array)$result);
    }

    /**
     * 接收充值通知
     * @param Channel $channel
     */
    public function acceptNotify(Channel $channel)
    {
        //启动事务
        $commit = false;
        DB::beginTransaction();
        ob_start();

        do {
            $result = (new $this->impl)->acceptNotify(array_merge((array)parse_ini_string($this->config), (array)parse_ini_string($channel->config)));
            if (!$result) {
                break;
            }

            if ($result->state === Deposit::STATE_COMPLETE) {
                if (!$result->masterContainer->changeBalance($result->amount, 0)) {
                    break;//到账失败
                }
            }

            if (!$result->save()) {
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
