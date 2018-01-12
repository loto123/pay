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

    }

    /**
     * 推广员绑卡
     * @SWG\Post(
     *   path="/promoter/bind-card",
     *   summary="推广员将卡片绑定给代理",
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

    }

    /**
     * 授权推广员
     * @SWG\Post(
     *   path="/promoter/grant",
     *   summary="推广员给其它用户开通推广员权限",
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

    }

    /**
     * 卡使用记录(含绑定,转出)
     * @SWG\Get(
     *   path="/promoter/cards-used",
     *   summary="推广员卡使用记录",
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

    }

    /**
     * 授权记录
     * @SWG\Get(
     *   path="/promoter/grant-history",
     *   summary="推广员授权记录",
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

    }

    /**
     * 可使用卡片
     * @SWG\Get(
     *   path="/promoter/cards-reserve",
     *   summary="推广员可用卡",
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

    }
}