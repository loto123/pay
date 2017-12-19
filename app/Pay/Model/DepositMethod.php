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
            $order->masterContainer,
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
        $result = (new $this->impl)->parseReturn();
        $result['state'] = Deposit::getStateText($result['state']);
        return view('pay_result', $result);
    }

    /**
     * 接收充值通知
     * @param Channel $channel
     */
    public function acceptNotify(Channel $channel)
    {
        return (new $this->impl)->acceptNotify(array_merge((array)parse_ini_string($this->config), (array)parse_ini_string($channel->config)));
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
