<?php

namespace App\Http\Controllers\Api;

use App\Bank;
use App\Pay\Impl\Heepay\SmallBatchTransfer;
use App\User;
use App\UserCard;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuth;
use Validator;
use Illuminate\Support\Facades\Log;

class CardController extends Controller
{
//    public function __construct()
//    {
//        $this->middleware("jwt.auth");
//    }

    /**
     * @SWG\GET(
     *   path="/card/index",
     *   summary="银行卡列表",
     *   tags={"我的"},
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        if($this->user->identify_status != 1) {
            return response()->json(['code'=>0,'msg'=>'未实名认证，该功能不可用','data'=>[]]);
        }
        $user_card_table = (new UserCard)->getTable();
        $cards = UserCard::leftJoin('banks as b', 'b.id', '=', $user_card_table.'.bank_id')
            ->where('user_id', '=', $this->user->id)
            ->select($user_card_table.'.*','b.name as bank_name','b.logo as bank_logo')
            ->orderBy('id')->get();
        $data = [];
        if( !empty($cards) && count($cards)>0 ) {
            foreach ($cards as $item) {
                $card_type = '';
                switch ($item->type) {
                    case 1:
                        $card_type = '储蓄卡';
                        break;
                    case 2:
                        $card_type = '信用卡';
                        break;
                }
                $data[$item->id] = [
                    'card_id' => $item->id,
                    'card_num' => $this->formatNum($item->card_num), //做掩码处理
                    'bank' => $item->bank_name,
                    'card_type' => $card_type,
                    'card_logo' => $item->bank_logo,
                    'is_pay_card' => ($item->id == $this->user->pay_card_id)? 1:0,
                ];
            }
            if( isset($data[$this->user->pay_card_id]) ) {
                $item = $data[$this->user->pay_card_id];
                unset($data[$this->user->pay_card_id]);
                array_unshift($data, $item);
            }
        }
        return response()->json(['code'=>1,'msg'=>'','data'=>$data]);
    }

    /**
     * @SWG\Post(
     *   path="/card/create",
     *   summary="绑定银行卡",
     *   tags={"我的"},
     *   @SWG\Parameter(
     *     name="card_num",
     *     in="formData",
     *     description="银行卡号",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="bank_id",
     *     in="formData",
     *     description="银行id",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="mobile",
     *     in="formData",
     *     description="银行卡绑定手机号",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="code",
     *     in="formData",
     *     description="手机验证码",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="province",
     *     in="formData",
     *     description="开户行所属省份",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="city",
     *     in="formData",
     *     description="开户行所属市",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="branch",
     *     in="formData",
     *     description="开户行所属支行",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        //字段验证
        $validator = Validator::make($request->all(),
            [
                'card_num' => 'bail|required|digits_between:16,19',
                'bank_id' => 'bail|required',
                'mobile' => 'required|regex:/^1[34578][0-9]{9}$/',
                'code' => 'bail|required',
                'province' => 'bail|required',
                'city' => 'bail|required',
            ],
            [
                'required' => trans('trans.required'),
                'digits_between' =>trans('trans.digits_between'),
                'mobile.regex'=>trans("api.error_mobile_format"),
            ]
        );

//        Log::info(['param'=>$request->all()]);
        if ($validator->fails()) {
            return response()->json(['code' => 0,'msg' => $validator->errors()->first(),'data' => []]);
        }
        $cache_key = "SMS_".$request->mobile;
        $cache_value = Cache::get($cache_key);
//        Log::info(['cache'=>[$cache_key=>$cache_value]]);
        if (!$cache_value || !isset($cache_value['code']) || !$cache_value['code'] || $cache_value['code'] != $request->code || $cache_value['time'] < (time() - 300)) {
             return response()->json(['code' => 0, 'msg' =>'验证码已失效或填写错误', 'data' => []]);
        }

        //同一用户只能绑定一次
        $card_list = UserCard::where('user_id',$this->user->id)->where('card_num',$request->card_num)->first();
        if (!empty($card_list) && count($card_list)>0) {
            return response()->json(['code' => 0,'msg' => '已经绑定的银行卡不能重复绑定','data' => []]);
        }

        $cards = new UserCard();
        $cards->user_id = $this->user->id;
        $cards->card_num = $request->card_num;
        $cards->bank_id = $request->bank_id;
        $cards->holder_name = $this->user->name;
        $cards->holder_id = $this->user->id_number;
        $cards->holder_mobile = $request->mobile;
        $cards->province = $request->province;
        $cards->city = $request->city;
        $cards->branch = $request->branch??NULL;
        $cards->save();
        if(empty($this->user->pay_card_id)) {
            $this->user->pay_card_id =$cards->id;
            $this->user->save();
        }
        return response()->json(['code' => 1,'msg' => '','data' => []]);
    }

    /**
     * @SWG\Post(
     *   path="/card/delete",
     *   summary="解绑银行卡",
     *   tags={"我的"},
     *   @SWG\Parameter(
     *     name="card_id",
     *     in="formData",
     *     description="银行卡ID",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(),
            ['card_id' => 'required|numeric'],
            [
                'required' => trans('trans.required'),
                'numeric' => trans('trans.numeric'),
            ]
        );
        if ($validator->fails()) {
            return response()->json(['code'=>0,'msg'=>$validator->errors()->first(),'data'=>[]]);
        }
        $card_id = $request->input('card_id');

        $card = UserCard::where('id',$card_id)->where('user_id',$this->user->id)->first();
        $user_card_count = UserCard::where('user_id',$this->user->id)->count();
        if ( !empty($card) && count($card)>0 && $user_card_count>0) {
            if ($user_card_count == 1){
                $card->delete();
                User::where('id',$this->user->id)->update(['pay_card_id'=>NULL]);
                return response()->json(['code'=>1,'msg'=>'','data'=>[]]);
            }else if($this->user->pay_card_id == $card_id) {
                return response()->json(['code'=>0,'msg'=>'操作无效，请先更换结算卡','data'=>[]]);
            }else {
                $card->delete();
                return response()->json(['code'=>1,'msg'=>'','data'=>[]]);
            }
        } else {
            return response()->json(['code'=>0,'msg'=>'您未绑定该卡','data'=>[]]);
        }
    }

    /**
     * @SWG\GET(
     *   path="/card/getBanks",
     *   summary="银行列表",
     *   tags={"我的"},
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function getBanks() {
        $query = Bank::query()->select()->get();
        $data = [];
        if(!empty($query) && count($query)>0) {
            foreach ($query as $item) {
                $data[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                ];
            }
        }
        return response()->json(['code'=>1,'msg'=>'','data'=>$data]);
    }

    /**
     * @SWG\GET(
     *   path="/card/getBankCardParams",
     *   summary="添加银行卡支付通道需要参数",
     *   tags={"我的"},
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function getBankCardParams() {
        $this->user = JWTAuth::parseToken()->authenticate();
        $data = [];
        switch($this->user->channel_id) {
            case 1:
            $data = SmallBatchTransfer::queryProvincesAndCities();
                break;
            default :
            break;
        }
        return response()->json(['code'=>1,'msg'=>'','data'=>$data]);
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
