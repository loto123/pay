<?php

namespace App\Http\Controllers\Api;

use App\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use EasyWeChat;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
     *     name="offset",
     *     in="path",
     *     description="最后记录id",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="limit",
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
        $query = User::where("parent_id", $user->id)->where("status", User::STATUS_NORMAL);
        if ($request->type == 0) {
            $query->has("shop");
        } else {
            $query->doesntHave("shop");
        }
        $count = (int)$query->count();
        if ($request->offset) {
            $query->where("id", "<", User::decrypt($request->offset));
        }
        $query->orderBy("id", "DESC")->limit($request->input('limit', 20));
        foreach ($query->get() as $_user) {
            $list[] = [
                'id' => $_user->en_id(),
                'avatar' => $_user->avatar,
                'name' => $_user->name,
                'mobile' => $_user->mobile
            ];
        }
        return $this->json(['total' => $count, 'list' => $list]);
    }

    /**
     * @SWG\Get(
     *   path="/proxy/members/count",
     *   summary="代理成员数",
     *   tags={"代理"},
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
     *                  @SWG\Property(property="total", type="integer", example=123,description="成员总数"),
     *                  @SWG\Property(property="manager_total", type="integer", example=123,description="店主成员总数"),
     *                  @SWG\Property(property="member_total", type="integer", example=123,description="普通成员总数"),
     *              )
     *          )
     *      ),     * )
     * @return \Illuminate\Http\Response
     */
    public function members_count() {
        $user = $this->auth->user();
        return $this->json(['total' => (int)$user->child_proxy()->count(), 'manager_total' => (int)$user->child_proxy()->has("shop")->count(), 'member_total' => (int)$user->child_proxy()->doesntHave("shop")->count()]);
    }

    /**
     * @SWG\Get(
     *   path="/proxy/qrcode",
     *   summary="代理二维码",
     *   tags={"代理"},
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
     *                  @SWG\Property(property="thumb", type="string", example="http://url",description="用户头像"),
     *                  @SWG\Property(property="name", type="string", example="用户名",description="用户名"),
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
    public function qrcode(Request $request) {
        $size = $request->input("size", 200);
        $user = $this->auth->user();
        /* @var $user User */
        $url = url(sprintf("/#/shareUser/inviteLink/download?mobile=%s", $user->mobile));
        $filename = md5($url."_".$size);
        $path = 'qrcode/'.$filename.'.png';
        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, QrCode::format('png')->size($size)->margin(1)->generate($url));
        }
        return $this->json(['url' => url('storage/'.$path), 'thumb' => $user->avatar, 'name' => $user->name]);
    }

    /**
     * 成为代理
     * @SWG\Post(
     *   path="/proxy/create",
     *   summary="成为代理",
     *   tags={"代理"},
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
    public function create() {
        $user = $this->auth->user();
        /* @var $user \App\User */
        if ($user->hasRole('agent')) {
            return $this->json([], trans("api.user_already_is_proxy"), 0);
        }
        $role = Role::where("name", 'agent')->first();
        $user->attachRole($role);
        return $this->json();
    }
}