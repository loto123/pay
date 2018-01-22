<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 宠物交易控制器
 * Class PetTrade
 * @package App\Http\Controllers\Api
 */
class PetTradeController extends Controller
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
    public function sellable(Request $request)
    {
        return [
            'code' => 1,
            'msg' => '',
            'data' => [
                'list' => [
                    ['id' => '1', 'pic' => 'a.jpg', 'is_egg' => false],
                    ['id' => '2', 'is_egg' => true],
                    ['id' => '1', 'pic' => 'c.jpg', 'is_egg' => false],
                ]
            ]
        ];

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
        return [
            'code' => 1,
            'msg' => '领取成功',
            'data' => [
                'egg_id' => 1,
            ]
        ];
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
        return [
            'code' => 1,
            'msg' => '',
            'data' => [
                'times' => 3
            ]
        ];
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
     *                  ),
     *                  @SWG\Property(
     *                      property="pic",
     *                      type="string",
     *                      description="孵化宠物图片"
     *                  )
     *              )
     *          )
     *      )
     * )
     */
    public function broodTheEgg()
    {
        return [
            'code' => 1,
            'msg' => '孵化成功',
            'data' => [
                'id' => '1', 'pic' => 'a.jpg',
            ]
        ];
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
     *                      property="pic",
     *                      type="string",
     *                      description="宠物图片"
     *                  )
     *              )
     *          )
     *      )
     * )
     * @return array
     */
    public function findSellBill()
    {
        return [
            'code' => 1,
            'msg' => '',
            'data' => [
                [
                    'id' => '1', //卖单id
                    'pic' => 'a.jpg'//宠物图片
                ],
            ]
        ];
    }


}
