<?php
/**
 * 交易商交易
 * Author: huangkaixuan
 * Date: 2018/1/26
 * Time: 14:48
 */

namespace App\Admin\Model;


use Illuminate\Database\Eloquent\Model;


class ContainerTransaction extends Model
{
    const TYPE_TRANSFER_OUT = 1;
    const TYPE_TRANSFER_IN = 2;
    const TYPE_PROFIT_SHARE = 3;
    const TYPE_DEPOSIT = 4;
    const TYPE_WITHDRAW = 5;
    const TYPE_FREEZE = 6;
    const TYPE_UNFREEZE = 7;
    protected $table = 'pay_container_transactions';

    public static function getTypeTextArray()
    {
        return [ContainerTransaction::TYPE_TRANSFER_OUT => '转出',
            ContainerTransaction::TYPE_TRANSFER_IN => '转入',
            ContainerTransaction::TYPE_PROFIT_SHARE => '分润',
            ContainerTransaction::TYPE_DEPOSIT => '充值',
            ContainerTransaction::TYPE_WITHDRAW => '提现',
            ContainerTransaction::TYPE_FREEZE => '冻结',
            ContainerTransaction::TYPE_UNFREEZE => '解冻'];
    }
}