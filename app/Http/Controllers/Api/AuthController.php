<?php

namespace App\Http\Controllers\Api;

use App\OauthUser;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Swagger\Annotations as SWG;
use Tymon\JWTAuth\Facades\JWTAuth;
use EasyWeChat;
//use SmsManager;

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
        return $this->json(compact('token'));
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
     *         required=true,
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
            'name' => 'required',
            'mobile' => 'required|regex:/^1[34578][0-9]{9}$/|unique:'.(new User)->getTable(),
            'invite_mobile' => 'required|regex:/^1[34578][0-9]{9}$/|exists:'.(new User)->getTable().',mobile',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        $success['token'] = JWTAuth::fromUser($user);
        $success['name'] = $user->name;
        if ($request->oauth_user) {
            $oauth_user = OauthUser::find($request->oauth_user);
            if ($oauth_user) {
                $oauth_user->user_id = $user->id;
                $oauth_user->save();
            }
        }

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
        $validator = Validator::make($request->all(), [
            'mobile' => 'regex:/^1[34578][0-9]{9}$/|unique:'.(new User)->getTable(),
            'invite_mobile' => 'regex:/^1[34578][0-9]{9}$/|exists:'.(new User)->getTable().',mobile',
        ], ['mobile.regex'=>trans("api.error_mobile_format"), 'invite_mobile.regex'=>trans("api.error_invite_mobile_format"), 'mobile.unique' => trans("api.user_exist"), 'invite_mobile.exists' => trans("api.invite_unexist")]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        return $this->json();
    }

//    public function sms() {
//        $result = Sms::make('SMS_75755134')->to('17673055987')->data(['vcode' => '12345'])->send();
//        var_dump($result);
//    }

}