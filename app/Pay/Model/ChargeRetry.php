<?php
/**
 * 充值尝试手动到账
 * Author: huangkaixuan
 * Date: 2017/12/23
 * Time: 19:45
 */

namespace App\Pay\Model;


use Illuminate\Support\Facades\DB;

class ChargeRetry extends PayRetry
{
    protected static $type = 'charge';
    function reDo()
    {
        // 手动到账
        $commit = false;
        DB::beginTransaction();

        do {
            $deposit = Deposit::where(['id' => $this->id, 'state' => Deposit::STATE_CHARGE_FAIL])->lockForUpdate()->first();
            if (!$deposit) {
                $msg = '充值不存在或已经到账';
                break;
            }

            if (!$deposit->masterContainer->changeBalance($deposit->amount, 0)) {
                $msg = '操作失败,请联系技术人员处理';
                break;
            }

            $deposit->state = Deposit::STATE_COMPLETE;
            if (!$deposit->save()) {
                $msg = '数据更新失败E1';
                break;
            }

            //将宠物也划拨一下,并将撮合状态改为成功
            $petBillMatch = BillMatch::where('deposit_id', $deposit->getKey())->first();
            $petBillMatch->state = BillMatch::STATE_DEAL_CLOSED;//拿到了宠物和钻石
            $thePet = $petBillMatch->sellBill->pet;
            if ($thePet->user_id != $petBillMatch->user_id) {
                if (!$thePet->transfer($petBillMatch->user_id)) {
                    $msg = '数据更新失败E2';
                    break;
                }
            }
            if (!$petBillMatch->save()) {
                $msg = '数据更新失败E3';
                break;
            }


            $commit = true;
            $msg = '到账成功';
        } while (false);

        $commit ? DB::commit() : DB::rollBack();
        return $this->response($commit, $msg);
    }
}