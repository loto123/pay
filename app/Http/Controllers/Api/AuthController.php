<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * @resource 验证
 *
 * 验证授权
 *
 * 使用token验证
 * "Authorization: token"
 * 或
 * http://url/?token=token
 *
 * @package App\Http\Controllers\Api
 */
class AuthController extends Controller {

    /**
     * 登录
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request){
        $credentials = $request->only('mobile', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
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
     * 注册
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mobile' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        $success['token'] =  JWTAuth::fromUser($user);
        $success['name'] =  $user->name;

        return response()->json(['success'=>$success]);
    }

}