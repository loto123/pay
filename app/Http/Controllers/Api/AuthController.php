<?php

namespace App\Http\Controllers\Api;

use App\Notifications\UserApply;
use App\OauthUser;
use App\Pay\Model\Channel;
use App\Pay\Model\PayFactory;
use App\User;
use App\Role;
use App\WechatOpen;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Swagger\Annotations as SWG;
use PhpSms;
use Tymon\JWTAuth\Facades\JWTAuth;
use EasyWeChat;

/**
 * @SWG\Swagger(
 *   basePath="/api",
 *   host="192.168.32.123:9999",
 *   @SWG\Info(
 *     title="游戏宝接口列表",
 *     version="0.0.1"
 *   ),
 *     @SWG\SecurityScheme(
 *      name="登录",
 *      securityDefinition="登录",
 *      type="apiKey"
 *     ),
 *     @SWG\Tag(
 *      name="登录",
 *      description="登录相关接口"
 *     ),
 *     @SWG\Tag(
 *      name="web微信登录",
 *      description="web微信登录相关接口(web使用)"
 *     ),
 *     @SWG\Tag(
 *      name="店铺",
 *      description="店铺相关接口（需登录)"
 *     ),
 *      @SWG\Definition(
 *          definition="CodeDefined",
 *          @SWG\Property(
 *              property="code",
 *              type="integer",
 *              format="int32"
 *          )
 *      ),
 *      @SWG\Definition(
 *          definition="SuccessModel",
 *          required={"code", "msg", "data"},
 *          @SWG\Property(
 *              property="code",
 *              type="integer",
 *              format="int32",
 *
 *          ),
 *          @SWG\Property(
 *              property="msg",
 *              type="string"
 *          ),
 *          @SWG\Property(
 *              property="data",
 *              type="object"
 *          )
 *      ),
 *      @SWG\Definition(
 *          definition="ErrorModel",
 *          required={"code", "msg", "data"},
 *          @SWG\Property(
 *              property="code",
 *              type="integer",
 *              format="int32"
 *          ),
 *          @SWG\Property(
 *              property="msg",
 *              type="string"
 *          ),
 *          @SWG\Property(
 *              property="data",
 *              type="object"
 *          )
 *      ),
 * )
 * @package App\Http\Controllers\Api
 */
class AuthController extends BaseController {

    /**
     * @SWG\Post(
     *   path="/auth/login",
     *   summary="手机号登录",
     *     tags={"登录"},
     *     @SWG\Parameter(
     *         name="mobile",
     *         in="formData",
     *         description="手机号",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         in="formData",
     *         description="密码",
     *         required=true,
     *         type="string",
     *     ),
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
     *                  @SWG\Property(property="token", type="string", example="abcd",description="jwt token"),
     *                  @SWG\Property(property="wechat", type="boolean", example=0,description="是否绑定微信"),
     *                  @SWG\Property(property="id", type="string", example="1234",description="用户id"),
     *                  @SWG\Property(property="ticket", type="string", example="abcd",description="用户ticket"),
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
    public function login(Request $request) {
        $credentials = $request->only('mobile', 'password');
        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return $this->json([], trans('api.error_password'), 0);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return $this->json([], trans('api.error'), 0);
        }

        // all good so return the token
        $user = JWTAuth::toUser($token);
        if ($user->status == User::STATUS_BLOCK) {
            return $this->json([], trans('api.user_block'), 0);
        }
        $wechat = $user->wechat_user ? 1 : 0;
        if (!$wechat) {
            $ticket = md5(sprintf("%d_%s_%s", $user->id, time(), str_random(10)));
            Cache::store('redis')->put("USER_TICKET_".$ticket, $user->id, 60*60);
        } else {
            $ticket = "";
        }
        $id = $user->en_id();

        return $this->json(compact('token', 'wechat', 'id', 'ticket'));
    }

    /**
     * @SWG\Post(
     *   path="/auth/sms/login",
     *   summary="手机验证码登录",
     *     tags={"登录"},
     *     @SWG\Parameter(
     *         name="mobile",
     *         in="formData",
     *         description="手机号",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="code",
     *         in="formData",
     *         description="验证码",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="oauth_user",
     *         in="formData",
     *         description="微信用户id",
     *         required=false,
     *         type="string",
     *     ),
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
     *                  @SWG\Property(property="token", type="string", example="abcd",description="jwt token"),
     *                  @SWG\Property(property="wechat", type="boolean", example=0,description="是否绑定微信"),
     *                  @SWG\Property(property="id", type="string", example="1234",description="用户id"),
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
    public function sms_login(Request $request) {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|regex:/^1[34578][0-9]{9}$/|exists:'.(new User)->getTable(),
            'code' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $cache_key = "SMS_".$request->mobile;
        $cache_value = Cache::get($cache_key);
        if (!$cache_value || !isset($cache_value['code']) || !$cache_value['code'] || $cache_value['code'] != $request->code || $cache_value['time'] < (time() - 300)) {
            return $this->json([], trans("api.error_sms_code"), 0);
        }
        Cache::forget($cache_key);
        $user = User::where("mobile", $request->mobile)->first();
        if (!$user) {
            return $this->json([], trans("api.error_sms_code"), 0);
        }
        $token = JWTAuth::fromUser($user);

        if ($user->status == User::STATUS_BLOCK) {
            return $this->json([], trans('api.user_block'), 0);
        }
        if ($request->oauth_user) {
            $oauth_user_id = Cache::store('redis')->get("OAUTH_USER_TICKET_".$request->oauth_user);
            if (!$oauth_user_id) {
                return $this->json([], trans("api.auth_error"), 0);
            }
            Cache::store('redis')->forget("OAUTH_USER_TICKET_".$request->oauth_user);
            $oauth_user = OauthUser::find($oauth_user_id);
            if ($oauth_user) {
                $oauth_user->user_id = $user->id;
                $user->avatar = $oauth_user->headimgurl;
                $user->name = $oauth_user->nickname;
                $user->save();
                $oauth_user->save();
            }
        }
        $wechat = $user->wechat_user ? 1 : 0;
        $id = $user->en_id();
        $name = $user->name;
        return $this->json(compact('token', 'wechat', 'id', 'name'));
    }

    /**
     * @SWG\Post(
     *   path="/auth/register",
     *   summary="手机号注册",
     *     tags={"登录"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="formData",
     *         description="用户名",
     *         required=false,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="mobile",
     *         in="formData",
     *         description="手机号",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="invite_mobile",
     *         in="formData",
     *         description="邀请人手机号",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         in="formData",
     *         description="密码",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="code",
     *         in="formData",
     *         description="手机验证码",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="oauth_user",
     *         in="formData",
     *         description="微信用户id",
     *         required=false,
     *         type="string",
     *     ),
     *   @SWG\Response(
     *     response=200,
     *     description="ok",
     *     examples={
     *      "code":1,
     *      "message":"ok",
     *      "data":{}
     *     },
     *     examples={
     *      "code":0,
     *      "message":"error mobile",
     *      "data":{}
     *     }
     *   ),
     * )
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|regex:/^1[34578][0-9]{9}$/|unique:'.(new User)->getTable(),
            'invite_mobile' => 'required|regex:/^1[34578][0-9]{9}$/|exists:'.(new User)->getTable().',mobile',
            'password' => 'required',
            'code' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $cache_key = "SMS_".$request->mobile;
        $cache_value = Cache::get($cache_key);
        if (!$cache_value || !isset($cache_value['code']) || !$cache_value['code'] || $cache_value['code'] != $request->code || $cache_value['time'] < (time() - 300)) {
            return $this->json([], trans("api.error_sms_code"), 0);
        }
        Cache::forget($cache_key);
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['name'] = $request->name ? $request->name : $request->mobile;
        $wallet = PayFactory::MasterContainer();
        $wallet->save();
        $channel = Channel::where("disabled",0)->inRandomOrder()->first();
        $input['container_id'] = $wallet->id;
        try {
            $user = User::create($input);
        } catch (\Exception $e){
            return $this->json();
        }
        $role = Role::where("name", 'user')->first();
        if ($role) {
            $user->roles()->sync($role);
        }
        
        $success['token'] = JWTAuth::fromUser($user);
        $success['name'] = $user->name;
        $success['id'] = $user->en_id();
        $wechat = $user->wechat_user ? 1 : 0;
        if (!$wechat) {
            $ticket = md5(sprintf("%d_%s_%s", $user->id, time(), str_random(10)));
            Cache::store('redis')->put("USER_TICKET_".$ticket, $user->id, 60*60);
            $success['wechat'] = 0;
            
        } else {
            $ticket = "";
            $success['wechat'] = 1;
        }
        $success['ticket'] = $ticket;

        if ($request->oauth_user) {
            $oauth_user_id = Cache::store('redis')->get("OAUTH_USER_TICKET_".$request->oauth_user);
            if (!$oauth_user_id) {
                return $this->json([], trans("api.auth_error"), 0);
            }
            Cache::store('redis')->forget("OAUTH_USER_TICKET_".$request->oauth_user);
            $oauth_user = OauthUser::find($oauth_user_id);
            if ($oauth_user) {
                $oauth_user->user_id = $user->id;
                $user->avatar = $oauth_user->headimgurl;
                $user->name = $oauth_user->nickname;
                $oauth_user->save();
                $success['wechat'] = 1;
            }
        }
        $invite = User::where("mobile", $request->invite_mobile)->first();
        if ($invite) {
            $user->parent_id = $invite->id;
            $user->operator_id = $invite->operator_id;
            \App\Admin\Controllers\NoticeController::send([$invite->id],2,'您推荐的'.$user->mobile.'注册成功');
        }
        $user->channel_id = $channel->id;
        $user->save();
        return $this->json($success);
    }

    /**
     *
     * @SWG\Get(
     *   path="/auth/login/wechat/url",
     *   summary="获取微信授权url",
     *     tags={"web微信登录"},
     *     @SWG\Parameter(
     *         name="redirect_url",
     *         in="query",
     *         description="跳转链接",
     *         required=true,
     *         type="string",
     *     ),
     *   @SWG\Response(
     *     response=200,
     *     description="A list with products",
     *     examples={
     *      "code":0,
     *      "msg":"ok"
     *     }
     *   ),
     * )
     * @return \Illuminate\Http\Response
     */
    public function wechat_login_url(Request $request) {
        $validator = Validator::make($request->all(), [
            'redirect_url' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $app = Factory::officialAccount([
            'app_id' => config("wechat.official_account.app_id"),
            'secret' => config("wechat.official_account.secret"),
        ]);
        $response = $app->oauth->scopes(['snsapi_userinfo'])
            ->redirect($request->redirect_url);
//        var_dump( $app->access_token->getToken());
//        $access_token = $app->oauth->getAccessToken("021Rodlu1DGvWc0JIAlu18kllu1Rodle");
//        var_dump($access_token);
//        $user = $app->user->get($access_token->openid);
//        var_dump($user);
        return $this->json(['url' => $response->getTargetUrl()]);
    }

    /**
     *
     * @SWG\Post(
     *   path="/auth/login/wechat",
     *   summary="微信登录",
     *     tags={"web微信登录"},
     *     @SWG\Parameter(
     *         name="code",
     *         in="formData",
     *         description="微信授权code",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="state",
     *         in="formData",
     *         description="微信授权state",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="user_ticket",
     *         in="formData",
     *         description="要绑定的用户ticket",
     *         required=false,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="is_app",
     *         in="formData",
     *         description="是否为app调用 0=否 1=是",
     *         required=false,
     *         type="boolean",
     *     ),
     *   @SWG\Response(
     *     response=200,
     *     description="A list with products",
     *     examples={
     *      "code":0,
     *      "msg":"ok"
     *     }
     *   ),
     * )
     * @return \Illuminate\Http\Response
     */
    public function wechat_login(Request $request) {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'state' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        if ($request->is_app) {
            $appid = config("wechat.open_platform.app_id");
            $app = new WechatOpen($appid, config("wechat.open_platform.secret"));
            $user = $app->user($request->code);
        } else {
            $appid = config("wechat.official_account.app_id");
            $app = Factory::officialAccount([
                'app_id' => $appid,
                'secret' => config("wechat.official_account.secret"),
            ]);
            $access_token = $app->oauth->getAccessToken($request->code);
            $user = $app->user->get($access_token->openid);
        }
//        $app = app('wechat.official_account');
        if (!$user || !isset($user['openid'])) {
            return $this->json([], "auth error", 0);
        }
        if (isset($user['unionid'])) {
            $oauth_user = OauthUser::where("unionid", $user['unionid'])->first();
        } else {
            $oauth_user = OauthUser::where("openid", $user['openid'])->first();
        }
        Log::info("oauth_user:".var_export($user, true));
        if (!$oauth_user) {
            $oauth_user = new OauthUser();
            $oauth_user->openid = $user['openid'];
            $oauth_user->appid = $appid;
        }
        $oauth_user->subscribe = isset($user['subscribe']) ? $user['subscribe'] : 0;
        $oauth_user->nickname = isset($user['nickname']) ? $user['nickname'] : '';
        $oauth_user->sex = isset($user['sex']) ? $user['sex'] : 0;
        $oauth_user->city = isset($user['city']) ? $user['city'] : '';
        $oauth_user->province = isset($user['province']) ? $user['province'] : '';
        $oauth_user->country = isset($user['country']) ? $user['country'] : '';
        $oauth_user->headimgurl = isset($user['headimgurl']) ? $user['headimgurl'] : '';
        $oauth_user->subscribe_time = isset($user['subscribe_time']) ? $user['subscribe_time'] : 0;
        $oauth_user->unionid = isset($user['unionid']) ? $user['unionid'] : '';
        $oauth_user->remark = isset($user['remark']) ? $user['remark'] : '';
        $oauth_user->groupid = isset($user['groupid']) ? $user['groupid'] : 0;
        $oauth_user->save();
        $result = ['token' => '', 'oauth_user' => ''];
        if ($request->user_ticket) {
            $user_id = Cache::store('redis')->get("USER_TICKET_".$request->user_ticket);
            if (!$user_id) {
                return $this->json([], trans("api.auth_error"), 0);
            }

            if ($oauth_user->user_id) {
                return $this->json([], trans("api.wechat_already_bind"), 0);
            }
            Cache::store('redis')->forget("USER_TICKET_".$request->user_ticket);

            $login_user = User::find($user_id);
            if ($login_user) {
                $login_user->avatar = $oauth_user->headimgurl;
                $login_user->name = $oauth_user->nickname;
                $login_user->save();
                $oauth_user->user_id = $login_user->id;
                $oauth_user->save();
                $result['token'] = JWTAuth::fromUser($login_user);
            }
        } else {
            if ($oauth_user->user_id) {
                $login_user = User::find($oauth_user->user_id);
                if ($login_user) {
                    $login_user->avatar = $oauth_user->headimgurl;
                    $login_user->name = $oauth_user->nickname;
                    $login_user->save();
                    $result['token'] = JWTAuth::fromUser($login_user);
                }
            } else {
                $ticket = md5(sprintf("%d_%s_%s", $oauth_user->id, time(), str_random(10)));
                Cache::store('redis')->put("OAUTH_USER_TICKET_".$ticket, $oauth_user->id, 60*60);
                $result['oauth_user'] = $ticket;
            }
        }
        return $this->json($result);

    }


    /**
     * @SWG\Post(
     *   path="/auth/valid",
     *   summary="注册验证",
     *     tags={"登录"},
     *     @SWG\Parameter(
     *         name="mobile",
     *         in="formData",
     *         description="用户手机号",
     *         required=false,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="invite_mobile",
     *         in="formData",
     *         description="邀请人手机号",
     *         required=false,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="code",
     *         in="formData",
     *         description="手机验证码",
     *         required=false,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="exist",
     *         in="formData",
     *         description="找回密码传1，其他验证不传",
     *         required=false,
     *         type="string",
     *     ),
     *   @SWG\Response(
     *     response=200,
     *     description="ok",
     *     examples={
     *      "code":0,
     *      "msg":"ok"
     *     }
     *   ),
     * )
     * @return \Illuminate\Http\Response
     */
    public function valid(Request $request) {
        if ($request->exist) {
            $validator = Validator::make($request->all(), [
                'mobile' => 'required_with:code|regex:/^1[34578][0-9]{9}$/|exists:'.(new User)->getTable().',mobile',
                'code' => 'regex:/^\d{4}$/',
            ], ['mobile.regex'=>trans("api.error_mobile_format"), 'invite_mobile.regex'=>trans("api.error_invite_mobile_format"), 'mobile.exists' => trans("api.user_unexist"), 'invite_mobile.exists' => trans("api.invite_unexist")]);
        } else {
            $validator = Validator::make($request->all(), [
                'mobile' => 'required_with:code|regex:/^1[34578][0-9]{9}$/|unique:'.(new User)->getTable().',mobile',
                'invite_mobile' => 'regex:/^1[34578][0-9]{9}$/|exists:'.(new User)->getTable().',mobile',
                'code' => 'regex:/^\d{4}$/',
            ], ['mobile.regex'=>trans("api.error_mobile_format"), 'invite_mobile.regex'=>trans("api.error_invite_mobile_format"), 'mobile.unique' => trans("api.user_exist"), 'invite_mobile.exists' => trans("api.invite_unexist")]);
        }



        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        if ($request->code) {
            $cache_key = "SMS_".$request->mobile;
            $cache_value = Cache::get($cache_key);
            if (!$cache_value || !isset($cache_value['code']) || !$cache_value['code'] || $cache_value['code'] != $request->code || $cache_value['time'] < (time() - 300)) {
                return $this->json([], trans("api.error_sms_code"), 0);
            }
        }
        return $this->json();
    }

    /**
     * @SWG\Get(
     *   path="/auth/mobile/status",
     *   summary="手机号状态",
     *     tags={"登录"},
     *     @SWG\Parameter(
     *         name="mobile",
     *         in="formData",
     *         description="用户手机号",
     *         required=true,
     *         type="string",
     *     ),
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
     *                  @SWG\Property(property="status", type="integer", example=0,description="状态 0=未注册 1=已注册未绑定微信 2=已注册已绑定微信"),
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
    public function mobile_status(Request $request) {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|regex:/^1[34578][0-9]{9}$/',
        ], ['mobile.regex'=>trans("api.error_mobile_format")]);
        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $user = User::where("mobile", $request->mobile)->first();
        if (!$user) {
            return $this->json(['status' => 0]);
        }

        if ($user->wechat_user) {
            return $this->json(['status' => 2]);
        } else {
            return $this->json(['status' => 1]);

        }
    }

    /**
     * @SWG\Post(
     *   path="/auth/sms",
     *   summary="发送手机验证码",
     *     tags={"登录"},
     *     @SWG\Parameter(
     *         name="mobile",
     *         in="formData",
     *         description="用户手机号",
     *         required=false,
     *         type="string",
     *     ),
     *   @SWG\Response(
     *     response=200,
     *     description="ok",
     *     examples={
     *      "code":0,
     *      "msg":"",
     *      "data": {}
     *     }
     *   ),
     * )
     * @return \Illuminate\Http\Response
     */
    public function sms(Request $request) {

        $validator = Validator::make($request->all(), [
            'mobile' => 'required|regex:/^1[34578][0-9]{9}$/',
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $cache_key = "SMS_".$request->mobile;
        $cache_value = Cache::get($cache_key);
        if ($cache_value && isset($cache_value['code'])) {
            $code = $cache_value['code'];
        } else {
            $code = join('', array_map(function () {
                return mt_rand(0, 9);
            }, range(1, 4)));
        }
        if (config('app.debug')) {
            $code = '1234';
            $result['success'] = true;
        } else {
            $result = PhpSms::make()->template(['YunTongXun' => '224348'])->to($request->mobile)->data([$code])->send();
        }
//        var_dump($result);
        if ($result && $result['success']) {
            Cache::put($cache_key, ['code' => $code, 'time' => time()], 5);
            return $this->json();
        } else {
            Log::info("send sms error".var_export($result, true));
            if (isset($result['logs'][0]['result']['code']) && $result['logs'][0]['result']['code'] == '160040'){
                return $this->json([], '今日短信发送次数已用完', 0);
            } else {

                return $this->json([], '发送失败', 0);
            }
        }
    }

    /**
     * @SWG\Post(
     *   path="/auth/password/reset",
     *   summary="忘记密码",
     *     tags={"登录"},
     *     @SWG\Parameter(
     *         name="mobile",
     *         in="formData",
     *         description="用户手机号",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         in="formData",
     *         description="新密码",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="code",
     *         in="formData",
     *         description="验证码",
     *         required=true,
     *         type="string",
     *     ),
     *   @SWG\Response(
     *     response=200,
     *     description="ok",
     *     examples={
     *      "code":0,
     *      "msg":"",
     *      "data": {}
     *     }
     *   ),
     * )
     * @return \Illuminate\Http\Response
     */
    public function reset_password(Request $request) {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|regex:/^1[34578][0-9]{9}$/|exists:'.(new User)->getTable(),
            'password' => 'required',
            'code' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $cache_key = "SMS_".$request->mobile;
        $cache_value = Cache::get($cache_key);
        if (!$cache_value || !isset($cache_value['code']) || !$cache_value['code'] || $cache_value['code'] != $request->code || $cache_value['time'] < (time() - 300)) {
            return $this->json([], trans("api.error_sms_code"), 0);
        }
        Cache::forget($cache_key);
        $user = User::where("mobile", $request->mobile)->first();
        if (!$user) {
            return $this->json([], trans("error user"), 0);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        return $this->json();
    }

}