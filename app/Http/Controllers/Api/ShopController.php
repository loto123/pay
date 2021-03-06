<?php

namespace App\Http\Controllers\Api;

use App\Notice;
use App\Notifications\ShopApply;
use App\Pay\Model\PayFactory;
use App\Shop;
use App\ShopFund;
use App\ShopUser;
use App\TipRecord;
use App\Transfer;
use App\User;
use App\UserFund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


/**
 *
 * @package App\Http\Controllers\Api
 */
class ShopController extends BaseController
{

    /**
     * @SWG\Post(
     *   path="/shop/create",
     *   summary="创建店铺",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     description="店铺名",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="rate",
     *     in="formData",
     *     description="倍率",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="percent",
     *     in="formData",
     *     description="抽水比例",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="active",
     *     in="formData",
     *     description="是否开启交易",
     *     required=true,
     *     type="boolean"
     *   ),
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
     *                  type="object"
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $user = $this->auth->user();
        if (!$user->can("create_guild")) {
            return $this->json([], trans("api.error_create_guild"), 0);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:10',
            'rate' => 'required|regex:/^\d{0,5}(\.\d{1})?$/|numeric|between:0.1,99999',
            'percent' => 'required|integer|between:0,100',
            'active' => 'required'
        ],['name.required'=>'公会名必填',
        'name.max'=>'公会名称不能超过10个字符',
        'rate.required'=>'默认倍率必填',
        'rate.regex'=>'默认倍率请填写0.1到99999之间数字',
        'rate.between' => '默认倍率请填写0.1到99999之间数字',
        'rate.numeric' => '默认倍率请填写0.1到99999之间数字',
        'percent.required'=>'佣金费率不能为空',
        'percent.integer'=>'佣金费率必须为0-100的整数',
        'percent.between'=>'佣金费率必须为0-100的整数'
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        if ($request->percent > config("guild_commission", 0)) {
            return $this->json([], trans("api.error_shop_percent"), 0);
        }

        if ($user->shop()->where("status", Shop::STATUS_NORMAL)->count() >= config("max_shops", 3)) {
            return $this->json([], trans("api.over_max_times"), 0);
        }
        $wallet = PayFactory::MasterContainer();
        $wallet->save();
        $shop = new Shop();
        $shop->name = $request->name;
        $shop->manager_id = $user->id;
        $shop->price = $request->rate;
        $shop->fee = $request->percent;
        $shop->container_id = $wallet->id;
        $shop->active = $request->active ? 1 : 0;
        $shop->save();
        $shop_user = new ShopUser();
        $shop_user->shop_id = $shop->id;
        $shop_user->user_id = $user->id;
        $shop_user->save();
        Artisan::queue('shop:logo', [
            '--id' => $shop->id
        ])->onQueue('shop_logo');
        return $this->json();
    }

    /**
     * @SWG\Get(
     *   path="/shop/lists",
     *   summary="我参与的店铺",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="offset",
     *     in="query",
     *     description="上次记录ID",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     description="数目",
     *     required=false,
     *     type="integer"
     *   ),
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
     *                  @SWG\Property(property="count", type="integer", example=20,description="总数"),
     *                  @SWG\Property(
     *                      property="data",
     *                      type="array",
     *                  @SWG\Items(
     *                      @SWG\Property(property="id", type="string", example="1234567890", description="店铺id"),
     *                      @SWG\Property(property="name", type="string", example="我的店铺", description="店铺名"),
     *                      @SWG\Property(property="logo", type="string", example="http://url/logo", description="店铺logo地址"),
     *                      @SWG\Property(property="status", type="integer", example=1, description="店铺状态 0=正常 2=冻结"),
     *                  )
     *                  ),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function lists(Request $request)
    {
        $user = $this->auth->user();
        $my_shop_ids = $user->shop()->pluck("id");
        $data = [];
        $query = $user->in_shops()->whereNotIn((new Shop)->getTable() . ".id", $my_shop_ids)->whereIn("status", [Shop::STATUS_NORMAL, Shop::STATUS_FREEZE]);
        $count = (int)$query->count();
        if ($request->offset) {
            $query->where((new Shop)->getTable() . ".id", "<", Shop::decrypt($request->offset));
        }
        $query->limit($request->input("limit", 20))->orderBy("ID", "DESC");
        foreach ($query->get() as $_shop) {
            /* @var $_shop Shop */
            $data[] = [
                'id' => $_shop->en_id(),
                'name' => $_shop->name,
                'logo' => $_shop->logo,
                'status' => (int)$_shop->status
            ];
        }
        return $this->json(['count' => $count, 'data' => $data]);
    }

    /**
     * @SWG\Get(
     *   path="/shop/lists/all",
     *   summary="我所有店铺（创建交易）",
     *   tags={"店铺"},
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
     *                  @SWG\Property(property="count", type="integer", example=20,description="总数"),
     *                  @SWG\Property(
     *                      property="data",
     *                      type="array",
     *                  @SWG\Items(
     *                      @SWG\Property(property="id", type="string", example="1234567890", description="店铺id"),
     *                      @SWG\Property(property="name", type="string", example="我的店铺", description="店铺名"),
     *                      @SWG\Property(property="price", type="double", example=9.9, description="店铺单价"),
     *                  )
     *                  ),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function all()
    {
        $user = $this->auth->user();
        $in_shops = $shops = [];
        $in_query = $user->in_shops()->where("status", Shop::STATUS_NORMAL);
        $count = $in_query->count();
        foreach ($in_query->get() as $_shop) {
            $in_shops[$_shop->id] = $_shop;
        }
        $data = [];
        //最近交易店铺
        $transfer_shop_ids = $user->transfer()->select(DB::raw("shop_id, max(id) as id_order"))->groupBy("shop_id")->orderBy("id_order", "DESC")->pluck("shop_id");
        $manager_shop_ids = $user->shop()->where("status", Shop::STATUS_NORMAL)->pluck("id");
        $shop_ids = $transfer_shop_ids->merge($manager_shop_ids)->merge(array_keys($in_shops))->unique()->all();
        foreach ($shop_ids as $shop_id) {
            if (isset($in_shops[$shop_id])) {
                $_shop = $in_shops[$shop_id];
                $data[] = [
                    'id' => $_shop->en_id(),
                    'name' => $_shop->name,
                    'price' => (double)$_shop->price
                ];
            }
        }
        return $this->json(['count' => $count, 'data' => $data]);
    }

    /**
     * @SWG\Get(
     *   path="/shop/lists/mine",
     *   summary="我创建的店铺",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="offset",
     *     in="query",
     *     description="上次记录ID",
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
     *                  @SWG\Property(property="count", type="integer", example=20,description="总数"),
     *                  @SWG\Property(
     *                      property="data",
     *                      type="array",
     *                  @SWG\Items(
     *                      @SWG\Property(property="id", type="string", example="1234567890", description="店铺id"),
     *                      @SWG\Property(property="name", type="string", example="我的店铺", description="店铺名"),
     *                      @SWG\Property(property="logo", type="string", example="http://url/logo", description="店铺logo地址"),
     *                      @SWG\Property(property="today_profit", type="double", example=1.23, description="店铺今日收益"),
     *                      @SWG\Property(property="total_profit", type="double", example=1.23, description="店铺总收益"),
     *                      @SWG\Property(property="status", type="integer", example=1, description="店铺状态 0=正常 2=冻结"),
     *                  )
     *                  ),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function my_lists(Request $request)
    {
        $user = $this->auth->user();
        $data = [];
        $query = $user->shop()->whereIn("status", [Shop::STATUS_NORMAL, Shop::STATUS_FREEZE]);
        $count = (int)$query->count();
        if ($request->offset) {
            $query->where((new Shop)->getTable() . ".id", "<", Shop::decrypt($request->offset));
        }
        $query->limit($request->input("limit", 20))->orderBy("ID", "DESC");
        foreach ($query->get() as $_shop) {
            /* @var $_shop Shop */
            $data[] = [
                'id' => $_shop->en_id(),
                'name' => $_shop->name,
                'logo' => $_shop->logo,
                'today_profit' => (double)$_shop->totalProfit([["updated_at", ">=", date("Y-m-d")]]),
                'total_profit' => (double)$_shop->totalProfit(),
                'status' => (int)$_shop->status
            ];
        }
        return $this->json(['count' => $count, 'data' => $data]);
    }

    /**
     * @SWG\Get(
     *   path="/shop/detail/{id}",
     *   summary="店铺详情",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="member_size",
     *     in="query",
     *     description="成员数目",
     *     required=false,
     *     type="integer"
     *   ),
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
     *                  @SWG\Property(property="id", type="string", example="1234567",description="店铺id"),
     *                  @SWG\Property(property="name", type="string", example="我的店铺", description="店铺名"),
     *                  @SWG\Property(property="use_link", type="boolean", example=0, description="是否开启邀请链接 0=关闭 1=开启"),
     *                  @SWG\Property(property="active", type="boolean", example=1, description="是否开启交易  0=关闭 1=开启"),
     *                  @SWG\Property(property="members", type="array", description="成员列表",
     *                  @SWG\Items(
     *                      @SWG\Property(property="name", type="string", example="noname", description="成员名"),
     *                      @SWG\Property(property="avatar", type="string", example="http://url/logo", description="成员头像"),)
     *                  ),
     *                  @SWG\Property(property="members_count", type="integer", example=20, description="成员总数"),
     *                  @SWG\Property(property="platform_fee", type="double", example=9.9, description="平台交易费"),
     *                  @SWG\Property(property="guild_commission", type="double", example=9.9, description="最多佣金数"),
     *                  @SWG\Property(property="rate", type="double", example=9.9, description="单机"),
     *                  @SWG\Property(property="percent", type="double", example=9.9, description="抽水比例 百分比数"),
     *                  @SWG\Property(property="created_at", type="integer", example=1514949735, description="创建时间戳"),
     *                  @SWG\Property(property="logo", type="string", example="url", description="店铺logo"),
     *                  @SWG\Property(property="is_manager", type="boolean", example=0, description="是否为群主  0=否 1=是"),
     *                  @SWG\Property(property="is_member", type="boolean", example=0, description="是否为群成员  0=否 1=是"),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function detail($id, Request $request)
    {
        $user = $this->auth->user();

        $member_size = $request->input('member_size', 5);
        $shop = Shop::findByEnId($id);
        if (!$shop || $shop->status != Shop::STATUS_NORMAL) {
            if ($shop && $shop->status == Shop::STATUS_FREEZE) {
                return $this->json([], trans("api.shop_freeze"), 0);
            } else {
                return $this->json([], trans("api.error_shop_status"), 0);
            }
        }
        /* @var $shop Shop */

        $is_manager = $shop->manager_id == $user->id ? true : false;
        $is_member = ShopUser::where('user_id', $user->id)->where("shop_id", $shop->id)->count() > 0;
        if (!$is_manager && !$is_member) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        $members = [];
        foreach ($shop->users()->limit($member_size)->get() as $_user) {
            /* @var $_user User */
            $members[] = [
//                'id' => (int)$_user->id,
                'name' => $_user->name,
                'avatar' => $_user->avatar,

            ];
        }

        if ($is_manager) {
            $data = [
                'id' => $shop->en_id(),
                'name' => $shop->name,
                'use_link' => $shop->use_link ? 1 : 0,
                'active' => $shop->active ? 1 : 0,
                'members' => $members,
                'members_count' => (int)$shop->users()->count(),
                'platform_fee' => (double)config("platform_fee_percent"),
                'guild_commission' => (double)config("guild_commission", 0),
                'rate' => (double)$shop->price,
                'percent' => (double)$shop->fee,
                'created_at' => strtotime($shop->created_at),
                'logo' => $shop->logo,
                'is_manager' => $is_manager ? 1 : 0,
                'is_member' => $is_member ? 1 : 0,
            ];
        } else {
            $data = [
                'id' => $shop->en_id(),
                'name' => $shop->name,
                'members' => $members,
                'members_count' => (int)$shop->users()->count(),
                'rate' => (double)$shop->price,
                'created_at' => strtotime($shop->created_at),
                'logo' => $shop->logo,
                'is_manager' => $is_manager ? 1 : 0,
                'is_member' => $is_member ? 1 : 0,
            ];
        }
        return $this->json($data);
    }

    /**
     * @SWG\Get(
     *   path="/shop/summary/{id}",
     *   summary="店铺摘要（游客）",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="店铺id",
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
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(property="id", type="string", example="1234567",description="店铺id"),
     *                  @SWG\Property(property="name", type="string", example="我的店铺", description="店铺名"),
     *                  @SWG\Property(property="members_count", type="integer", example=20, description="成员总数"),
     *                  @SWG\Property(property="platform_fee", type="double", example=9.9, description="平台交易费"),
     *                  @SWG\Property(property="created_at", type="integer", example=1514949735, description="创建时间戳"),
     *                  @SWG\Property(property="logo", type="string", example="url", description="店铺logo"),
     *                  @SWG\Property(property="manager", type="string", example="noname", description="群主名"),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function shop_summary($id) {
        $shop = Shop::findByEnId($id);
        if (!$shop || $shop->status != Shop::STATUS_NORMAL) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        $data = [
            'id' => $shop->en_id(),
            'name' => $shop->name,
            'members_count' => (int)$shop->users()->count(),
            'created_at' => strtotime($shop->created_at),
            'logo' => $shop->logo,
            'manager' => $shop->manager->name
        ];
        return $this->json($data);
    }

    /**
     * @SWG\Get(
     *   path="/shop/members/{id}",
     *   summary="店铺成员详情",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     description="成员数目",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="offset",
     *     in="query",
     *     description="最后一条记录的id，默认0",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="keyword",
     *     in="query",
     *     description="搜索关键字",
     *     required=false,
     *     type="string"
     *   ),
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
     *                  @SWG\Property(property="count", type="integer", example=20,description="总数"),
     *                  @SWG\Property(
     *                      property="data",
     *                      type="array",
     *                  @SWG\Items(
     *                      @SWG\Property(property="id", type="string", example="1234567890", description="成员id"),
     *                      @SWG\Property(property="name", type="string", example="我的店铺", description="成员名"),
     *                      @SWG\Property(property="avatar", type="string", example="http://url/logo", description="成员头像地址"),
     *                      @SWG\Property(property="mobile", type="string", example="1333333333",description="用户手机号"),
     *                  )
     *                  ),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function members($id, Request $request)
    {
        $user = $this->auth->user();
        $shop = Shop::findByEnId($id);
        if (!$shop || $shop->status != Shop::STATUS_NORMAL) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        $is_manager = $shop->manager_id == $user->id ? true : false;
        $is_member = ShopUser::where('user_id', $user->id)->where("shop_id", $shop->id)->count() > 0;
        if (!$is_manager && !$is_member) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        if ($request->keyword) {
            $query = $shop->users()->where("name", 'like', '%'.$request->keyword.'%');
        } else {
            $query = $shop->users();
        }
        $count = $query->count();
        $members = [];
        $query->orderBy((new ShopUser)->getTable().".id");
        if ($request->offset) {
            $shop_user_id = ShopUser::where("shop_id", $shop->id)->where("user_id", User::decrypt($request->offset))->value("id");
            if ($shop_user_id) {
                $query->where((new ShopUser)->getTable().".id", ">", $shop_user_id);
            }
        }
        foreach ($query->limit($request->input("limit", 20))->get() as $_user) {
            /* @var $_user User */
            $members[] = [
                'id' => (string)$_user->en_id(),
                'name' => $_user->name,
                'avatar' => $_user->avatar,
                'mobile' => $_user->mobile,
            ];
        }
        return $this->json([
            'count' => (int)$count,
            'members' => $members,
        ]);
    }

    /**
     * @SWG\Post(
     *   path="/shop/members/{shop_id}/delete/{user_id}",
     *   description="url中带用户id则为删除该单个用户，url中不带用户id并post用户id数组则为删除多个",
     *   summary="删除店铺成员",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="shop_id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="query",
     *     description="用户id数组",
     *     required=false,
     *     type="array",
     *     @SWG\Items(
     *      type="string"
     *     )
     *   ),
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="path",
     *     description="用户id",
     *     required=false,
     *     type="integer"
     *   ),
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
     *                  type="object"
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function member_delete($shop_id, $user_id = null, Request $request) {
        $user = $this->auth->user();
        $shop = Shop::findByEnId($shop_id);
        $members = [];
        if ($user_id) {
            $members[] = User::findByEnId($user_id);
        } else if ($request->user_id) {
            $user_ids = [];
            foreach ($request->user_id as $_user_id) {
                $user_ids[] = User::decrypt($_user_id);
            }
            $members = User::whereIn('id', $user_ids)->get();
        } else {
            return $this->json([], trans("api.error"), 0);

        }
        if (!$shop || $shop->status != Shop::STATUS_NORMAL) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        if ($shop->manager_id != $user->id) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        $member_ids = [];
        foreach ($members as $member) {
            if ($member->id == $user->id) {
                return $this->json([], trans("api.cannot_delete_self"), 0);
            }
            $member_ids[] = $member->id;
        }
        if ($member_ids) {
            ShopUser::whereIn("user_id", $member_ids)->where("shop_id", $shop->id)->delete();
            Artisan::queue('shop:logo', [
                '--id' => $shop->id
            ])->onQueue('shop_logo');
        } else {
            return $this->json([], trans("api.error"), 0);
        }
        return $this->json();
    }
    
    /**
     * @SWG\Post(
     *   path="/shop/close/{id}",
     *   summary="解散店铺",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="店铺id",
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
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object"
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function close($id) {
        $user = $this->auth->user();
        $shop = Shop::findByEnId($id);
        if (!$shop || $shop->status != Shop::STATUS_NORMAL) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        if ($shop->status == Shop::STATUS_CLOSED) {
            return $this->json([], trans("api.shop_closed"), 0);
        }
        if ($shop->manager_id != $user->id) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        if ($shop->container->balance > 0 || $shop->active || Transfer::where("shop_id", $shop->id)->where("status", "!=", 3)->count() > 0) {
            return $this->json([], trans("api.shop_cannot_close"), 0);
        }
        $shop->status = Shop::STATUS_CLOSED;
        $shop->save();
        return $this->json();
    }

    /**
     * @SWG\Post(
     *   path="/shop/quit/{id}",
     *   summary="退出店铺",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="店铺id",
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
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object"
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function quit($id) {
        $user = $this->auth->user();

        $shop = Shop::findByEnId($id);
        if (!$shop || $shop->status != Shop::STATUS_NORMAL) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        ShopUser::where('shop_id', $shop->id)->where("user_id", $user->id)->delete();
        Artisan::queue('shop:logo', [
            '--id' => $shop->id
        ])->onQueue('shop_logo');
        return $this->json();
    }

    /**
     * @SWG\Post(
     *   path="/shop/update/{id}",
     *   summary="更新店铺",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="use_link",
     *     in="formData",
     *     description="是否开启邀请链接",
     *     required=false,
     *     type="boolean"
     *   ),
     *   @SWG\Parameter(
     *     name="active",
     *     in="formData",
     *     description="是否开启交易",
     *     required=false,
     *     type="boolean"
     *   ),
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     description="店铺名",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="percent",
     *     in="formData",
     *     description="抽水比例",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="rate",
     *     in="formData",
     *     description="单价",
     *     required=false,
     *     type="string"
     *   ),
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
     *                  type="object"
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'max:10',
            'rate' => 'regex:/^\d{0,5}(\.\d{1})?$/|numeric|between:0.1,99999',
            'percent' => 'integer|between:0,100',
        ],[
        'name.max'=>'公会名称不能超过10个字符',
        'rate.regex'=>'倍率请填写0.1到99999之间数字',
        'rate.between' => '倍率请填写0.1到99999之间数字',
        'rate.numeric' => '倍率请填写0.1到99999之间数字',
        'percent.integer'=>'佣金费率必须为0-100的整数',
        'percent.between'=>'佣金费率必须为0-100的整数'
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $user = $this->auth->user();

        $shop = Shop::findByEnId($id);
        if (!$shop || $shop->status != Shop::STATUS_NORMAL) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        if ($shop->manager_id != $user->id) {
            return $this->json([], trans("api.error_shop_perm"), 0);
        }
        if ($request->name !== null) {
            $name = trim($request->name);
            if (strlen($name) > 0) {
                $shop->name = $name;
            }
        }
        if ($request->use_link !== null) {
            $shop->use_link = $request->use_link ? 1 : 0;
        }

        if ($request->active !== null) {
            $shop->active = $request->active ? 1 : 0;
        }

        if ($request->rate !== null) {
            $shop->price = $request->rate;
        }

        if ($request->percent !== null) {
            if ($request->percent > config("guild_commission", 0)) {
                return $this->json([], trans("api.error_shop_percent"), 0);
            }
            $shop->fee = $request->percent;
        }
        $shop->save();
        return $this->json();
    }

    /**
     * @SWG\Get(
     *   path="/shop/qrcode/{id}",
     *   summary="店铺二维码",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="size",
     *     in="query",
     *     description="二维码尺寸",
     *     required=false,
     *     type="integer"
     *   ),
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
     *                  @SWG\Property(property="url", type="string", example="http://url",description="二维码链接"),
     *                  @SWG\Property(property="share_url", type="string", example="http://url",description="分享链接"),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function qrcode($id, Request $request) {
//        $validator = Validator::make($request->all(), [
//            'width' => 'required',
//            'height' => 'required',
//        ]);
//
//        if ($validator->fails()) {
//            return $this->json([], $validator->errors()->first(), 0);
//        }
        $size = $request->input("size", 200);
        $shop = Shop::findByEnId($id);
        if (!$shop || $shop->status != Shop::STATUS_NORMAL) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        $user = $this->auth->user();
        /* @var $user User */
        $url = url(sprintf("/#/share/?shopId=%s&userId=%s", $shop->en_id(), $user->en_id()));
        $filename = md5($url."_".$size);
        $path = 'qrcode/'.$filename.'.png';
        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, QrCode::format('png')->size($size)->margin(1)->generate($url));
        }
        return $this->json(['url' => url('storage/'.$path), 'share_url' => $url]);
    }

    /**
     * @SWG\Post(
     *   path="/shop/join/{id}",
     *   summary="加入店铺",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="店铺id",
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
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object"
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function join($id) {
        $user = $this->auth->user();
        $shop = Shop::findByEnId($id);
        if (!$shop || $shop->status != Shop::STATUS_NORMAL) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        if (!$shop->use_link) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        if (ShopUser::where("user_id", $user->id)->where("shop_id", $shop->id)->count() > 0) {
            return $this->json([], trans("api.shop_exist_member"), 0);
        }
        //#todo
        Notification::send($shop->manager, new ShopApply(['user_id' => $user->id, 'shop_id' => $shop->id, 'type' => ShopApply::TYPE_APPLY]));
        return $this->json();
    }

    /**
     * @SWG\Get(
     *   path="/shop/account/{id}",
     *   summary="店铺帐户信息",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="店铺id",
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
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(property="balance", type="double", example=9.9,description="店铺余额"),
     *                  @SWG\Property(property="today_profit", type="double", example=9.9,description="店铺今日收益"),
     *                  @SWG\Property(property="total_profit", type="double", example=9.9,description="店铺总收益"),
     *                  @SWG\Property(property="last_profit", type="double", example=9.9,description="店铺昨日收益"),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function account($id) {
        $user = $this->auth->user();
        $shop = Shop::findByEnId($id);
        if (!$shop || $shop->status != Shop::STATUS_NORMAL) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        if ($shop->manager_id != $user->id) {
            return $this->json([], trans("api.error_shop_perm"), 0);
        }
        return $this->json([
            'balance' => (double)$shop->container->balance,
            'today_profit' => (double)$shop->totalProfit([["updated_at", ">=", date("Y-m-d")]]),
            'total_profit' => (double)$shop->totalProfit(),
            'last_profit' => (double)$shop->totalProfit([["updated_at", ">=", date("Y-m-d", strtotime('-1 day'))],["updated_at", "<", date("Y-m-d")]])
        ]);
    }

    /**
     * @SWG\Get(
     *   path="/shop/profit",
     *   summary="所有店铺收益信息",
     *   tags={"店铺"},
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
     *                  @SWG\Property(property="profit", type="double", example=9.9,description="我的店铺总收益"),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function profit() {
        $user = $this->auth->user();

        return $this->json(['profit' => (double)$user->shop_tips()->where((new TipRecord)->getTable().".status", TipRecord::USEABLE_STATUS)->sum('amount')]);
    }

    /**
     * @SWG\Get(
     *   path="/shop/messages",
     *   summary="店铺消息",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="offset",
     *     in="path",
     *     description="最后记录id",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="limie",
     *     in="path",
     *     description="数目",
     *     required=false,
     *     type="integer"
     *   ),
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
     *                  @SWG\Property(property="count", type="integer", example=20,description="总数"),
     *                  @SWG\Property(
     *                      property="data",
     *                      type="array",
     *                  @SWG\Items(
     *                  @SWG\Property(property="user_avatar", type="string", example="url",description="用户头像"),
     *                  @SWG\Property(property="user_name", type="string", example="noname",description="用户名"),
     *                  @SWG\Property(property="shop_name", type="string", example="我的店铺",description="店铺名"),
     *                  @SWG\Property(property="id", type="string", example="12312312",description="消息id"),
     *                  @SWG\Property(property="type", type="integer", example=0,description="消息类型 0=申请 1=邀请"),
     *                  @SWG\Property(property="created_at", type="integer", example=15200000,description="创建时间戳"),
     *                  )
     *                  ),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function messages(Request $request) {
        $user = $this->auth->user();

        $data = [];
        $query = $user->unreadNotifications()->where("type", "App\Notifications\ShopApply");
        $count = (int)$query->count();
        if ($request->offset) {
            $last = Notice::find($request->offset);
            if ($last) {
                $query->where("uid", "<", $last->uid);
            }
        }
        $query->limit($request->input("limit", 20))->orderBy("uid","DESC");
        foreach ($query->get() as $notification) {
            try {
                if ($notification->data['type'] == ShopApply::TYPE_INVITE) {
                    $user = User::find($notification->data['invite_id']);
                } else {
                    $user = User::find($notification->data['user_id']);
                }
                $shop = Shop::find($notification->data['shop_id']);
                if ($user && $shop) {
                    $data[] = [
                        'user_avatar' => $user->avatar,
                        'user_name' => $user->name,
                        'shop_name' => $shop->name,
                        'id' => $notification->id,
                        'type' => (int)$notification->data['type'],
                        'created_at' => strtotime($notification->created_at)
                    ];
                }
            } catch (\Exception $e){}
        }
        return $this->json(['count' => $count, 'data' => $data]);
    }

    /**
     * @SWG\Get(
     *   path="/shop/messages/count",
     *   summary="店铺未读消息数",
     *   tags={"店铺"},
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
     *                  @SWG\Property(property="count", type="integer", example=100,description="店铺未读消息数"),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function messages_count() {
        $user = $this->auth->user();

        return $this->json(['count' => (int)$user->unreadNotifications()->where("type", "App\Notifications\ShopApply")->count()]);
    }

    /**
     * @SWG\Post(
     *   path="/shop/agree",
     *   summary="店铺同意消息",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="消息id，不传同意全部",
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
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object"
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function agree(Request $request) {
        $user = $this->auth->user();
        $shop_ids = [];
        if ($request->id) {
            $notification = $user->unreadNotifications()->where("id", $request->id)->first();
            if ($notification) {
                $notification->markAsRead();
                try {
                    $user = User::find($notification->data['user_id']);
                    $shop = Shop::find($notification->data['shop_id']);
                    if (!$shop || $shop->status != Shop::STATUS_NORMAL) {
                        return $this->json([], trans("api.error_shop_status"), 0);
                    }
                    $exist = ShopUser::where("user_id", $user->id)->where("shop_id", $shop->id)->first();
                    if (!$exist) {
                        $shop_user = new ShopUser();
                        $shop_user->shop_id = $shop->id;
                        $shop_user->user_id = $user->id;
                        $shop_user->save();
                        $shop_ids[] = $shop->id;
                    }
                } catch (\Exception $e){}
            }
        } else {
            foreach ($user->unreadNotifications as $notification) {
                try {
                    $user = User::find($notification->data['user_id']);
                    $shop = Shop::find($notification->data['shop_id']);
                    if (!$shop || $shop->status != Shop::STATUS_NORMAL) {
                        continue;
                    }
                    $exist = ShopUser::where("user_id", $user->id)->where("shop_id", $shop->id)->first();
                    if (!$exist) {
                        $shop_user = new ShopUser();
                        $shop_user->shop_id = $shop->id;
                        $shop_user->user_id = $user->id;
                        $shop_user->save();
                        $shop_ids[] = $shop->id;
                    }
                } catch (\Exception $e){}
                $notification->markAsRead();
            }
//            $user->unreadNotifications->markAsRead();
        }
        if ($shop_ids) {
            Artisan::queue('shop:logo', [
                '--id' => array_unique($shop_ids)
            ])->onQueue('shop_logo');
        }
        return $this->json();
    }

    /**
     * @SWG\Post(
     *   path="/shop/ignore",
     *   summary="店铺忽略消息",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="消息id,不传忽略全部",
     *     required=false,
     *     type="integer"
     *   ),
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
     *                  type="object"
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function ignore(Request $request) {
        $user = $this->auth->user();
        if ($request->id) {
            $message = $user->unreadNotifications()->where("id", $request->id)->first();
            if ($message) {
                $message->markAsRead();
            }
        } else {
            foreach ($user->unreadNotifications as $notification) {
                $notification->markAsRead();
            }
        }
        return $this->json();
    }

    /**
     * @SWG\Post(
     *   path="/shop/invite/{shop_id}/{user_id}",
     *   summary="店铺邀请成员",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="shop_id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="path",
     *     description="被邀请人id",
     *     required=true,
     *     type="string"
     *   ),
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
     *                  type="object"
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function invite($shop_id, $user_id) {
        $shop = Shop::findByEnId($shop_id);
        if (!$shop || $shop->status != Shop::STATUS_NORMAL) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        $user = User::findByEnId($user_id);
        if (!$user || $user->status == User::STATUS_BLOCK) {
            return $this->json([], trans("api.user_unexist"), 0);

        }
        if (ShopUser::where("user_id", $user->id)->where("shop_id", $shop->id)->count() > 0) {
            return $this->json([], trans("api.shop_exist_member"), 0);
        }
        Notification::send($user, new ShopApply(['user_id' => $user->id, 'invite_id' => $this->auth->user()->id, 'shop_id' => $shop->id, 'type' => ShopApply::TYPE_INVITE]));
//        $user = $this->auth->user();
//        if ($request->id) {
//            $message = $user->unreadNotifications()->where("id", $request->id)->first();
//            if ($message) {
//                $message->markAsRead();
//            }
//        } else {
//            $user->unreadNotifications->markAsRead();
//        }
        return $this->json();
    }

    /**
     * @SWG\Get(
     *   path="/shop/user/search",
     *   summary="手机号搜索用户",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="mobile",
     *     in="path",
     *     description="手机号",
     *     required=true,
     *     type="string"
     *   ),
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
     *                  @SWG\Property(property="avatar", type="string", example="url",description="用户头像"),
     *                  @SWG\Property(property="name", type="string", example="noname",description="用户名"),
     *                  @SWG\Property(property="id", type="string", example="12312312",description="用户id"),
     *                  @SWG\Property(property="mobile", type="string", example="1333333333",description="用户手机号"),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function user_search(Request $request) {
        $validator = Validator::make($request->all(),
            ['mobile' => 'bail|required']
        );
        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $user = User::where("mobile", $request->mobile)->first();
        if (!$user) {
            return $this->json([], 'ok', 1);
        }
        return $this->json([
            'avatar' => $user->avatar,
            'name' => $user->name,
            'id' => $user->en_id(),
            'mobile' => $user->mobile,
        ]);
    }

    /**
     * @SWG\Post(
     *   path="/shop/transfer/{shop_id}",
     *   summary="店铺转账到个人帐户",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="shop_id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="amount",
     *     in="formData",
     *     description="金额",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="支付密码",
     *     required=true,
     *     type="string"
     *   ),
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
     *                  type="object"
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function transfer($shop_id, Request $request) {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|min:0',
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $shop = Shop::findByEnId($shop_id);
        if (!$shop || $shop->status != Shop::STATUS_NORMAL) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        if ($shop->container->balance < $request->amount) {
            return $this->json([], trans("api.error_balance"), 0);
        }
        if (!$shop->manager) {
            return $this->json([], trans("error_shop_manager"), 0);
        }
        try {
            if (!$shop->manager->check_pay_password($request->password)) {
                return $this->json([], trans("api.error_pay_password"),0);
            }
        } catch (\Exception $e) {
            return $this->json([], $e->getMessage(),0);
        }
        $record = new ShopFund();
        $record->shop_id = $shop->id;
        $record->user_id = $shop->manager->id;
        $record->type = ShopFund::TYPE_TRANAFER;
        $record->mode = ShopFund::MODE_OUT;
        $record->amount = $request->amount;
        $record->balance = $shop->container->balance - $request->amount;
        $record->status = ShopFund::STATUS_SUCCESS;

        $user_record = new UserFund();
        $user_record->user_id = $shop->manager->id;
        $user_record->type = UserFund::TYPE_SHOP_TRANSFER;
        $user_record->mode = UserFund::MODE_IN;
        $user_record->amount = $request->amount;
//        $user_record->balance = $user->container->balance - $request->amount;
        $user_record->status = UserFund::STATUS_SUCCESS;
        try {
            $record->save();
            $user_record->save();
            $shop->container->transfer($shop->manager->container, $request->amount, 0, false, false);
        } catch (\Exception $e){
            Log::info("shop transfer error:".$e->getMessage());
            return $this->json([], 'error', 0);
        }
        
        return $this->json();
    }

    /**
     * @SWG\Post(
     *   path="/shop/transfer/{shop_id}/{user_id}",
     *   summary="店铺转账到成员帐户",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="shop_id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="amount",
     *     in="formData",
     *     description="金额",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="path",
     *     description="成员id",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="remark",
     *     in="formData",
     *     description="备注",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="支付密码",
     *     required=true,
     *     type="string"
     *   ),
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
     *                  type="object"
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function transfer_member($shop_id, $user_id, Request $request) {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|min:0',
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $shop = Shop::findByEnId($shop_id);
        if (!$shop || $shop->status != Shop::STATUS_NORMAL) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        if ($shop->container->balance < $request->amount) {
            return $this->json([], trans("api.error_balance"), 0);
        }
        try {
            if (!$shop->manager->check_pay_password($request->password)) {
                return $this->json([], trans("api.error_pay_password"),0);
            }
        } catch (\Exception $e) {
            return $this->json([], $e->getMessage(),0);
        }
        $member = User::findByEnId($user_id);
        $record = new ShopFund();
        $record->shop_id = $shop->id;
        $record->type = ShopFund::TYPE_TRANAFER_MEMBER;
        $record->user_id = $member->id;
        $record->mode = ShopFund::MODE_OUT;
        $record->amount = $request->amount;
        $record->remark = $request->remark;
        $record->balance = $shop->container->balance - $request->amount;
        $record->status = ShopFund::STATUS_SUCCESS;

        $user_record = new UserFund();
        $user_record->user_id = $member->id;
        $user_record->type = UserFund::TYPE_SHOP_TRANSFER;
        $user_record->mode = UserFund::MODE_IN;
        $user_record->amount = $request->amount;
//        $user_record->balance = $user->container->balance - $request->amount;
        $user_record->status = UserFund::STATUS_SUCCESS;
        try {
            $record->save();
            $user_record->save();
            $shop->container->transfer($member->container, $request->amount, 0, false, false);
        } catch (\Exception $e){
            Log::info("shop transfer member error:".$e->getMessage());
            return $this->json([], 'error', 0);
        }
        return $this->json();
    }

    /**
     * @SWG\Get(
     *   path="/shop/transfer/records/{shop_id}",
     *   summary="店铺帐单明细",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="type",
     *     in="query",
     *     description="类型",
     *     required=false,
     *     type="array",
     *     @SWG\Items(
     *      type="integer"
     *     )
     *   ),
     *   @SWG\Parameter(
     *     name="start",
     *     in="query",
     *     description="结束日期",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     description="数目",
     *     required=false,
     *     type="number"
     *   ),
     *   @SWG\Parameter(
     *     name="offset",
     *     in="query",
     *     description="上一次记录的最后一条ID,默认0",
     *     required=false,
     *     type="string"
     *   ),
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
     *                  @SWG\Property(property="count", type="integer", example=20,description="总数"),
     *                  @SWG\Property(
     *                      property="data",
     *                      type="array",
     *                  @SWG\Items(
     *                  @SWG\Property(property="id", type="string", example="12345676789",description="记录id"),
     *                  @SWG\Property(property="type", type="integer", example=1,description="帐单类别 0=转账给个人 1=转账给成员 2=从个人转账"),
     *                  @SWG\Property(property="mode", type="integer", example=1,description="收入支出 0=收入 1=支出"),
     *                  @SWG\Property(property="amount", type="double", example=9.9,description="金额"),
     *                  @SWG\Property(property="created_at", type="integer", example=152000000,description="创建时间戳"),
     *                  )
     *                  ),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function transfer_records($shop_id, Request $request) {
        $data = [];
        $user = $this->auth->user();
        $shop = Shop::findByEnId($shop_id);
        if (!$shop || $shop->status != Shop::STATUS_NORMAL || $shop->manager_id != $user->id) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        $query = $shop->funds();
        if ($request->type !== null) {
            $types = [];
            if (is_array($request->type)) {
                $types = $request->type;
            } else {
                $types[] = $request->type;
            }
            $query->whereIn("type", $types);
        }
        if ($request->start) {
            $start = date("Y-m-d H:i:s", strtotime($request->start." +1 month"));
            $query->where("created_at", "<", $start);
        }
        $count = $query->count();
        $query->orderBy('id',  'DESC')->limit($request->input('limit', 20));
        if ($request->offset) {
            $query->where("id", "<", ShopFund::decrypt($request->offset));
        }
        /* @var $user User */
        foreach ($query->get() as $_fund) {
            $data[] = [
                'id' => $_fund->en_id(),
                'type' => (int)$_fund->type,
                'mode' => (int)$_fund->mode,
                'amount' => (double)$_fund->amount,
                'created_at' => strtotime($_fund->created_at)
            ];
        }
        return $this->json(['count' => (int)$count, 'data' => $data]);
    }

    /**
     * @SWG\Get(
     *   path="/shop/transfer/records/detail/{id}",
     *   summary="帐单详情",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="帐单id",
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
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(property="id", type="string", example="12345676789",description="记录id"),
     *                  @SWG\Property(property="type", type="integer", example=1,description="帐单类别 0=转账给个人 1=转账给成员 2=从个人转账"),
     *                  @SWG\Property(property="mode", type="integer", example=1,description="收入支出 0=收入 1=支出"),
     *                  @SWG\Property(property="amount", type="double", example=9.9,description="金额"),
     *                  @SWG\Property(property="created_at", type="integer", example=152000000,description="创建时间戳"),
     *                  @SWG\Property(property="no", type="string", example="123123",description="交易单号"),
     *                  @SWG\Property(property="remark", type="string", example="xxxx",description="备注"),
     *                  @SWG\Property(property="balance", type="double", example=9.9,description="交易后余额"),
     *                  @SWG\Property(property="user_name", type="string", example="noname",description="转账帐户"),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function record_detail($id) {
        $user = $this->auth->user();

        $fund = ShopFund::findByEnId($id);
        if (!$fund || !$fund->shop || $fund->shop->status != Shop::STATUS_NORMAL || $fund->shop->manager_id != $user->id) {
            return $this->json([], trans("error_fund"), 0);
        }
        return $this->json([
            'id' => $fund->en_id(),
            'type' => (int)$fund->type,
            'mode' => (int)$fund->mode,
            'amount' => $fund->amount,
            'created_at' => strtotime($fund->created_at),
            'no' => (string)$fund->en_id(),
            'remark' => (string)$fund->remark,
            'user_name' => $fund->user ? $fund->user->mobile : '',
            'balance' => $fund->balance
        ]);
    }

    /**
     * @SWG\Get(
     *   path="/shop/transfer/records/month/{shop_id}",
     *   summary="帐单月数据",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="shop_id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="type",
     *     in="query",
     *     description="类型",
     *     required=false,
     *     type="array",
     *     @SWG\Items(
     *      type="integer"
     *     )
     *   ),
     *   @SWG\Parameter(
     *     name="month",
     *     in="formData",
     *     description="月(2017-12形式)",
     *     required=true,
     *     type="string"
     *   ),
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
     *                  @SWG\Property(property="in", type="double", example=123.4,description="店铺当月收入总数"),
     *                  @SWG\Property(property="out", type="double", example=123.4,description="店铺当月支出总数"),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function month_data($shop_id, Request $request) {
        $validator = Validator::make($request->all(),
            ['month' => 'required|regex:/^\d{4}-\d{2}$/']
        );
        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $shop = Shop::findByEnId($shop_id);
        if (!$shop || $shop->status != Shop::STATUS_NORMAL) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        if ($request->type) {
            $in_amount = (double)ShopFund::where("shop_id", $shop->id)->whereIn("type", $request->type)->where("created_at", ">=", date("Y-m-01", strtotime($request->month)))->where("created_at", "<", date("Y-m-01", strtotime($request->month . " +1 month")))->where("mode", ShopFund::MODE_IN)->sum("amount");
            $out_amount = (double)ShopFund::where("shop_id", $shop->id)->whereIn("type", $request->type)->where("created_at", ">=", date("Y-m-01", strtotime($request->month)))->where("created_at", "<", date("Y-m-01", strtotime($request->month . " +1 month")))->where("mode", ShopFund::MODE_OUT)->sum("amount");
        } else {
            $in_amount = (double)ShopFund::where("shop_id", $shop->id)->where("created_at", ">=", date("Y-m-01", strtotime($request->month)))->where("created_at", "<", date("Y-m-01", strtotime($request->month . " +1 month")))->where("mode", ShopFund::MODE_IN)->sum("amount");
            $out_amount = (double)ShopFund::where("shop_id", $shop->id)->where("created_at", ">=", date("Y-m-01", strtotime($request->month)))->where("created_at", "<", date("Y-m-01", strtotime($request->month . " +1 month")))->where("mode", ShopFund::MODE_OUT)->sum("amount");
        }

        return $this->json(['in' => $in_amount, 'out' => $out_amount]);
    }

    /**
     * @SWG\Get(
     *   path="/shop/tips",
     *   summary="我的赏金",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="offset",
     *     in="query",
     *     description="上次记录ID",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="shop_id",
     *     in="query",
     *     description="店铺id",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     description="数目",
     *     required=false,
     *     type="integer"
     *   ),
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
     *                  @SWG\Property(property="count", type="integer", example=20,description="总数"),
     *                  @SWG\Property(
     *                      property="data",
     *                      type="array",
     *                  @SWG\Items(
     *                      @SWG\Property(property="id", type="string", example="1234567890", description="记录id"),
     *                      @SWG\Property(property="shop_id", type="string", example="1234567890", description="店铺id"),
     *                      @SWG\Property(property="shop_name", type="string", example="店铺名", description="店铺名"),
     *                      @SWG\Property(property="user_name", type="string", example="1234567890", description="用户名"),
     *                      @SWG\Property(property="user_avatar", type="string", example="url", description="用户头像"),
     *                  @SWG\Property(property="mode", type="integer", example=1,description="收入支出 0=收入 1=支出"),
     *                  @SWG\Property(property="amount", type="double", example=9.9,description="金额"),
     *                  @SWG\Property(property="created_at", type="integer", example=152000000,description="创建时间戳"),
     *                  )
     *                  ),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function tips(Request $request) {
        $data = [];
        $user = $this->auth->user();
        if ($request->shop_id) {
            $shop = Shop::findByEnId($request->shop_id);
            if (!$shop || $shop->status != Shop::STATUS_NORMAL || $shop->manager_id != $user->id) {
                return $this->json([], trans("api.error_shop_status"), 0);
            }
            $query = $shop->funds();
        } else {
            $query = $user->shop_funds();

        }
        $query->with(["shop", 'user'])->where((new ShopFund())->getTable().".type", ShopFund::TYPE_TIP);

        if ($request->start) {
            $start = date("Y-m-d H:i:s", strtotime($request->start." +1 month"));
            $query->where("created_at", "<", $start);
        }
        $count = $query->count();
        $query->orderBy((new ShopFund())->getTable().'.id',  'DESC')->limit($request->input('limit', 20));
        if ($request->offset) {
            $query->where((new ShopFund())->getTable().".id", "<", ShopFund::decrypt($request->offset));
        }
        /* @var $user User */
        foreach ($query->get() as $_fund) {
            $data[] = [
                'id' => $_fund->en_id(),
                'shop_id' => $_fund->shop ? $_fund->shop->en_id() : "",
                'shop_name' => $_fund->shop ? $_fund->shop->name : "",
                'user_name' => $_fund->user ? $_fund->user->name : "",
                'user_avatar' => $_fund->user ? $_fund->user->avatar : "",
                'mode' => (int)$_fund->mode,
                'amount' => (double)$_fund->amount,
                'created_at' => strtotime($_fund->created_at)
            ];
        }
        return $this->json(['count' => (int)$count, 'data' => $data]);
    }

    /**
     * @SWG\Get(
     *   path="/shop/settings",
     *   summary="店铺配置",
     *   tags={"店铺"},
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
     *                  @SWG\Property(property="guild_commission", type="double", example=1.2,description="最大佣金数"),
     *                  @SWG\Property(property="price", type="string", example="200.00",description="原价"),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function settings() {
        return $this->json(['guild_commission' => (double)config("guild_commission", 0), 'price' => config("shop_price")]);
    }
}