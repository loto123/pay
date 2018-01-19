<?php
/**
 *
 * @transaction safe
 * 充值方式
 *
 * 一个支付平台支持多种充值方式
 */

namespace App\Pay\Model;

use App\Pay\PayLogger;
use Illuminate\Database\Eloquent\Model;
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
        $msg = Deposit::getStateText($result->state);
        return view('pay_result', ['result' => $result, 'status_text' => $msg]);
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
        DB::beginTransaction();
        ob_start();

        do {
            $result = (new $this->impl)->acceptNotify(array_merge((array)parse_ini_string($this->config), (array)parse_ini_string($channel->config)));
            if (!$result) {
                break;
            }

            if ($result->state === Deposit::STATE_COMPLETE) {
                $result->state = Deposit::STATE_CHARGE_FAIL;//到账失败
                if ($result->masterContainer->changeBalance($result->amount, 0)) {
                    $result->state = Deposit::STATE_COMPLETE;
                }
            } else {
                PayLogger::deposit()->notice('支付异常', [$result->toArray()]);
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
