<?php

namespace App\Http\Controllers\Api;

use App\Bank;
use App\User;
use App\UserCard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use JWTAuth;
use Validator;

class UserController extends Controller
{
    //
//    public function __construct()
//    {
//        $this->middleware("jwt.auth");
//    }

    /**
     * @SWG\GET(
     *   path="/my/index",
     *   summary="我的列表",
     *   tags={"我的"},
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $this->user = JWTAuth::parseToken()->authenticate();
        $user_card_count = UserCard::where('user_id',$this->user->id)->count();
        $parent = User::find($this->user->parent_id);
        $parent_name = '';
        $parent_mobile = '';
        if (!empty($parent) && count($parent)>0) {
            $parent_name = $parent->name;
            $parent_mobile = $parent->mobile;
        }
        return response()->json(['code'=>1,'msg'=>'','data'=>
            [
                'identify_status'=>$this->user->identify_status,
                'card_count'=> $user_card_count,
                'parent_name' => $parent_name,
                'parent_mobile' => $parent_mobile,
                'has_pay_password' => empty($this->user->pay_password) ? 0 : 1,
            ]]);
    }

    /**
     * @SWG\Post(
     *   path="/my/updatePassword",
     *   summary="修改登录密码",
     *   tags={"我的"},
     *   @SWG\Parameter(
     *     name="old_password",
     *     in="formData",
     *     description="旧密码",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="confirm_password",
     *     in="formData",
     *     description="确认密码",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="new_password",
     *     in="formData",
     *     description="新密码",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
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

    /**
     * @SWG\Post(
     *   path="/my/setPayPassword",
     *   summary="设置支付密码",
     *   tags={"我的"},
     *   @SWG\Parameter(
     *     name="pay_password",
     *     in="formData",
     *     description="支付密码",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
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

    /**
     * @SWG\Post(
     *   path="/my/updatePayPassword",
     *   summary="修改支付密码",
     *   tags={"我的"},
     *   @SWG\Parameter(
     *     name="old_pay_password",
     *     in="formData",
     *     description="旧支付密码",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="confirm_pay_password",
     *     in="formData",
     *     description="确认支付密码",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="new_pay_password",
     *     in="formData",
     *     description="新支付密码",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
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
        if(empty($this->user->pay_password)) {
            return response()->json(['code' => 0,'msg' => '请先设置支付密码','data' => []]);
        }
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

    /**
     * @SWG\Post(
     *   path="/my/updatePayCard",
     *   summary="更换结算卡",
     *   tags={"我的"},
     *   @SWG\Parameter(
     *     name="card_id",
     *     in="formData",
     *     description="银行卡id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
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
        if(empty($this->user->pay_password)) {
            return response()->json(['code' => 0,'msg' => '请先设置支付密码','data' => []]);
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

    /**
     * @SWG\GET(
     *   path="/my/getPayCard",
     *   summary="查看结算卡",
     *   tags={"我的"},
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function getPayCard()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        if(empty($this->user->pay_card_id)) {
           return response()->json(['code'=>0,'msg'=>'请先绑定银行卡','data'=>[]]);
        }
//        if(empty($this->user->pay_password)) {
//            return response()->json(['code' => 0,'msg' => '请先设置支付密码','data' => []]);
//        }
        $user_card = UserCard::find($this->user->pay_card_id);
        $bank = Bank::find($user_card->bank);
        $data = [
            'user_mobile' => $this->user->mobile,
            'holder_name' => $user_card->holder_name,
            'holder_id' => $this->formatNum($user_card->holder_id,6,4),
            'card_num' => $this->formatNum($user_card->card_num,6,4),
            'bank' => $bank->name,
        ];
        return response()->json(['code' => 1,'msg' => '','data' => $data]);
    }

    /**
     * @SWG\Post(
     *   path="/my/identify",
     *   summary="实名认证",
     *   tags={"我的"},
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     description="姓名",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="id_number",
     *     in="formData",
     *     description="身份证号",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="code",
     *     in="formData",
     *     description="验证码",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function identify(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(),
            [
                'name' => 'bail|required',
                'id_number' => 'bail|required|size:18',
                'code' => 'bail|required',
            ],
            [
                'required' => trans('trans.required'),
                'size' => trans('trans.size'),
            ]
        );
        if ($validator->fails()) {
            return response()->json(['code' => 0,'msg' => $validator->errors()->first(),'data' => []]);
        }

        Log::info(['param'=>$request->all()]);

        $name = $request->input('name');
        $id_number = $request->input('id_number');
        $cache_key = "SMS_".$request->mobile;
        $cache_value = Cache::get($cache_key);
        Log::info(['cache'=>$cache_value]);
        if (!$cache_value || !isset($cache_value['code']) || !$cache_value['code'] || $cache_value['code'] != $request->code || $cache_value['time'] < (time() - 300)) {
            return response()->json(['code' => 0, 'msg' =>'验证码已失效或填写错误', 'data' => []]);
        }
        //调用实名认证接口

        if(true) {
            User::where('id',$this->user->id)->update([
                'identify_status' => 1,
                'name' => $name,
                'id_number' => $id_number,
            ]);
            return response()->json(['code' => 1, 'msg' =>'', 'data' => []]);
        }
        return response()->json(['code' => 0, 'msg' =>'', 'data' => []]);

    }

    /**
     * @SWG\GET(
     *   path="/my/info",
     *   summary="用户信息",
     *   tags={"我的"},
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function info()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $data = [
            'name' => $this->user->name,
            'mobile' => $this->user->mobile,
            'thumb' => '1.png',
            'has_pay_password' => empty($this->user->pay_password) ? 0 : 1,
        ];
        return response()->json(['code' => 1, 'msg' =>'', 'data' => $data]);
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

    /**
     * @SWG\Post(
     *   path="/my/pay_password",
     *   summary="支付密码验证",
     *   tags={"我的"},
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="支付密码",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function pay_password(Request $request) {
        $validator = Validator::make($request->all(),
            ['password' => 'bail|required']
        );
        if ($validator->fails()) {
            return response()->json(['code' => 0,'msg' => $validator->errors()->first(),'data' => []]);
        }
        $user = JWTAuth::parseToken()->authenticate();
        if (!Hash::check($request->password, $user->pay_password)) {
            return response()->json(['code' => 0,'msg' => trans("api.error_pay_password"),'data' => []]);
        } else {
            return response()->json(['code' => 1,'msg' => '','data' => []]);
        }
    }

}
