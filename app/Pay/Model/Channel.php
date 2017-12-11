<?php
/**
 * 支付通道
 */

namespace App\Pay\Model;

use App\Pay\PlatformInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Channel extends Model
{
    public $timestamps = false;
    protected $table = 'pay_channel';
    protected $casts = [
        'limit_amount' => 'float',
        'used_amount' => 'float',
        'disabled' => 'boolean'
    ];


    /**
     * 获取通道通知地址
     * @return string
     */
    public function getNotifyUrl()
    {
        return route('pay_notify', ['channel_id' => $this->getKey()]);
    }


    /**
     * 获取备用通道
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function spareChannel()
    {
        return $this->belongsTo(self::class, 'spare_channel_id');
    }


    /**
     * 通道所属支付平台
     */
    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    /**
     * 关联的运营主体
     */
    public function businessEntity()
    {
        return $this->belongsTo(BusinessEntity::class, 'entity_id');
    }

    /**
     * 通道储值记录
     */
    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    /**
     * 通道提现记录
     */
    public function withdraws()
    {
        return $this->hasMany(Withdraw::class);
    }

    /**
     * 处理通道通知
     */
    public function acceptNotify(Request $request)
    {
        $platformInterface = $this->platform->getImplInstance();
        if ($platformInterface instanceof PlatformInterface) {

            //启动事务
            $commit = false;
            DB::beginTransaction();

            ob_start();
            do {
                $result = $platformInterface->acceptNotify($request, $this->getInterfaceConfigure());
                if (!($result instanceof Withdraw || $result instanceof Deposit)) {
                    break;
                }

                if ($result instanceof Withdraw) {
                    //提现通知

                } else {
                    //充值通知
                    if ($result->state === Deposit::STATE_COMPLETE) {
                        if (!$result->masterContainer->changeBalance($result->amount, 0)) {
                            break;//到账失败
                        }
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
    }

    /**
     * 获取接口参数
     *
     * @return array
     */
    public function getInterfaceConfigure()
    {
        return array_merge(
            (array)parse_ini_string($this->platform->public_cfg),//公共参数
            (array)parse_ini_string($this->cfg)//通道参数
        );
    }
}
