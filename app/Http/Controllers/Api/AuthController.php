<?php

namespace App\Http\Controllers\Api;

use App\OauthUser;
use App\Pay\Model\PayFactory;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
 *   )
 * )
 * @package App\Http\Controllers\Api
 */
class AuthController extends BaseController {
    /**
     *
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
        $wechat = $user->wechat_user ? 1 : 0;
        return $this->json(compact('token', 'wechat'));
    }

    /**
     *
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
            return $this->json([], trans("error code"), 0);
        }
        Cache::forget($cache_key);
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['name'] = $request->name ? $request->name : $request->mobile;
        $wallet = PayFactory::MasterContainer();
        $wallet->save();
        $input['container_id'] = $wallet->id;
        try {
            $user = User::create($input);
        } catch (\Exception $e){
            return $this->json();
        }
        $invite = User::where("mobile", $request->invite_mobile)->first();
        if ($invite) {
            $user->parent_id = $invite->id;
        }
        $success['token'] = JWTAuth::fromUser($user);
        $success['name'] = $user->name;
        if ($request->oauth_user) {
            $oauth_user = OauthUser::find($request->oauth_user);
            if ($oauth_user) {
                $oauth_user->user_id = $user->id;
                $user->avatar = $oauth_user->headimgurl;
                $oauth_user->save();
            }
        }
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
        $app = EasyWeChat::officialAccount();
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
        $app = EasyWeChat::officialAccount();
        $access_token = $app->oauth->getAccessToken($request->code);
        $user = $app->user->get($access_token->openid);
        $oauth_user = OauthUser::where("openid", $access_token->openid)->first();
        if (!$oauth_user) {
            $oauth_user = new OauthUser();
            $oauth_user->openid = $user['openid'];
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
        if ($oauth_user->user_id) {
            $user = User::find($oauth_user->user_id);
            if ($user) {
                return $this->json(['token' => JWTAuth::fromUser($user)]);
            }
        } else {
            return $this->json(['oauth_user' => $oauth_user->id]);
        }
    }


    /**
     *
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
                'mobile' => 'required_with:code|regex:/^1[34578][0-9]{9}$/|exist:'.(new User)->getTable().',mobile',
                'code' => 'regex:/^\d{4}$/',
            ], ['mobile.regex'=>trans("api.error_mobile_format"), 'invite_mobile.regex'=>trans("api.error_invite_mobile_format"), 'mobile.unique' => trans("api.user_exist"), 'invite_mobile.exists' => trans("api.invite_unexist")]);
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
                return $this->json([], trans("error code"), 0);
            }
        }
        return $this->json();
    }

    /**
     *
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
            return $this->json([], 'error', 0);
        }
    }

    /**
     *
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
            return $this->json([], trans("error code"), 0);
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