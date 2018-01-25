<?php

namespace App\Http\Controllers\Api;

use App\Pay\Model\PayQuota;
use App\Pay\Model\SellBill;
use App\Pay\PayLogger;
use App\Pet;
use App\PetRecord;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 宠物交易控制器
 * Class PetTrade
 * @package App\Http\Controllers\Api
 */
class PetTradeController extends BaseController
{
    /**
     * 获取用户当前可售宠物和宠物蛋
     * @SWG\Get(
     *   path="/pet/sellable",
     *   summary="可售宠物列表",
     *   tags={"宠物交易"},
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(
     *                      property="list",
     *                      type="array",
     *                  @SWG\Items(
     *                  @SWG\Property(property="id", type="integer", example="1",description="宠物/蛋id"),
     *                  @SWG\Property(property="pic", type="string", example="http://xy.com/a.gif",description="宠物图片"),
     *                  @SWG\Property(property="is_egg", type="boolean", example=false,description="是不是蛋"),
     *                  )
     *                  )
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return array
     */
    public function sellable()
    {
        return $this->json(['list' => Auth::user()->pets_for_sale()->get()->map(function ($item) {
            return ['id' => $item->getKey(), 'pic' => $item->status == Pet::STATUS_UNHATCHED ? '' : $item->image, 'is_egg' => $item->status == Pet::STATUS_UNHATCHED];
        })]);
    }


    /**
     * 领取宠物蛋
     * @return array
     * @SWG\Post(
     *   path="/pet/acquire_egg",
     *   summary="申请免费宠物蛋",
     *   tags={"宠物交易"},
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1,
     *                  description="成功返回1"
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(
     *                      property="egg_id",
     *                      type="integer",
     *                      description="获得的蛋id"
     *                  )
     *              )
     *          )
     *      )
     * )
     */
    public function freeEgg()
    {
        $pet = Auth::user()->create_pet(Pet::TYPE_EGG, PetRecord::TYPE_NEW);
        $success = $pet === false;
        return $this->json(['egg_id' => $success ? $pet->getKey() : 0], $success ? '领取成功' : '领取失败', (int)$success);
    }

    /**
     *  剩余宠物蛋领取次数
     * @return array
     * @SWG\Get(
     *   path="/pet/egg_acquire_times",
     *   summary="宠物蛋可领取次数",
     *   tags={"宠物交易"},
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1,
     *                  description="成功返回1"
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(
     *                      property="times",
     *                      type="integer",
     *                      description="剩余次数"
     *                  )
     *              )
     *          )
     *      )
     * )
     */
    public function eggCanDrawTimes()
    {
        return $this->json(['times' => Auth::user()->pet_left_times()]);

    }


    /**
     * 孵蛋
     * @return array
     * @return array
     * @SWG\Post(
     *   path="/pet/brood",
     *   summary="孵蛋",
     *   tags={"宠物交易"},
     *     @SWG\Parameter(
     *     name="egg_id",
     *     in="formData",
     *     description="蛋id",
     *     required=true,
     *     type="integer"
     *   ),
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1,
     *                  description="成功返回1"
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer",
     *                      description="孵化宠物id"
     *                  )
     *              )
     *          )
     *      )
     * )
     */
    public function broodTheEgg(Request $request)
    {
        $pet = Pet::where([['user_id', Auth::id()], ['status', Pet::STATUS_UNHATCHED]])->find($request->egg_id);

        if (!$pet) {
            return $this->json([], '该蛋不存在', 0);
        }

        if (!$pet->hatch()) {
            return $this->json([], '孵蛋失败', 0);
        }

        return $this->json([
            'id' => $pet->getKey(),
        ], '孵化中');
    }


    /**
     * 查找卖单,没有则生成一笔专属
     * @SWG\Post(
     *   path="/pet/on_sale",
     *   summary="查找出售宠物",
     *   tags={"宠物交易"},
     *     @SWG\Parameter(
     *     name="price",
     *     in="formData",
     *     description="价格",
     *     required=true,
     *     type="number"
     *   ),
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1,
     *                  description="成功返回1"
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer",
     *                      description="卖单id"
     *                  ),
     *                  @SWG\Property(
     *                      property="pet_id",
     *                      type="integer",
     *                      description="宠物id,用于刷新宠物"
     *                  ),
     *                  @SWG\Property(
     *                      property="pic",
     *                      type="string",
     *                      description="宠物图片"
     *                  ),
     *                   @SWG\Property(
     *                      property="hatching",
     *                      type="boolean",
     *                      description="是否孵化中,未孵化要等待孵化"
     *                  ),
     *              )
     *          )
     *      )
     * )
     * @return array
     */
    public function findSellBill(Request $request)
    {
        //DB::connection()->enableQueryLog();
        $price = (float)$request->price;
        //检查宠物购买价格
        $prices = PayQuota::getPayQuotas(2);
        if (!$price || !$prices || !in_array($price, $prices)) {
            return $this->json([], '价格无效', 0);
        }

        //查找卖单
        $sellBill = SellBill::onSale()->where('price', $price)->inRandomOrder()->first();

        //没有符合条件的卖单由交易商随机生成一个
        if (!$sellBill) {

            /**
             * @var $dealer User
             */

            $dealer = User::whereHas('roles', function ($query) {
                $query->where('name', '=', Pet::DEALER_ROLE_NAME);
            })->inRandomOrder()->first();

            if (!$dealer) {
                PayLogger::deposit()->emergency('没有交易商,系统无法挂售宠物');
                return $this->json([], '当前没有宠物在售', 0);
            }

            DB::beginTransaction();
            $commit = false;
            $error = '';

            do {
                $sellBill = new SellBill([
                    'price' => $price,
                    'by_dealer' => 1,
                ]);

                $sellBill->belongToUser()->associate(Auth::user());//该卖单专属于当前用户
                $sellBill->placeBy()->associate($dealer);

                //从交易商现有宠物取得一只
                $pet = $dealer->pets_for_sale()->inRandomOrder()->lockForUpdate()->first();
                if (!$pet) {
                    //没有则为交易商生成一只
                    $pet = $dealer->create_pet(Pet::TYPE_PET, PetRecord::TYPE_NEW);
                }

                if (!$pet) {
                    $error = '系统异常E1,请稍后再试';
                    break;
                }

                /**
                 * @var $pet Pet
                 */
                $pet->status = Pet::STATUS_LOCKED;
                $sellBill->pet()->associate($pet);

                if (!$pet->save() || !$sellBill->save()) {
                    $error = '系统异常E2,请稍后再试';
                    break;
                }
                $commit = true;
            } while (false);

            $commit ? DB::commit() : DB::rollBack();
            if (!$commit) {
                return $this->json([], $error, 0);
            }

        }

        return $this->json(['id' => $sellBill->getKey(), 'pet_id' => $sellBill->pet->getKey(), 'hatching' => $sellBill->pet->status == Pet::STATUS_HATCHING, 'pic' => $sellBill->pet->image]);
    }

    /**
     * 查询宠物状态
     * @SWG\Post(
     *   path="/pet/refresh_pet",
     *   summary="订单宠物刷新",
     *   tags={"宠物交易"},
     *     @SWG\Parameter(
     *     name="pet_id",
     *     in="formData",
     *     description="宠物(蛋)id",
     *     required=true,
     *     type="integer"
     *   ),
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1,
     *                  description="成功返回1"
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer",
     *                      description="宠物id"
     *                  ),
     *                  @SWG\Property(
     *                      property="pic",
     *                      type="string",
     *                      description="宠物图片"
     *                  ),
     *                   @SWG\Property(
     *                      property="hatching",
     *                      type="boolean",
     *                      description="是否孵化中,未孵化继续轮询"
     *                  ),
     *              )
     *          )
     *      )
     * )
     * @param Request $request
     * @return array
     */
    public function queryPet(Request $request)
    {
        $pet_id = (int)$request->pet_id;
        if ($pet_id <= 0) {
            return null;
        }

        //只能查询自己的宠物或专属卖单的宠物
        $row = DB::select('select `id`,`status`,`image` from `pets` where `id` = (select `id` from `pets` where `user_id`=? and `id`=? union select `pet_id` from `pay_sell_bill` where `belong_to`=? and `pet_id`=?)', [Auth::id(), $pet_id, Auth::id(), $pet_id]);
        if ($row) {
            $pet = $row[0];
            return $this->json(['id' => $pet->id, 'hatching' => $pet->status == Pet::STATUS_HATCHING, 'pic' => $pet->image]);
        } else {
            return $this->json([], '宠物不存在', 0);
        }
    }
}
