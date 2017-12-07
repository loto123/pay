<?php
/**
 * 运营主体
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

class BusinessEntity extends Model
{
    public $timestamps = false;
    protected $table = 'pay_business_entity';

    /**
     * 关联的支付通道
     */
    public function channels()
    {
        $this->hasMany('App\Pay\Model\Channel', 'entity_id');
    }
}
