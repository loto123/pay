<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use JWTAuth;
use Validator;

class UserController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware("jwt.auth");
    }

    //“我的”列表
    public function account(Request $request) {
        $this->user = JWTAuth::parseToken()->authenticate();
        return response()->json(['code'=>1,'msg'=>'','data'=>[
            'info'=>[
                'thumb'=>'',
                'name' => $this->user->name,
                'mobile' => $this->user->mobile,
            ],
            'item'=>[
                ['name'=>'推荐人', 'content'=>'', 'url'=>'',],
                ['name'=>'银行卡管理', 'content'=>'', 'url'=>'',],
                ['name'=>'实名认证', 'content'=>'', 'url'=>'',],
                ['name'=>'查看结算卡', 'content'=>'', 'url'=>'',],
                ['name'=>'更多设置', 'content'=>'', 'url'=>'',],
            ],
        ]]);
    }

    //修改密码
    public function change_password(Request $request) {
        $this->user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(),
            [
                'old_password' => 'bail|required',
                'new_password' => 'bail|required|min:6|max:16',
            ],
            [
                'required' => trans('trans.required'),
                'min' => trans('trans.min'),
                'max' => trans('trans.max'),
            ]
        );

        if ($validator->fails()) {
            return response()->json(['code' => 0,'msg' => $validator->errors()->first(),'data' => []]);
        }

        $old_password = $request->input('old_password');
        $new_password = $request->input('new_password');
        $confirm_password = $request->input('confirm_password');
        //验证两次密码
        if(isset($confirm_password)) {
            if ($confirm_password != $new_password) {
                return response()->json(['code' => 0,'msg' => '两次新密码输入不一致！','data' => []]);
            }
        }
        //验证旧密码
        if (!Hash::check($old_password,$this->user->password)) {
            return response()->json(['code' => 0,'msg' => '原密码输入错误！','data' => []]);
        }
        //更新密码
        User::where('id',$this->user->id)->update(['password'=>bcrypt($new_password)]);
        return response()->json(['code' => 1,'msg' => '','data' => []]);
    }





}
