<?php
/**
 * 余额转账
 */

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $table = 'pay_transfer';

    /**
     * 退回资金
     *
     * @return bool
     */
    public function chargeback()
    {
        //TODO
    }
}
