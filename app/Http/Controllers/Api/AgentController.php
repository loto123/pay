<?php
/**
 * 代理功能控制器
 * Author: huangkaixuan
 * Date: 2018/1/11
 * Time: 16:50
 */

namespace App\Http\Controllers\Api;


use Illuminate\Support\Facades\Auth;

class AgentController extends BaseController
{

    /**
     * 我的VIP权益
     */
    public function myVip()
    {
        dump('fuck');
        $card = Auth::user()->myVipCard();
        $json = ['vip_bound' => !empty($card)];//是否绑定
        if ($json['if_bound']) {
            $json['card_name'] = $card->type->name; //卡名
            $json['percent'] = $card->type->percent * 10;//卡分润比例(千分比)
            $json['expired_at'] = $card->expired_at;//过期时间
            $json['card_no'] = $card->getKey();//卡号
        }
        return $this->json($json);
    }
}