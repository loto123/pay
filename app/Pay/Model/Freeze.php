<?php
/**
 * 资金冻结/解冻记录
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

class Freeze extends Model
{
    const UPDATED_AT = null;
    protected $table = 'pay_freeze';
    protected $casts = [
        'amount' => 'float',
        'create_at' => 'datetime'
    ];

    public function container()
    {
        $this->morphTo();
    }
}
