<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\UserCard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
    public function index(Request $request) {
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
    public function updatePassword(Request $request) {
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

    //设置支付密码
    public function setPayPassword(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(),
            [
                'pay_password' => 'bail|required|digits:6',
            ],
            [
                'required' => trans('trans.required'),
                'digits' => trans('trans.digits'),
            ]
        );
        if($validator->fails()) {
            return response()->json(['code' => 0,'msg' => $validator->errors()->first(),'data' => []]);
        }

        $user_pay_password = User::find($this->user->id)->pay_password;
        if(!empty($user_pay_password)) {
            return response()->json(['code' => 0,'msg' => '您已经设置过支付密码了','data' => []]);
        }

        $pay_password = $request->input('pay_password');
        User::where('id',$this->user->id)->update(['pay_password'=>bcrypt($pay_password)]);
        return response()->json(['code' => 1,'msg' => '','data' => []]);
    }

    //修改支付密码
    public function updatePayPassword(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(),
            [
                'old_pay_password' => 'bail|required|digits:6',
                'new_pay_password' => 'bail|required|digits:6',
            ],
            [
                'required' => trans('trans.required'),
            ]
        );

        if ($validator->fails()) {
            return response()->json(['code' => 0,'msg' => $validator->errors()->first(),'data' => []]);
        }

        $old_password = $request->input('old_pay_password');
        $new_password = $request->input('new_pay_password');
        $confirm_password = $request->input('confirm_pay_password');
        //验证两次密码
        if(isset($confirm_password)) {
            if ($confirm_password != $new_password) {
                return response()->json(['code' => 0,'msg' => '两次新密码输入不一致！','data' => []]);
            }
        }
        //验证旧密码
        if (!Hash::check($old_password,$this->user->pay_password)) {
            return response()->json(['code' => 0,'msg' => '原密码输入错误！','data' => []]);
        }
        //更新密码
        User::where('id',$this->user->id)->update(['pay_password'=>bcrypt($new_password)]);
        return response()->json(['code' => 1,'msg' => '','data' => []]);
    }

    //更换结算卡
    public function updatePayCard(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(),
            [
                'card_id' => 'bail|required|numeric',
            ],
            [
                'required' => trans('trans.required'),
                'numeric' => trans('trans.numeric'),
            ]
        );
        if ($validator->fails()) {
            return response()->json(['code' => 0,'msg' => $validator->errors()->first(),'data' => []]);
        }
        $card_id = $request->input('card_id');
        $user_card = UserCard::where('id',$card_id)->where('user_id',$this->user->id)->first();
        if (empty($user_card) || count($user_card)==0) {
            return response()->json(['code' => 0,'msg' => '您没有绑定该卡','data' => []]);
        }
        $this->user->pay_card_id = $card_id;
        $this->user->save();
        return response()->json(['code' => 1,'msg' => '','data' => []]);

    }

    //查看结算卡
    public function GetPayCard()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        if(empty($this->user->pay_card_id)) {
           return response()->json(['code'=>0,'msg'=>'请先绑定银行卡','data'=>[]]);
        }
        $user_card = UserCard::find($this->user->pay_card_id);
        $data = [
            'user_mobile' => $this->user->mobile,
            'holder_name' => $user_card->holder_name,
            'holder_id' => $this->formatNum($user_card->holder_id,6,4),
            'card_num' => $this->formatNum($user_card->card_num,6,4),
            'bank' => $user_card->bank
        ];
        return response()->json(['code' => 1,'msg' => '','data' => $data]);
    }

    //对字符串做掩码处理
    private function formatNum($num,$pre=0,$suf=4)
    {
        $prefix = '';
        $suffix = '';
        if($pre>0) {
            $prefix = substr($num, 0, $pre);
        }
        if ($suf>0){
            $suffix = substr($num, 0-$suf, $suf);
        }
        $maskBankCardNo = $prefix . str_repeat('*', strlen($num)-$pre-$suf) . $suffix;
        $maskBankCardNo = rtrim(chunk_split($maskBankCardNo, 4, ' '));
        return $maskBankCardNo;
    }

}
