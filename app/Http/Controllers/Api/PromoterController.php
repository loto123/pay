<?php
/**
 * 推广员功能控制器
 * Author: huangkaixuan
 * Date: 2018/1/12
 * Time: 11:05
 */

namespace App\Http\Controllers\Api;


use App\Agent\Card;
use App\Agent\CardUse;
use App\Agent\PromoterGrant;
use App\User;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PromoterController extends BaseController
{
    /**
     * 推广员转卡
     *
     * @SWG\Post(
     *   path="/promoter/transfer-card",
     *   summary="推广员将卡片转让给其它推广员",
     *   tags={"推广员功能"},
     *   @SWG\Parameter(
     *     name="card_no",
     *     in="formData",
     *     description="要转出的卡号",
     *     required=true,
     *     type="string"
     *   ),
     *     @SWG\Parameter(
     *     name="user_id",
     *     in="formData",
     *     required=true,
     *     type="string",
     *     description="要转出的用户的手机号码",
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
     *                  type="string",
     *                  description="失败返回错误信息"
     *              )
     *          )
     *      )
     * )
     */
    public function transferCard(Request $request)
    {
        //卡片从推广员->推广员

        $validator = Validator::make($request->all(), [
            'user_id' => 'digits:11',
            'card_no' => 'digits:' . Card::CARD_NO_LENGTH
        ], [
            'user_id.digits' => '无效的手机号',
            'card_no.digits' => '无效卡号',
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        $cardId = Card::recover_id($request->card_no);
        $card = Card::where('promoter_id', Auth::id())->find($cardId);
        if (!$card) {
            return $this->json([], '该卡不存在', 0);
        }

        if ($card->is_bound) {
            return $this->json([], '该卡已被使用', 0);
        }

        if ($card->is_frozen) {
            return $this->json([], '该卡被冻结', 0);
        }

        //验证推广员
        $transferTo = User::where('mobile', $request->user_id)->whereHas('roles', function ($query) {
            $query->where('name', '=', PromoterGrant::PROMOTER_ROLE_NAME);
        })->first();

        if (!$transferTo) {
            return $this->json([], '该推广员不存在', 0);
        }

        DB::beginTransaction();
        $commit = false;

        do {
            $updateColumns = ['owner' => $transferTo->getKey(), 'promoter_id' => $transferTo->getKey()];

            if (!Card::where([
                ['promoter_id', Auth::id()],
                ['is_bound', 0],
                ['is_frozen', 0],
                ['id', $cardId]
            ])->update($updateColumns)
            ) {
                break;
            }

            if (!CardUse::insert([
                ['from' => Auth::id(), 'to' => $transferTo->getKey(), 'type' => CardUse::TYPE_TRANSFER, 'card_id' => $cardId]
            ])
            ) {
                break;
            }

            $commit = true;
        } while (false);


        $commit ? DB::commit() : DB::rollBack();
        return $this->json([], $commit ? '转卡成功' : '转卡失败,请稍后重试', (int)$commit);
    }

    /**
     * 推广员绑卡
     * @SWG\Post(
     *   path="/promoter/bind-card",
     *   summary="开通VIP",
     *   tags={"推广员功能"},
     *   @SWG\Parameter(
     *     name="card_no",
     *     in="formData",
     *     description="要绑定的卡号",
     *     required=true,
     *     type="string"
     *   ),
     *     @SWG\Parameter(
     *     name="user_id",
     *     in="formData",
     *     required=true,
     *     type="string",
     *     description="要绑定的代理的手机号码",
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
     *                  type="string",
     *                  description="失败返回错误信息"
     *              )
     *          )
     *      )
     * )
     */
    public function bindCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'digits:11',
            'card_no' => 'digits:' . Card::CARD_NO_LENGTH
        ], [
            'user_id.digits' => '无效的手机号',
            'card_no.digits' => '无效卡号',
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        $cardId = Card::recover_id($request->card_no);
        $card = Card::where('promoter_id', Auth::id())->find($cardId);
        if (!$card) {
            return $this->json([], '该卡不存在', 0);
        }

        if ($card->is_bound) {
            return $this->json([], '该卡已被使用', 0);
        }

        if ($card->is_frozen) {
            return $this->json([], '该卡被冻结', 0);
        }

        //验证代理身份
        $bindTo = User::where('mobile', $request->user_id)->whereHas('roles', function ($query) {
            $query->where('name', '=', 'agent');
        })->first();

        if (!$bindTo) {
            return $this->json([], '该代理不存在', 0);
        }

        //只有更高分润比例卡可以绑定
        if ($bindTo->myVipProfitShareRate() >= $card->type->percent) {
            return $this->json([], '有生效中的vip卡,只能绑定更高级卡片', 0);
        }

        DB::beginTransaction();
        $commit = false;

        do {
            $updateColumns = ['is_bound' => 1, 'owner' => $bindTo->getKey()];
            if ($card->type->valid_days > 0) {
                $updateColumns['expired_at'] = date('Y-m-d H:i:s', time() + $card->type->valid_days * 60 * 60 * 24);
            }

            if (!Card::where([
                ['owner', Auth::id()],
                ['is_bound', 0],
                ['is_frozen', 0],
                ['id', $cardId]
            ])->update($updateColumns)
            ) {
                break;
            }

            if (!CardUse::insert([
                ['from' => Auth::id(), 'to' => $bindTo->getKey(), 'type' => CardUse::TYPE_BINDING, 'card_id' => $cardId]
            ])
            ) {
                break;
            }

            $commit = true;
        } while (false);

        $commit ? DB::commit() : DB::rollBack();
        return $this->json([], $commit ? '绑定成功' : '绑定失败,请稍后重试', (int)$commit);
    }

    /**
     * 授权推广员
     * @SWG\Post(
     *   path="/promoter/grant",
     *   summary="给用户授权",
     *   tags={"推广员功能"},
     *     @SWG\Parameter(
     *     name="user_id",
     *     in="formData",
     *     required=true,
     *     type="string",
     *     description="要开通推广员权限的手机号码",
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
     *                  type="string",
     *                  description="失败返回错误信息"
     *              )
     *          )
     *      )
     * )
     */
    public function grant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'digits:11',
        ]);

        if ($validator->fails()) {
            return $this->json([], '无效的手机号', 0);
        }
        $toGrant = User::where('mobile', $request->user_id)->whereDoesntHave('roles', function ($query) {
            $query->where('name', '=', PromoterGrant::PROMOTER_ROLE_NAME);
        })->first();

        if (!$toGrant) {
            return $this->json([], '用户不存在或已经是推广员', 0);
        }
        if (Auth::user()->grantPromoterTo($toGrant)) {
            return $this->json([], '授权成功');
        } else {
            return $this->json([], '授权失败', 0);
        }
    }

    /**
     * 卡使用记录(含绑定,转出)
     * @SWG\Get(
     *   path="/promoter/cards-used",
     *   summary="卡使用记录",
     *   tags={"推广员功能"},
     *     @SWG\Parameter(
     *     name="offset",
     *     in="formData",
     *     description="最后一条已读取记录id,初始为0",
     *     required=true,
     *     type="integer",
     *   ),
     *     @SWG\Parameter(
     *     name="limit",
     *     in="formData",
     *     description="每页记录数量",
     *     required=false,
     *     default=10,
     *     type="integer",
     *   ),
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Items(
     *                  @SWG\Property(property="id", type="integer", example="12",description="记录id"),
     *                  @SWG\Property(property="type", type="string", example="binding",description="使用类型:binding 绑定 transfer 转出"),
     *                  @SWG\Property(property="card_name", type="string", example="vip金卡",description="卡名称"),
     *                  @SWG\Property(property="card_no", type="string", example="12345678",description="卡号"),
     *                  @SWG\Property(property="to_user", type="string", example="13111111111",description="目标用户"),
     *                  @SWG\Property(property="created_at", type="string", example="2017-01-01 0:0:0",description="使用时间")
     *                  )
     *          )
     *      )
     * )
     */
    public function cardsUseRecords(Request $request)
    {
//        return $this->json([
//            ['id' => 12, 'type' => 'binding', 'card_name' => 'vip金卡', 'card_no' => '12345678', 'to_user' => '13111111111', 'created_at' => '2017-01-01 0:0:0'],
//            ['id' => 13, 'type' => 'binding', 'card_name' => 'vip金卡', 'card_no' => '12345678', 'to_user' => '13111111111', 'created_at' => '2017-01-01 0:0:0'],
//            ['id' => 14, 'type' => 'binding', 'card_name' => 'vip金卡', 'card_no' => '12345678', 'to_user' => '13111111111', 'created_at' => '2017-01-01 0:0:0'],
//            ['id' => 15, 'type' => 'binding', 'card_name' => 'vip金卡', 'card_no' => '12345678', 'to_user' => '13111111111', 'created_at' => '2017-01-01 0:0:0'],
//            ['id' => 16, 'type' => 'binding', 'card_name' => 'vip金卡', 'card_no' => '12345678', 'to_user' => '13111111111', 'created_at' => '2017-01-01 0:0:0'],
//        ]);

        $offset = (int)$request->get('offset', 0);
        $limit = (int)$request->get('limit', 10);

        $where = [
            ['from', Auth::id()],
        ];
        if ($offset > 0) {
            $where [] = ['id', '<', $offset];
        }
        return $this->json(CardUse::where($where)->limit($limit)->with(['card.type', 'toUser'])->orderByDesc('id')->get()->map(function ($item) {
            return [
                'id' => $item->getKey(),
                'type' => [CardUse::TYPE_BINDING => 'binding', CardUse::TYPE_TRANSFER => 'transfer'][$item->type],
                'card_name' => $item->card->type->name,
                'card_no' => $item->card->mix_id(),
                'to_user' => $item->toUser->mobile,
                'created_at' => $item->created_at->toDateTimeString()
            ];
        }));
    }

    /**
     * 授权记录
     * @SWG\Get(
     *   path="/promoter/grant-history",
     *   summary="授权记录",
     *   tags={"推广员功能"},
     *     @SWG\Parameter(
     *     name="offset",
     *     in="formData",
     *     description="最后一条已读取记录id,初始为0",
     *     required=true,
     *     type="integer",
     *   ),
     *     @SWG\Parameter(
     *     name="limit",
     *     in="formData",
     *     description="每页记录数量",
     *     required=false,
     *     default=10,
     *     type="integer",
     *   ),
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Items(
     *                  @SWG\Property(property="id", type="integer", example="12",description="记录id"),
     *                  @SWG\Property(property="name", type="string", example="张三",description="用户名"),
     *                  @SWG\Property(property="user_id", type="string", example="13111111111",description="用户账号"),
     *                  @SWG\Property(property="avatar", type="string", example="http://x.com/img/a.gif",description="头像"),
     *                  @SWG\Property(property="created_at", type="string", example="2017-01-01 0:0:0",description="授权时间")
     *                  )
     *          )
     *      )
     * )
     */
    public function grantRecords(Request $request)
    {
//        return $this->json([
//            [
//                "id" => "12",
//                "name" => "张三",
//                "user_id" => "13111111111",
//                "avatar" => "http://x.com/img/a.gif",
//                "created_at" => "2017-01-01 0:0:0"
//            ],
//            [
//                "id" => "13",
//                "name" => "张三",
//                "user_id" => "13111111111",
//                "avatar" => "http://x.com/img/a.gif",
//                "created_at" => "2017-01-01 0:0:0"
//            ],
//            [
//                "id" => "14",
//                "name" => "张三",
//                "user_id" => "13111111111",
//                "avatar" => "http://x.com/img/a.gif",
//                "created_at" => "2017-01-01 0:0:0"
//            ]
//        ]);

        $offset = (int)$request->get('offset', 0);
        $limit = (int)$request->get('limit', 10);

        $where = [
            ['by_admin', 0],
            ['grant_by', Auth::id()],
        ];
        if ($offset > 0) {
            $where [] = ['id', '<', $offset];
        }
        return $this->json(PromoterGrant::where($where)->limit($limit)->with('grantTo')->orderByDesc('id')->get()->map(function ($item) {
            return ['id' => $item->getKey(), 'name' => $item->grantTo->name, 'user_id' => $item->grantTo->mobile, 'avatar' => $item->grantTo->avatar, 'created_at' => $item->created_at->toDateTimeString()];
        }));
    }

    /**
     * 可使用卡片
     * @SWG\Get(
     *   path="/promoter/cards-reserve",
     *   summary="可用卡列表",
     *   tags={"推广员功能"},
     *     @SWG\Parameter(
     *     name="offset",
     *     in="formData",
     *     description="最后一条已读取记录id,初始为0",
     *     required=true,
     *     type="integer",
     *   ),
     *     @SWG\Parameter(
     *     name="limit",
     *     in="formData",
     *     description="每页记录数量",
     *     required=false,
     *     default=10,
     *     type="integer",
     *   ),
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Items(
     *                  @SWG\Property(property="id", type="integer", example="12",description="记录id"),
     *                  @SWG\Property(property="card_no", type="string", example="12345678",description="卡号"),
     *                  @SWG\Property(property="card_name", type="string", example="vip金卡",description="卡名"),
     *                  @SWG\Property(property="percent", type="float", example="5",description="分润千分比"),
     *                  )
     *          )
     *      )
     * )
     */
    public function cardsReserve(Request $request)
    {
//        return $this->json([[
//            "id" => "12",
//            "card_no" => "12345678",
//            "card_name" => "vip金卡",
//            "percent" => "5"
//        ],
//            [
//                "id" => "13",
//                "card_no" => "12345678",
//                "card_name" => "vip金卡",
//                "percent" => "5"
//            ],
//            [
//                "id" => "14",
//                "card_no" => "12345678",
//                "card_name" => "vip金卡",
//                "percent" => "5"
//            ],
//            [
//                "id" => "15",
//                "card_no" => "12345678",
//                "card_name" => "vip金卡",
//                "percent" => "5"
//            ]]);
        $offset = (int)$request->get('offset', 0);
        $limit = (int)$request->get('limit', 10);
        return $this->json(Card::where([
                ['promoter_id', Auth::id()],
                ['is_bound', 0],
                ['is_frozen', 0],
                ['id', '>', $offset]]
        )->with('type')->limit($limit)->get()->map(function ($item) {
            return ['id' => $item->getKey(), 'card_no' => $item->mix_id(), 'card_name' => $item->type->name, 'percent' => $item->type->percent * 10];
        }));
    }

    /**
     * 出卡总数
     * @SWG\Get(
     *   path="/promoter/cards_used_num",
     *   summary="已出卡总数",
     *   tags={"推广员功能"},
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="used_cards",
     *                  type="integer",
     *                  example=6,
     *                  description="出卡总数量"
     *              )
     *          )
     *      )
     * )
     */
    public function cardsUsedNum()
    {
        return $this->json(['used_cards' => CardUse::where('from', Auth::id())->count()]);
    }


    /**
     * 查询代理用户
     * @SWG\Post(
     *   path="/promoter/query-agent",
     *   summary="查询代理",
     *   tags={"推广员功能"},
     *     @SWG\Parameter(
     *     name="user_id",
     *     in="formData",
     *     required=true,
     *     type="string",
     *     description="要开通vip的账号",
     *   ),
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1,
     *                  description="代理存在返回1"
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string",
     *                  description="失败返回错误信息"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(property="avatar", type="string", example="http://abd.com/a.gif",description="用户头像"),
     *                  @SWG\Property(property="user_id", type="string", example="13111111111",description="用户id"),
     *                  @SWG\Property(property="name", type="string", example="张三",description="用户名"),
     *              )
     *          )
     *      )
     * )
     */
    public function queryAgent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'digits:11',
        ]);

        if ($validator->fails()) {
            return $this->json([], '无效的手机号', 0);
        }

        $user = User::where('mobile', $request->user_id)->whereHas('roles', function ($query) {
            $query->where('name', '=', 'agent');
        })->first();
        if ($user) {
            return $this->json([
                'avatar' => $user->avatar,
                'user_id' => $user->mobile,
                'name' => $user->name,

            ]);
        } else {
            return $this->json([], '该代理不存在', 0);
        }
    }

    /**
     * 查询推广员
     * @SWG\Post(
     *   path="/promoter/query-promoter",
     *   summary="查询推广员",
     *   tags={"推广员功能"},
     *     @SWG\Parameter(
     *     name="user_id",
     *     in="formData",
     *     required=true,
     *     type="string",
     *     description="要转出卡的目标账号",
     *   ),
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1,
     *                  description="推广员存在返回1"
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string",
     *                  description="失败返回错误信息"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(property="avatar", type="string", example="http://abd.com/a.gif",description="用户头像"),
     *                  @SWG\Property(property="user_id", type="string", example="13111111111",description="用户id"),
     *                  @SWG\Property(property="name", type="string", example="张三",description="用户名"),
     *              )
     *          )
     *      )
     * )
     */
    public function queryPromoter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'digits:11',
        ]);

        if ($validator->fails()) {
            return $this->json([], '无效的手机号', 0);
        }

        $user = User::where('mobile', $request->user_id)->whereHas('roles', function ($query) {
            $query->where('name', '=', PromoterGrant::PROMOTER_ROLE_NAME);
        })->first();
        if ($user) {
            return $this->json([
                'avatar' => $user->avatar,
                'user_id' => $user->mobile,
                'name' => $user->name,

            ]);
        } else {
            return $this->json([], '该推广员不存在', 0);
        }
    }

    /**
     * 查询非推广员用户
     * @SWG\Post(
     *   path="/promoter/query-none-promoter",
     *   summary="查询非推广员用户",
     *   tags={"推广员功能"},
     *     @SWG\Parameter(
     *     name="user_id",
     *     in="formData",
     *     required=true,
     *     type="string",
     *     description="要授权为推广员的用户账号",
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
     *                  type="string",
     *                  description="失败返回错误信息"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(property="avatar", type="string", example="http://abd.com/a.gif",description="用户头像"),
     *                  @SWG\Property(property="user_id", type="string", example="13111111111",description="用户id"),
     *                  @SWG\Property(property="name", type="string", example="张三",description="用户名"),
     *              )
     *          )
     *      )
     * )
     */
    public function queryNonePromoter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'digits:11',
        ]);

        if ($validator->fails()) {
            return $this->json([], '无效的手机号', 0);
        }

        $user = User::where('mobile', $request->user_id)->whereDoesntHave('roles', function ($query) {
            $query->where('name', '=', PromoterGrant::PROMOTER_ROLE_NAME);
        })->first();
        if ($user) {
            return $this->json([
                'avatar' => $user->avatar,
                'user_id' => $user->mobile,
                'name' => $user->name,

            ]);
        } else {
            return $this->json([], '用户不存在或已经是推广员', 0);
        }
    }

    /**
     * 卡片详情
     * @SWG\Post(
     *   path="/promoter/card-detail",
     *   summary="卡片详情",
     *   tags={"推广员功能"},
     *    @SWG\Parameter(
     *    name="card_id",
     *    in="formData",
     *    required=true,
     *    type="integer",
     *    description="要查询的卡记录ID",
     *   ),
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1,
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(property="id", type="integer", example="12",description="记录id"),
     *                  @SWG\Property(property="card_no", type="string", example="12345678",description="卡号"),
     *                  @SWG\Property(property="card_name", type="string", example="vip金卡",description="卡名"),
     *                  @SWG\Property(property="percent", type="float", example="5",description="分润千分比"),
     *              )
     *          )
     *      )
     * )
     */
    public function cardDetail(Request $request)
    {
        $card = Card::where([
                ['promoter_id', Auth::id()],
                ['is_bound', 0],
                ['is_frozen', 0],
            ]
        )->with('type')->find($request->post('card_id'));
        if ($card) {
            return $this->json(['id' => $card->getKey(), 'card_no' => $card->mix_id(), 'card_name' => $card->type->name, 'percent' => $card->type->percent * 10]);
        }
    }


}