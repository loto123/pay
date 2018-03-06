<?php
/**
 * 充值尝试手动到账
 * Author: huangkaixuan
 * Date: 2017/12/23
 * Time: 19:45
 */

namespace App\Pay\Model;


use App\Pet;
use App\PetRecord;
use App\User;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\DB;

class ChargeRetry extends PayRetry
{
    const PERMISSION_NAME = 'retry_charges';
    protected static $type = 'charge';

    function reDo()
    {
        if (Admin::user()->cannot(self::PERMISSION_NAME)) {
            return $this->response(false, '没有强制到账权限');
        }

        // 手动到账
        $commit = false;
        DB::beginTransaction();

        do {
            $deposit = Deposit::where('id', $this->id)->whereIn('state', [Deposit::STATE_CHARGE_FAIL, Deposit::STATE_TIMEOUT_PAY, Deposit::STATE_EXPIRED])->lockForUpdate()->first();
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

            //将撮合状态改为成功
            $petBillMatch = BillMatch::where('deposit_id', $deposit->getKey())->first();
            $petBillMatch->state = BillMatch::STATE_DEAL_CLOSED;//拿到了钻石

            //用户补偿一个宠物
            $user = User::where('container_id', $deposit->master_container)->first();
            if (!$user->create_pet(Pet::TYPE_PET, PetRecord::TYPE_NEW)) {
                $msg = '数据更新失败E2';
                break;
            }
            //可能原卖单已经宠物已经卖给别人，不能直接交割
//            $thePet = $petBillMatch->sellBill->pet;
//            if ($thePet->user_id != $petBillMatch->user_id) {
//                if (!$thePet->transfer($petBillMatch->user_id)) {
//                    $msg = '数据更新失败E2';
//                    break;
//                }
//            }
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

    protected function render()
    {
        return "<a class='btn btn-xs btn-danger fa grid-retry' data-id='{$this->id}'>强制到账</a>";
    }
}