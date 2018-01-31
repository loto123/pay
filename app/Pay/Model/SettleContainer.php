<?php
/**
 * 结算容器
 *
 * @transaction safe
 * 用于向多个主容器收发资金
 * Author: huangkaixuan
 * Date: 2017/12/4
 * Time: 16:51
 */

namespace App\Pay\Model;


use Illuminate\Support\Facades\DB;

class SettleContainer extends Container
{
    /**
     * 状态
     */
    const STATE_NORMAL = 0;//正常
    const STATE_CLOSED = 1; //关闭
    const STATE_EXTRACTED = 2; //已提取
    protected $table = 'pay_settle_container';
    protected $casts = [
        'state' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'balance' => 'float',
        'frozen_balance' => 'float',
    ];

    /**
     * 提取所有金额
     * 独占主-从容器
     * @return bool
     */
    public function extract()
    {
        //启动事务
        $commit = false;
        DB::beginTransaction();

        do {
            //获取总余额
            $reserve = DB::table($this->table)->select('balance', 'frozen_balance')->lockForUpdate()->first();

            $toExtract = $reserve->balance + $reserve->frozen_balance;
            if ($toExtract <= 0) {
                break;
            }

            //清空结算余额
            if (!$this->changeBalance(-$reserve->balance, -$reserve->frozen_balance)) {
                break;
            }

            //主容器增加余额
            if (!$this->masterContainer->changeBalance($toExtract, 0)) {
                break;
            }

            //新的提取记录
            if (!$this->extraction()->save(new MoneyExtract([
                'amount' => $toExtract
            ]))
            ) {
                break;
            }

            //更新提取状态
            if (!self::where($this->getKeyName(), $this->getKey())->update(['state' => self::STATE_EXTRACTED])) {
                break;
            }

            $commit = true;
        } while (false);

        $commit ? DB::commit() : DB::rollBack();
        if ($commit) {
            $this->state = self::STATE_EXTRACTED;
        }
        return $commit ? $toExtract : $commit;
    }

    /**
     * 结算提取记录
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function extraction()
    {
        return $this->hasOne(MoneyExtract::class, 'settle_container');
    }

    /**
     * 主容器
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function masterContainer()
    {
        return $this->belongsTo(MasterContainer::class, 'master_container');
    }

    /**
     * 关闭结算容器
     * 容器关闭后无法收发款
     *
     * @return bool
     */
    public function close()
    {
        return self::where([[$this->getKeyName(), $this->getKey()], ['state', self::STATE_NORMAL]])->update(['state' => self::STATE_CLOSED]) > 0;
    }
}