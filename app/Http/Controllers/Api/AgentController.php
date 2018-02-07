<?php
/**
 * 代理功能控制器
 * Author: huangkaixuan
 * Date: 2018/1/11
 * Time: 16:50
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AgentController extends Controller
{

    /**
     * 我的VIP权益
     * @SWG\Get(
     *   path="/agent/bound_vip",
     *   summary="已绑定vip",
     *   tags={"代理功能"},
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *     @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(
     *                  property="if_bound",
     *                  type="boolean",
     *                  example=true,
     *                  description="是否有绑定vip卡",
     *                  ),
     *              property="if_frozen",
     *                  type="boolean",
     *                  example=true,
     *                  description="卡片是否被冻结",
     *                  ),
     *              @SWG\Property(
     *                  property="card_name",
     *                  type="string",
     *                  example="vip金卡",
     *                  description="卡片名称"
     *              ),
     *              @SWG\Property(
     *                  property="default_percent",
     *                  type="number",
     *                  example="5",
     *                  description="默认分润比例,千分比"
     *              ),
     *              @SWG\Property(
     *                  property="percent",
     *                  type="number",
     *                  example="5",
     *                  description="VIP加成分润比例,千分比"
     *              ),
     *              @SWG\Property(
     *                  property="expired_at",
     *                  type="string",
     *                  example="2017-01-01 0:0:0",
     *                  description="过期时间,如果为null为永久有效"
     *              ),
     *              @SWG\Property(
     *                  property="card_no",
     *                  type="string",
     *                  example="12345678",
     *                  description="卡号"
     *              )
     *              ),
     *     @SWG\Property(
     *                  property="code",
     *                  type="integer"
     *              ),
     *
     *          )
     *      )
     * )
     */

    public function myVip()
    {
//        return [
//            "if_bound" => false,
//            "card_name" => "vip金卡",
//            "percent" => "5",
//            "expired_at" => "2017-01-01 0:0:0",
//            "card_no" => "12345678"
//        ];
        $card = Auth::user()->myVipCard();
        $json = ['if_bound' => !empty($card)];//是否绑定
        if ($json['if_bound']) {
            $json['card_name'] = $card->type->name; //卡名
            $json['if_frozen'] = !!$card->is_frozen;
            $json['default_percent'] = Auth::user()->myDefaultProfitShareRate() * 10;
            $json['percent'] = $card->type->percent * 10;//卡分润比例(千分比)
            $json['expired_at'] = $card->expired_at ? $card->expired_at : null;//过期时间
            $json['card_no'] = $card->mix_id();//卡号
        }
        return ['code' => 1, 'data' => $json, 'msg' => ''];
    }
}