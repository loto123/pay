<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Swagger\Annotations as SWG;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * @SWG\Swagger(
 *   basePath="/api",
 *   @SWG\Info(
 *     title="游戏宝接口列表",
 *     version="0.0.1"
 *   )
 * )
 * @package App\Http\Controllers\Api
 */
class AuthController extends Controller {

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
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // all good so return the token
        return response()->json(compact('token'));
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
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mobile' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        $success['token'] = JWTAuth::fromUser($user);
        $success['name'] = $user->name;

        return response()->json(['success' => $success]);
    }

}