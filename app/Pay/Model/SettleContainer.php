<?php
/**
 * 结算容器
 * 用于向多个主容器收发资金
 * Author: huangkaixuan
 * Date: 2017/12/4
 * Time: 16:51
 */

namespace App\Pay\Model;


use App\Pay\ContainerTrait;
use Illuminate\Support\Facades\DB;

class SettleContainer extends Container
{
    /**
     * 状态
     */
    const STATE_NORMAL = 0;
    const STATE_CLOSED = 1; //正常
    const STATE_EXTRACTED = 2; //关闭
    protected $table = 'pay_settle_container'; //已提取

    /**
     * 提取所有金额
     * @return bool
     */
    public function extract()
    {
        //启动事务
        $commit = false;
        DB::beginTransaction();

        do {
            //更改容器状态
            if (!DB::table($this->table)->where([
                ['id', '=', $this->getKey()],
                ['state', '<>', self::STATE_EXTRACTED]
            ])->update(['state' => self::STATE_EXTRACTED])
            ) {
                break;
            }
            $this->state = self::STATE_EXTRACTED;

            //获取总余额
            $reserve = DB::table($this->table)->select('balance, frozen_balance')
                ->where('id', $this->getKey())->lockForUpdate()->first();

            $toExtract = $reserve->balance + $reserve->frozen_balance;
            if ($toExtract <= 0) {
                break;
            }

            //清空结算余额
            if (!$this->changeBalance(-$reserve->balance, -$reserve->frozen_balance)) {
                break;
            }

            //主容器增加余额
            if (!$this->masterContainer()->changeBalance($toExtract, 0)) {
                break;
            }

            //新的提取记录
            if (!$this->extraction()->save(new MoneyExtract([
                'amount' => $toExtract
            ]))
            ) {
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
        return $this->belongsTo('App\Pay\Model\MasterContainer', 'master_container');
    }

    /**
     * 结算提取记录
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function extraction()
    {
        return $this->hasOne('App\Pay\Model\MoneyExtract', 'settle_container');
    }

    /**
     * 关闭结算容器
     * 容器关闭后无法收发款
     *
     * @return bool
     */
    public function close()
    {
        if ($this->state == self::STATE_NORMAL) {
            $this->state = self::STATE_CLOSED;
            return $this->save();
        } else {
            return false;
        }

    }

    /**
     * 转账到容器
     * @param Container $to_container
     * @param float $amount
     * @param int $type
     * @param float $fee
     * @param bool $from_frozen
     * @param bool $to_frozen
     * @param array $profit_shares
     * @return Transfer|bool
     */
    public function transfer(Container $to_container, $amount, $fee, $from_frozen, $to_frozen, array $profit_shares = [])
    {
        if ($this->state != self::STATE_NORMAL) {
            return false;
        }
        return $this->transfer($to_container, $amount, $fee, $from_frozen, $to_frozen, $profit_shares);
    }
}