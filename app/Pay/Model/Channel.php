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

    /**
     * 获取通道通知地址
     * @return string
     */
    public function getNotifyUrl()
    {
        return route('pay_notify', ['channel_id' => $this->getKey()]);
    }


    /**
     * 通道所属支付平台
     */
    public function platform()
    {
        return $this->belongsTo('App\Pay\Model\Platform');
    }

    /**
     * 关联的运营主体
     */
    public function businessEntity()
    {
        return $this->belongsTo('App\Pay\Model\BusinessEntity', 'entity_id');
    }

    /**
     * 通道储值记录
     */
    public function deposits()
    {
        return $this->hasMany('App\Pay\Model\Deposit');
    }

    /**
     * 通道提现记录
     */
    public function withdraws()
    {
        return $this->hasMany('App\Pay\Model\Withdraw');
    }

    /**
     * 处理通道通知
     */
    public function acceptNotify(Request $request)
    {
        $platformInterface = $this->platform->getInterface();
        if ($platformInterface instanceof PlatformInterface) {

            //启动事务
            $commit = false;
            DB::beginTransaction();

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

                //结束事务

            } while (false);

            $commit ? DB::commit() : DB::rollBack();

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
