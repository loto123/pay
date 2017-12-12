<?php
/**
 *
 * @transaction safe
 * 提现异常
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

class WithdrawException extends Model
{
    const UPDATED_AT = null;
    protected $table = 'pay_withdraw_exception';
    protected $casts = [
        'create_at' => 'datetime'
    ];

    /**
     * 所属提现
     */
    public function withdrawBelongTo()
    {
        return $this->belongsTo('App\Pay\Model\Withdraw');
    }
}
