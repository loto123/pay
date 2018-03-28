<?php
/**
 *
 * @transaction safe
 * 提现模型
 */

namespace App\Pay\Model;

use App\Pet;
use App\PetRecord;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Withdraw extends Model
{
    /**
     * 提现状态
     */
    const STATE_QUEUED = 1; //已进入提现队列
    const STATE_SUBMIT = 2; //已提交平台处理
    const STATE_COMPLETE = 3;//处理完成
    const STATE_PROCESS_FAIL = 4;//处理失败
    const STATE_SEND_FAIL = 5;//提交失败
    const STATE_CANCELED = 6;//已取消

    protected $table = 'pay_withdraw';
    protected $casts = [
        'amount' => 'float',
        'system_fee' => 'float',
        'channel_fee' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'state' => 'integer'
    ];
    protected $guarded = ['id'];

    /**
     * 取得状态文本
     * @param $state
     * @return string
     */
    public static function getStateText($state)
    {
        switch ($state) {
            case self::STATE_QUEUED:
                return '等待处理';
            case self::STATE_SEND_FAIL:
                return '提交通道失败';
            case self::STATE_PROCESS_FAIL:
                return '通道处理失败';
            case self::STATE_SUBMIT:
                return '等待通道处理';
            case self::STATE_COMPLETE:
                return '提现成功';
            case self::STATE_CANCELED:
                return '已退款';
            default:
                return '异常';
        }
    }

    public function getAmountAttribute($value)
    {
        return sprintf('%.2f', $value);
    }

    public function getReceiverInfoAttribute($value)
    {
        return unserialize($value);
    }

    public function setReceiverInfoAttribute(array $value)
    {
        $this->attributes['receiver_info'] = serialize($value);
    }

    /**
     * 提现方式
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function method()
    {
        return $this->belongsTo(WithdrawMethod::class, 'method_id');
    }

    /**
     * 宠物出售订单
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function petSellBill()
    {
        return $this->hasOne(SellBill::class);
    }

    /**
     * 提现通道
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * 提现状态变更回调,会保存相关状态到数据库
     * @param $query
     * @param $content
     * @return bool
     */
    public function stateCallback($query, $content)
    {
        if ($this->state === Withdraw::STATE_PROCESS_FAIL) {
            $exception = '';

            //失败要更改卖单状态为未成交
            try {
                SellBill::where('withdraw_id', $this->getKey())->update(['deal_closed' => 0]);
            } catch (\Exception $e) {
                $exception = $e->getMessage();
                //break;
            }

            $this->exceptions()->save(new WithdrawException([
                'message' => json_encode(['query' => $query, 'body' => $content], JSON_UNESCAPED_UNICODE),
                'state' => $this->state,
                'exception' => $exception
            ]));
        }

        if (!$this->save()) {
            return false;
        }

        return true;
    }

    /**
     * 提现异常
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function exceptions()
    {
        return $this->hasMany(WithdrawException::class);
    }

    /**
     * 取消提现
     * @return bool
     */
    public function cancel()
    {
        $commit = false;
        DB::beginTransaction();

        do {
            if (!self::where($this->getKeyName(), $this->getKey())->whereIn('state', WithdrawRetry::$abnormal_states)->update(['state' => self::STATE_CANCELED])) {
                break;
            }

            if (!$this->masterContainer->changeBalance($this->amount, 0)) {
                break;
            }

            //用户补偿一个宠物
            $user = User::where('container_id', $this->master_container)->first();
            if (!$user->create_pet(Pet::TYPE_PET, PetRecord::TYPE_NEW)) {
                break;
            }

            $commit = true;
        } while (false);

        $commit ? DB::commit() : DB::rollBack();
        return $commit;
    }

    /**
     * 主容器
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function masterContainer()
    {
        return $this->belongsTo(MasterContainer::class, 'master_container');
    }
}
