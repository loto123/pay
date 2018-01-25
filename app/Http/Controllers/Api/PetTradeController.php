<?php

namespace App\Http\Controllers\Api;

use App\Pay\Model\PayQuota;
use App\Pay\Model\SellBill;
use App\Pay\Model\WithdrawRetry;
use App\Pay\PayLogger;
use App\Pet;
use App\PetRecord;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

    /**
     * 我的宠物出售记录
     * @SWG\Get(
     *   path="/pet/sold_record",
     *   summary="出售记录",
     *   tags={"宠物交易"},
     *   @SWG\Parameter(
     *     name="month",
     *     description="月份Y-m,默认当月",
     *     required=false,
     *     in="query",
     *     type="string"
     *   ),
     *     @SWG\Parameter(
     *     name="offset",
     *     description="已读的最后记录id",
     *     default=0,
     *     in="query",
     *     required=false,
     *     type="integer"
     *   ),
     *     @SWG\Parameter(
     *     name="limit",
     *     description="每页显示记录数",
     *     default=10,
     *     in="query",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(
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
     *                      property="sold_amount",
     *                      type="float",
     *                      example=100.00,
     *                      description="本月成交销售额"
     *                  ),
     *                  @SWG\Property(
     *                      property="month",
     *                      type="string",
     *                      example="2018-01",
     *                      description="月份"
     *                  ),
     *                  @SWG\Property(
     *                      property="list",
     *                      type="array",
     *                      description="购买方式列表",
     *                      @SWG\Items(
     *                          @SWG\Property(property="id", type="integer", description="记录id"),
     *                          @SWG\Property(property="state", type="string", description="状态文本"),
     *                          @SWG\Property(property="pet_pic", type="string", description="宠物图片"),
     *                          @SWG\Property(property="price", type="double", description="出售价格"),
     *                          @SWG\Property(property="created_at", type="string", description="出售时间"),
     *                      ),
     *                  ),
     *              )
     *          )
     *      ),
     * )
     * @param Request $request
     * @return mixed
     */
    public function mySoldPets(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'date_format:Y-m',
            'offset' => 'integer|min:0',
            'limit' => 'integer|min:1'
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        $month = $request->get('month', date('Y-m'));
        $offset = (int)$request->get('offset', 0);
        $limit = (int)$request->get('limit', 10);

        $soldAmount = 0.00; //出售获得
        $startTime = "$month-1 0:0:0";
        $endTime = "$month-31 23:59:59";

        $filter = [
            ['place_by', Auth::id()],
            ['created_at', '>=', $startTime],
            ['created_at', '<=', $endTime]
        ];


        $where = $filter;
        if ($offset > 0) {
            $where [] = ['id', '<', $offset];
        }

        //取得出售记录
        $list = SellBill::where($filter)->limit($limit)->with(['pet', 'withdraw'])->orderByDesc('id')->get()->map(function ($item) {
            return [
                'id' => $item->getKey(),
                'state' => $item->deal_closed ? (WithdrawRetry::isWithdrawFailed($item->withdraw->state) ? '状态异常' : '出售成功') : '出售中',
                'pet_pic' => $item->pet->image,
                'price' => $item->price,
                'created_at' => $item->created_at->toDateTimeString()
            ];
        });

        //取得当月销售额
        if ($list) {
            $where = $filter;
            $where [] = ['deal_closed', 1];
            $soldAmount = SellBill::where($where)->sum('price');
        }

        return $this->json(['sold_amount' => $soldAmount, 'month' => $month, 'list' => $list]);

    }
}
