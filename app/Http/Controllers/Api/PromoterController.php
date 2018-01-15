<?php
/**
 * 推广员功能控制器
 * Author: huangkaixuan
 * Date: 2018/1/12
 * Time: 11:05
 */

namespace App\Http\Controllers\Api;


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
    public function transferCard()
    {
        return $this->json();
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
    public function bindCard()
    {
        return $this->json();
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
    public function grant()
    {
        return $this->json();
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
    public function cardsUseRecords()
    {
        return $this->json([
            ['id' => 12, 'type' => 'binding', 'card_name' => 'vip金卡', 'card_no' => '12345678', 'to_user' => '13111111111', 'created_at' => '2017-01-01 0:0:0'],
            ['id' => 13, 'type' => 'binding', 'card_name' => 'vip金卡', 'card_no' => '12345678', 'to_user' => '13111111111', 'created_at' => '2017-01-01 0:0:0'],
            ['id' => 14, 'type' => 'binding', 'card_name' => 'vip金卡', 'card_no' => '12345678', 'to_user' => '13111111111', 'created_at' => '2017-01-01 0:0:0'],
            ['id' => 15, 'type' => 'binding', 'card_name' => 'vip金卡', 'card_no' => '12345678', 'to_user' => '13111111111', 'created_at' => '2017-01-01 0:0:0'],
            ['id' => 16, 'type' => 'binding', 'card_name' => 'vip金卡', 'card_no' => '12345678', 'to_user' => '13111111111', 'created_at' => '2017-01-01 0:0:0'],
        ]);
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
    public function grantRecords()
    {
        return $this->json([
            [
                "id" => "12",
                "name" => "张三",
                "user_id" => "13111111111",
                "avatar" => "http://x.com/img/a.gif",
                "created_at" => "2017-01-01 0:0:0"
            ],
            [
                "id" => "13",
                "name" => "张三",
                "user_id" => "13111111111",
                "avatar" => "http://x.com/img/a.gif",
                "created_at" => "2017-01-01 0:0:0"
            ],
            [
                "id" => "14",
                "name" => "张三",
                "user_id" => "13111111111",
                "avatar" => "http://x.com/img/a.gif",
                "created_at" => "2017-01-01 0:0:0"
            ]
        ]);
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
    public function cardsReserve()
    {
        return $this->json([[
            "id" => "12",
            "card_no" => "12345678",
            "card_name" => "vip金卡",
            "percent" => "5"
        ],
            [
                "id" => "13",
                "card_no" => "12345678",
                "card_name" => "vip金卡",
                "percent" => "5"
            ],
            [
                "id" => "14",
                "card_no" => "12345678",
                "card_name" => "vip金卡",
                "percent" => "5"
            ],
            [
                "id" => "15",
                "card_no" => "12345678",
                "card_name" => "vip金卡",
                "percent" => "5"
            ]]);
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
        return $this->json(['used_cards' => 6]);
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
    public function queryAgent()
    {
        return $this->json([
            'avatar' => 'http://a.com/c.gif',
            'user_id' => '13111111111',
            'name' => '张三'

        ]);
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
    public function queryPromoter()
    {
        return $this->json([
            'avatar' => 'http://a.com/c.gif',
            'user_id' => '13111111111',
            'name' => '张三'

        ]);
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
    public function queryNonePromoter()
    {
        return $this->json([
            'avatar' => 'http://a.com/c.gif',
            'user_id' => '13111111111',
            'name' => '张三'

        ]);
    }

    /**
     * 卡片详情
     * @SWG\Get(
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
    public function cardDetail()
    {
        return $this->json([
            "id" => "15",
            "card_no" => "12345678",
            "card_name" => "vip金卡",
            "percent" => "5"
        ]);
    }


}