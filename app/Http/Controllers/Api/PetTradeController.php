<?php

namespace App\Http\Controllers\Api;

use App\Pay\Model\PayQuota;
use App\Pay\Model\SellBill;
use App\Pay\Model\Withdraw;
use App\Pay\Model\WithdrawRetry;
use App\Pay\PayLogger;
use App\Pet;
use App\PetRecord;
use App\User;
use Carbon\Carbon;
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
     *                  ),
     *               @SWG\Property(
     *                      property="egg_acquire_times",
     *                      type="integer",
     *                      description="免费宠物蛋可领取次数"
     *              )
     *               )
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
        return $this->json(['egg_acquire_times' => Auth::user()->pet_left_times(), 'list' => Auth::user()->pets_for_sale()->get()->map(function ($item) {
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
        $success = $pet != false;
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
        $sellBill = SellBill::onSale()->where([['price', $price], ['place_by', '<>', Auth::id()]])->inRandomOrder()->first();

        //没有符合条件的卖单由交易商随机生成一个
        if (!$sellBill) {

            /**
             * @var $dealer User
             */

            $dealer = User::whereHas('roles', function ($query) {
                $query->where('name', '=', Pet::DEALER_ROLE_NAME);
            })->where('id', '<>', Auth::id())->inRandomOrder()->first();

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
                    //没有则通过交易商发行一只宠物
                    $pet = $dealer->create_pet(Pet::TYPE_PET, PetRecord::TYPE_NEW);
                    $sellBill->pet_issued = 1;

                    //交易商进货记录
                    if (!DB::table('pay_dealer_pets_stock')->insert(['dealer_id' => $dealer->getKey(), 'price' => $price * 0.9, 'pet_id' => $pet->getKey()])) {
                        $error = '系统异常E3,请稍后再试';
                        break;
                    }
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

        return $this->json(['id' => $sellBill->getKey(), 'pet_id' => $sellBill->pet->getKey(), 'hatching' => empty($sellBill->pet->hash), 'pic' => $sellBill->pet->image]);
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
        $row = DB::select('select `id`,`image`,`hash` from `pets` where `id` = (select `id` from `pets` where `user_id`=? and `id`=? union select `pet_id` from `pay_sell_bill` where `belong_to`=? and `pet_id`=?)', [Auth::id(), $pet_id, Auth::id(), $pet_id]);
        if ($row) {
            $pet = $row[0];
            return $this->json(['id' => $pet->id, 'hatching' => empty($pet->hash), 'pic' => $pet->image]);
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
     *     @SWG\Parameter(
     *     name="version",
     *     description="接口版本,1返回指定月,2返回指定月及以前",
     *     default=1,
     *     in="query",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *          response="default",
     *          description="成功返回,V1",
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
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回,V2",
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
     *                      property="grouping",
     *                      type="array",
     *                      @SWG\Items(
     *                          @SWG\Property(
     *                              property="list",
     *                              type="array",
     *                              description="销售记录",
     *                              @SWG\Items(
     *                                  @SWG\Property(property="id", type="integer", description="记录id"),
     *                                  @SWG\Property(property="state", type="string", description="状态文本"),
     *                                  @SWG\Property(property="pet_pic", type="string", description="宠物图片"),
     *                                  @SWG\Property(property="price", type="double", description="出售价格"),
     *                                  @SWG\Property(property="created_at", type="string", description="出售时间"),
     *                              )
     *                          ),
     *                          @SWG\Property(property="sold_amount", type="float", description="月销售额"),
     *                          @SWG\Property(property="month", type="string", description="月份"),
     *                      ),
     *                  )
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
        $version = $request->get('version', 1);

        $endTime = "$month-31 23:59:59";

        if ($version == 1) {
            //V1版本
            $soldAmount = 0.00; //出售获得
            $startTime = "$month-1 0:0:0";

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
            $list = SellBill::where($where)->limit($limit)->with(['pet', 'withdraw'])->orderByDesc('id')->get()->map(function ($item) {
                return [
                    'id' => $item->getKey(),
                    'state' => $item->deal_closed ? '出售成功' : (WithdrawRetry::isWithdrawFailed($item->withdraw->state) || $item->withdraw->state == Withdraw::STATE_SEND_FAIL ? '状态异常' : '出售中'),
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
        } else {
            //V2版本
            $filter = [
                ['place_by', Auth::id()],
                ['created_at', '<=', $endTime]
            ];


            $where = $filter;
            if ($offset > 0) {
                $where [] = ['id', '<', $offset];
            }

            //取得出售记录
            //dump($limit);
            $collection = SellBill::where($where)->limit($limit)->with(['pet', 'withdraw'])->orderByDesc('id')->get();
            //dump(DB::getQueryLog());
            $startTime = $collection->min('created_at');

            //获取每月的销售额
            /**
             * @var $startTime Carbon
             */
            $where = $filter;
            $where [] = ['created_at', '>=', $startTime->format('Y-m-1 0:0:0')];
            $where [] = ['deal_closed', 1];
            $sellAmounts = SellBill::where($where)->selectRaw('date_format(`created_at`,\'%Y-%m\') as `month`,sum(price) as total_amount')->groupBy('month')->get()->pluck('total_amount', 'month');

            $grouped = $collection->groupBy(function ($item) {
                return substr($item->created_at, 0, 7);
            })->map(function ($list, $month) use ($sellAmounts) {
                $list->transform(function ($item) {
                    return [
                        'id' => $item->getKey(),
                        'state' => $item->deal_closed ? '出售成功' : (WithdrawRetry::isWithdrawFailed($item->withdraw->state) || $item->withdraw->state == Withdraw::STATE_SEND_FAIL ? '状态异常' : '出售中'),
                        'pet_pic' => $item->pet->image,
                        'price' => $item->price,
                        'created_at' => $item->created_at->toDateTimeString()
                    ];

                });
                return ['list' => $list, 'month' => $month, 'sold_amount' => (float)$sellAmounts->get($month)];
            });

            return $this->json(['grouping' => $grouped->sortByDesc('month')->values()]);
        }

    }


    /**
     * 交易行在售宠物
     * @SWG\Get(
     *   path="/pet/all_on_sale",
     *   summary="交易行在售宠物",
     *   tags={"交易行"},
     *     @SWG\Parameter(
     *     name="offset",
     *     in="query",
     *     description="最后记录id",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     description="数目",
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
     *                  description="宠物列表",
     *                  @SWG\Property(
     *                      property="1",
     *                      type="array",
     *                      description="宠物信息",
     *                      @SWG\Items(
     *                          @SWG\Property(property="id", type="integer", example="1", description="记录id"),
     *                          @SWG\Property(property="pet_id", type="integer", example="1", description="宠物编号"),
     *                          @SWG\Property(property="holder_name", type="string", example="张三", description="持有人"),
     *                          @SWG\Property(property="price", type="string", example="100", description="出售价格"),
     *                          @SWG\Property(property="pet_image", type="string", example="url", description="宠物图片"),
     *                      ),
     *                  ),
     *              )
     *          )
     *      ),
     * )
     * @param Request $request
     * @return mixed
     */
    public function onSalePets(Request $request)
    {
        $data = [];
        $query = SellBill::onSale()->has('placeBy')->has('pet')->with(['placeBy','pet']);
        if($request->offset) {
            $query = $query->where('id','<',$request->offset);
        }
        $count = $query->count();
        $bill = $query->orderBy('id','DESC')->limit($request->input('limit', 20))->get();
        if(!empty($bill) && count($bill)>0) {
            foreach ($bill as $item) {
                $data[] = [
                    'id' => $item->id,
                    'pet_id' => $item->pet_id,
                    'pet_image' => $item->pet->image,
                    'holder_name' => $item->placeBy->name,
                    'price' => ($item->by_dealer==1)? '面议' : $item->price,
                ];
            }
        }
        return $this->json(compact('count','data'));
    }

    /**
     * 交易行我的宠物
     * @SWG\Get(
     *   path="/pet/my_pets",
     *   summary="交易行我的宠物",
     *   tags={"交易行"},
     *     @SWG\Parameter(
     *     name="offset",
     *     in="query",
     *     description="最后记录time",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     description="数目",
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
     *                  description="宠物列表",
     *                  @SWG\Property(
     *                      property="1",
     *                      type="array",
     *                      description="宠物信息",
     *                      @SWG\Items(
     *                          @SWG\Property(property="pet_id", type="integer", example="1", description="宠物编号"),
     *                          @SWG\Property(property="holder_name", type="string", example="张三", description="持有人"),
     *                          @SWG\Property(property="price", type="string", example="100", description="出售价格"),
     *                          @SWG\Property(property="time", type="string", example="100", description="时间"),
     *                          @SWG\Property(property="pet_image", type="string", example="url", description="宠物图片"),
     *                      ),
     *                  ),
     *              )
     *          )
     *      ),
     * )
     * @param Request $request
     * @return mixed
     */
    public function myPets(Request $request)
    {
        $query = Pet::where('user_id',$this->user()->id);
        if($request->offset)
        {
            $query = $query->where('updated_at', '<', date('Y-m-d H:i:s',$request->offset));
        }

        $pets = $query->limit($request->input('limit', 20))->get();
        //用户购买宠物记录
        $user_bill = $this->user()->whereHas('bill_match', function($query) use($pets) {
            $query->whereHas('sellBill', function($query) use($pets) {
                $query->whereIn('pet_id', $pets->pluck('id'));
            });
        })->first();

        //买来的宠物
        $pay_pets = [];
        if(isset($user_bill->bill_match) && count($user_bill->bill_match)>0) {
            foreach ($user_bill->bill_match as $item) {
                if(isset($pay_pets[$item->sellBill->pet_id])
                    && $pay_pets[$item->sellBill->pet_id]['created_at'] > (string)$item->created_at) {
                    continue;
                }
                $pay_pets[$item->sellBill->pet_id] = [
                    'price' => $item->sellBill->price,
                    'created_at' => (string)$item->created_at,
                ];
            }
        }

        //用户的宠物
        $data = [];
        $count = $pets->count();
        if(isset($pets) && count($pets)>0) {
            foreach ($pets as $_pet) {
                $data[] = [
                    'pet_id' => $_pet->id,
                    'holder_name'=>$this->user()->name,
                    'pet_image' => $_pet->image,
                    'price' => isset($pay_pets[$_pet->id]) ? $pay_pets[$_pet->id]['price'] : 0,
                    'time' => strtotime((string)$_pet->updated_at),
                ];
            }
        }

        return $this->json(compact('data','count'));
    }
}
