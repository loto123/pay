<?php

namespace App\Http\Controllers\Api;

use App\Bank;
use App\Pay\Impl\ALiPay\Auth;
use App\Pay\Impl\Heepay\Heepay;
use App\Pay\Impl\Heepay\Reality;
use App\Pay\Impl\Showapi\Showapi;
use App\PayInterfaceRecord;
use App\User;
use App\UserCard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use JWTAuth;
use Validator;

class UserController extends BaseController
{
    /**
     * @SWG\GET(
     *   path="/my/index",
     *   summary="我的列表",
     *   tags={"我的"},
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
     *                  @SWG\Property(property="identify_status", type="integer", example=1,description="实名认证状态:1 已认证，0 未认证"),
     *                  @SWG\Property(property="card_count", type="integer", example=0,description="已绑定银行卡张数"),
     *                  @SWG\Property(property="parent_name", type="string", example="张三",description="推荐人姓名"),
     *                  @SWG\Property(property="parent_mobile", type="string", example="13333333333",description="推荐人手机号"),
     *                  @SWG\Property(property="has_pay_password", type="integer", example=1,description="是否设置支付密码：1 已设置，0 未设置"),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     *
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
        return $this->json(
            [
                'identify_status'=>$this->user->identify_status,
                'card_count'=> $user_card_count,
                'parent_name' => $parent_name,
                'parent_mobile' => $parent_mobile,
                'has_pay_password' => empty($this->user->pay_password) ? 0 : 1,
            ]);
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
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="new_password",
     *     in="formData",
     *     description="新密码",
     *     required=true,
     *     type="string"
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
    public function updatePassword(Request $request) {
        $this->user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(),
            [
                'old_password' => 'bail|required',
                'new_password' => 'bail|required|min:8|max:16',
                'confirm_password' => 'bail|required|min:8|max:16',
            ],
            [
                'required' => trans('trans.required'),
                'min' => trans('trans.min'),
                'max' => trans('trans.max'),
            ]
        );

        if ($validator->fails()) {
            return $this->json([],$validator->errors()->first(),0);
        }

        $old_password = $request->input('old_password');
        $new_password = $request->input('new_password');
        $confirm_password = $request->input('confirm_password');
        //验证两次密码
        if ($confirm_password != $new_password) {
            return $this->json([], '两次新密码输入不一致！',0);
        }
        //验证旧密码
        if (!Hash::check($old_password,$this->user->password)) {
            return $this->json([],'原密码输入错误！',0);
        }
        //更新密码
        User::where('id',$this->user->id)->update(['password'=>bcrypt($new_password)]);
        return $this->json([],'',1);
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
     *   @SWG\Response(
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
            return $this->json([],$validator->errors()->first(),0);
        }

        $user_pay_password = User::find($this->user->id)->pay_password;
        if(!empty($user_pay_password)) {
            return $this->json([],'您已经设置过支付密码了',0);
        }

        $pay_password = $request->input('pay_password');
        User::where('id',$this->user->id)->update(['pay_password'=>bcrypt($pay_password)]);
        return $this->json();
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
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="new_pay_password",
     *     in="formData",
     *     description="新支付密码",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
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
    public function updatePayPassword(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(),
            [
                'old_pay_password' => 'bail|required|digits:6',
                'new_pay_password' => 'bail|required|digits:6',
                'confirm_pay_password' => 'bail|required|digits:6',
            ],
            [
                'required' => trans('trans.required'),
            ]
        );

        if ($validator->fails()) {
            return $this->json([],$validator->errors()->first(),0);
        }

        $old_password = $request->input('old_pay_password');
        $new_password = $request->input('new_pay_password');
        $confirm_password = $request->input('confirm_pay_password');
        if(empty($this->user->pay_password)) {
            return $this->json([],'请先设置支付密码',0);
        }
        //验证两次密码
        if ($confirm_password != $new_password) {
            return $this->json([],'两次新密码输入不一致',0);
        }
        //验证旧密码
        if (!Hash::check($old_password,$this->user->pay_password)) {
            return $this->json([],'原密码输入错误',0);
        }
        //更新密码
        User::where('id',$this->user->id)->update(['pay_password'=>bcrypt($new_password)]);
        return $this->json();
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
     *   @SWG\Response(
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
            return $this->json([],$validator->errors()->first(),0);
        }
        if(empty($this->user->pay_password)) {
            return $this->json([],'请先设置支付密码',0);
        }
        $card_id = $request->input('card_id');
        $user_card = UserCard::where('id',$card_id)->where('user_id',$this->user->id)->first();
        if (empty($user_card) || count($user_card)==0) {
            return $this->json([],'您没有绑定该卡',0);
        }
        $this->user->pay_card_id = $card_id;
        $this->user->save();
        return $this->json();
    }

    /**
     * @SWG\GET(
     *   path="/my/getPayCard",
     *   summary="查看结算卡",
     *   tags={"我的"},
     *   @SWG\Response(
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
     *                  @SWG\Property(property="user_mobile", type="string", example="13333333333",description="用户手机号(账号)"),
     *                  @SWG\Property(property="holder_name", type="string", example="张三",description="持卡人姓名"),
     *                  @SWG\Property(property="holder_id", type="string", example="123456******1234",description="持卡人身份证号"),
     *                  @SWG\Property(property="card_num", type="string", example="123456******1234",description="银行卡号"),
     *                  @SWG\Property(property="bank", type="string", example="中国银行",description="开户行名称"),
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
    public function getPayCard()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        if(empty($this->user->pay_card_id)) {
            return $this->json([],'请先绑定银行卡',0);
        }
        $user_card = UserCard::find($this->user->pay_card_id);
        $bank = Bank::find($user_card->bank_id);
        $data = [
            'user_mobile' => $this->user->mobile,
            'holder_name' => $user_card->holder_name,
            'holder_id' => $this->formatNum($user_card->holder_id,6,4),
            'card_num' => $this->formatNum($user_card->card_num,6,4),
            'bank' => $bank->name,
        ];
        return $this->json($data);
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
     *   @SWG\Response(
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
            return $this->json([], $validator->errors()->first(), 0);
        }
//        Log::info(['param'=>$request->all()]);
        $name = $request->input('name');
        $id_number = $request->input('id_number');
        $cache_key = "SMS_".$this->user->mobile;
        $cache_value = Cache::get($cache_key);
//        Log::info(['cache'=>[$cache_key=>$cache_value]]);
        if (!$cache_value || !isset($cache_value['code']) || !$cache_value['code'] || $cache_value['code'] != $request->code || $cache_value['time'] < (time() - 300)) {
            return $this->json([], '验证码已失效或填写错误', 0);
        }
        Cache::forget($cache_key);
        //添加记录
        $bill_id = CardController::createUniqueId();
        try{
            $pay_record = new PayInterfaceRecord();
            $pay_record->bill_id = $bill_id;
            $pay_record->user_id = $this->user->id;
            $pay_record->type = UserCard::IDENTIFY_TYPE;
//            $pay_record->platform = Heepay::PLATFORM;
            $pay_record->platform = Showapi::PLATFORM;
            $pay_record->save();
        } catch (\Exception $e) {
            return $this->json([], '记录无法生成', 0);
        }
        //调用实名认证接口
        if(!config('app.debug')) {
            $reality_res = Showapi::identify($pay_record->id,$name,$id_number);
//            $reality_res = Reality::identify($pay_record->id,$name,$id_number);
        } else {
            $reality_res = true;
        }
        if($reality_res === true) {
            User::where('id',$this->user->id)->update([
                'identify_status' => 1,
                'name' => $name,
                'id_number' => $id_number,
            ]);
            return $this->json();
        }
        return $this->json([], '身份证号码与姓名不匹配，请核实后重新输入', 0);
    }

    /**
     * @SWG\GET(
     *   path="/my/info",
     *   summary="用户信息",
     *   tags={"我的"},
     *   @SWG\Response(
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
     *                  @SWG\Property(property="name", type="string", example="张三",description="昵称"),
     *                  @SWG\Property(property="mobile", type="string", example="13333333333",description="手机号"),
     *                  @SWG\Property(property="thumb", type="string", example="url",description="头像"),
     *                  @SWG\Property(property="has_pay_password", type="integer", example="1",description="是否已设置支付密码 1：已设置，0：未设置"),
     *                  @SWG\Property(property="id_number", type="string", example="123456******1234",description="身份证号"),
     *                  @SWG\Property(property="has_parent", type="integer", example="1",description="是否有推荐人 1:有，0：没有"),
     *                  @SWG\Property(property="parent_name", type="string", example="李四",description="推荐人昵称"),
     *                  @SWG\Property(property="parent_mobile", type="string", example="14444444444",description="推荐人手机号"),
     *                  @SWG\Property(property="pay_card_id", type="integer", example="1",description="结算卡id"),
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
    public function info()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $parent = User::find($this->user->parent_id);
        $data = [
            'name' => $this->user->name,
            'mobile' => $this->user->mobile,
            'thumb' => $this->user->avatar??'',
            'has_pay_password' => empty($this->user->pay_password) ? 0 : 1,
            'id_number' => $this->user->id_number ? str_replace(' ','',$this->formatNum($this->user->id_number,4,4)) : '',
            'has_parent'=> $this->user->parent_id>0 ? 1 : 0,
            'parent_name' => $parent->name??'',
            'parent_mobile' => $parent->mobile??'',
            'pay_card_id' => $this->user->pay_card_id??'',
        ];
        return $this->json($data);
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
     *   @SWG\Response(
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
     *         @SWG\Schema(
     *             @SWG\Property(
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
     *                  @SWG\Property(property="time", type="integer", example="1",description="当日剩余错误次数（当次数为0时，不会返回此字段）"),
     *              )
     *         )
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function pay_password(Request $request) {
        $validator = Validator::make($request->all(),
            ['password' => 'bail|required']
        );

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(),0);
        }
        $user = JWTAuth::parseToken()->authenticate();
        /* @var $user User */
        try {
            if (!$user->check_pay_password($request->password)) {
                return $this->json([], trans("api.error_pay_password"),0);
            }
        } catch (\Exception $e) {
            return $this->json([], $e->getMessage(),0);
        }
        return $this->json();
    }

    /**
     * @SWG\Post(
     *   path="/my/resetPayPassword",
     *   summary="忘记支付密码",
     *     tags={"我的"},
     *     @SWG\Parameter(
     *         name="mobile",
     *         in="formData",
     *         description="用户手机号",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="pay_password",
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
    public function resetPayPassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|regex:/^1[34578][0-9]{9}$/|exists:'.(new User)->getTable(),
            'pay_password' => 'required|digits:6',
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
        $user->pay_password = Hash::make($request->pay_password);
        $user->save();
        return $this->json();
    }

    /**
     * @SWG\GET(
     *   path="/my/parent",
     *   summary="推荐人信息",
     *   tags={"我的"},
     *   @SWG\Response(
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
     *                  @SWG\Property(property="name", type="string", example="张三",description="昵称"),
     *                  @SWG\Property(property="mobile", type="string", example="13333333333",description="手机号"),
     *                  @SWG\Property(property="avatar", type="string", example="url",description="头像")
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
    public function parent() {
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user->parent) {
            return $this->json([
                'avatar' => "",
                'name' => "",
                'mobile' => "",
            ]);
        }
        return $this->json([
            'avatar' => $user->parent->avatar,
            'name' => $user->parent->name,
            'mobile' => $user->parent->mobile,
        ]);
    }

}
