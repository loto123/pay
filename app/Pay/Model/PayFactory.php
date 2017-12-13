<?php
/**
 * 主容器工厂
 *
 * @transaction safe
 * Author: huangkaixuan
 * Date: 2017/12/7
 * Time: 16:07
 */

namespace App\Pay\Model;


class PayFactory
{
    /**
     * 取得或生成一个主容器
     * @param null $id
     * @return MasterContainer|null
     */
    public static function MasterContainer($id = null)
    {
        if ($id !== null) {
            return MasterContainer::findOrFail($id);
        } else {
            //生成
            return new MasterContainer();
        }
    }

    /**
     * 构造分润
     * @param Container $receiver
     * @param $amount
     * @param $is_frozen
     */
    public static function profitShare(Container $receiver, $amount, $is_frozen)
    {
        $profit_share = new ProfitShare([
            'amount' => $amount,
            'is_frozen' => $is_frozen,
        ]);
        $profit_share->receiveContainer()->associate($receiver);
        return $profit_share;
    }
}