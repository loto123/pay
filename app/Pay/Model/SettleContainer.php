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
use Illuminate\Database\Eloquent\Model;

class SettleContainer extends Model
{
    use ContainerTrait;

    const CREATED_AT = null;
    protected $table = 'pay_settle_container';

    /**
     * 主容器
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function masterContainer()
    {
        return $this->belongsTo('App\Pay\Model\MasterContainer', 'master_container');
    }

    /**
     * 取得内部容器
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function container()
    {
        return $this->morphOne('App\Pay\Model\Container', 'instance');
    }

    /**
     * 结算提取
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function extraction()
    {
        return $this->hasOne('App\Pay\Model\MoneyExtract', 'settle_container');
    }
}