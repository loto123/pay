<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use EasyWeChat;

/**
 *
 * @package App\Http\Controllers\Api
 */
class ProxyController extends BaseController {

    /**
     * @SWG\Get(
     *   path="/proxy/share",
     *   summary="代理分享",
     *   tags={"代理"},
     *   @SWG\Parameter(
     *     name="list",
     *     in="path",
     *     description="分享api列表",
     *     required=true,
     *     type="array",
     *             @SWG\Items(
     *                 type="string"
     *             )
     *   ),
     *   @SWG\Parameter(
     *     name="share_url",
     *     in="path",
     *     description="分享链接地址",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function share(Request $request) {
        $validator = Validator::make($request->all(),
            [
                'list' => 'required|array',
                'share_url' => 'required|url'
            ]
        );
        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $app = EasyWeChat::officialAccount();
        $app->jssdk->setUrl($request->share_url);
        $config = $app->jssdk->buildConfig($request->list);
        return $this->json([
            'config' => $config,
            'invite_id' => $this->auth->user()->en_id(),
        ]);
    }

    /**
     * @SWG\Get(
     *   path="/proxy/members",
     *   summary="代理成员",
     *   tags={"代理"},
     *   @SWG\Parameter(
     *     name="type",
     *     in="path",
     *     description="类型 0=店主 1=用户",
     *     required=true,
     *     type="number"
     *   ),
     *   @SWG\Parameter(
     *     name="page",
     *     in="path",
     *     description="页码",
     *     required=false,
     *     type="number"
     *   ),
     *   @SWG\Parameter(
     *     name="size",
     *     in="path",
     *     description="数目",
     *     required=false,
     *     type="number"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function members(Request $request) {
        $user = $this->auth->user();
        $list = [];
        $query = $user->child_proxy();
        if ($request->type == 0) {
            $query->has("shop");
        } else {
            $query->doesntHave("shop");
        }
        foreach ($query->paginate($request->input('size', 20)) as $_user) {
            $list[] = [
                'avatar' => $_user->avatar,
                'name' => $_user->name,
                'mobile' => $_user->mobile
            ];
        }
        return $this->json(['total' => (int)$query->count(), 'list' => $list]);
    }

    /**
     * @SWG\Get(
     *   path="/proxy/members/count",
     *   summary="代理成员数",
     *   tags={"代理"},
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function members_count() {
        $user = $this->auth->user();
        return $this->json(['total' => (int)$user->child_proxy()->count(), 'manager_total' => (int)$user->child_proxy()->has("shop")->count(), 'member_total' => (int)$user->child_proxy()->doesntHave("shop")->count()]);
    }
}