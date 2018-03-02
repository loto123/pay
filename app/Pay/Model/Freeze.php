<?php
/**
 * 资金冻结/解冻记录
 *
 * @transaction safe
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

class Freeze extends Model
{
    const UPDATED_AT = null;
    const OPERATION_FREEZE = 0;
    const OPERATION_UNFREEZE = 1;
    protected $table = 'pay_freeze';
    protected $guarded = ['id'];
    protected $casts = [
        'amount' => 'float',
        'created_at' => 'datetime'
    ];

    public function container()
    {
        return $this->morphTo(null, 'container_type', 'container_id');
    }
}
