<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\UserCard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JWTAuth;
use phpDocumentor\Reflection\Types\Null_;
use Validator;

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
        $user_card_table = (new UserCard)->getTable();
        $cards = UserCard::leftJoin('banks as b', 'b.id', '=', $user_card_table.'.bank')
            ->where('user_id', '=', $this->user->id)
            ->select($user_card_table.'.*','b.name as bank_name','b.logo as bank_logo')->get();
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
                $data[] = [
                    'card_id' => $item->id,
                    'card_num' => $this->formatNum($item->card_num), //做掩码处理
                    'bank' => $item->bank_name,
                    'card_type' => $card_type,
                    'card_logo' => $item->bank_logo,
                ];
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
     *     name="name",
     *     in="formData",
     *     description="持卡人姓名",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="id",
     *     in="formData",
     *     description="持卡人身份证ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="bank",
     *     in="formData",
     *     description="开户银行名称",
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
                'name' => 'bail|required',
                'id' => 'bail|required|size:18',
                'bank' => 'bail|required',
                'mobile' => 'bail|required|digits:11',
            ],
            [
                'required' => trans('trans.required'),
                'digits_between' =>trans('trans.digits_between'),
                'size' => trans('trans.size'),
            ]
        );
        if ($validator->fails()) {
            return response()->json(['code' => 0,'msg' => $validator->errors()->first(),'data' => []]);
        }

        $card_num = $request->input('card_num');
        $bank = $request->input('bank');
        $holder_name = $request->input('name');
        $holder_id = $request->input('id');
        $holder_mobile = $request->input('mobile');

        //验证卡号？身份证号？手机号？

        //同一用户只能绑定一次
        $card_list = UserCard::where('user_id',$this->user->id)->where('card_num',$card_num)->first();
        if (!empty($card_list) && count($card_list)>0) {
            return response()->json(['code' => 0,'msg' => '已经绑定的银行卡不能重复绑定','data' => []]);
        }

        $cards = new UserCard();
        $cards->user_id = $this->user->id;
        $cards->card_num = $card_num;
        $cards->bank = $bank;
        $cards->holder_name = $holder_name;
        $cards->holder_id = $holder_id;
        $cards->holder_mobile = $holder_mobile;
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
        $user_card = UserCard::where('id',$card_id)->where('user_id',$this->user->id)->first();
        if (!empty($user_card) && count($user_card)>0) {
            $user_card->delete();
            //如果银行卡都解绑了，要把结算卡清零
            $user_card_count = UserCard::where('id',$this->user->id)->count();
            if($user_card_count==0) {
                User::where('id',$this->user->id)->update(['pay_card_id'=>NULL]);
            }
            return response()->json(['code'=>1,'msg'=>'','data'=>[]]);
        } else {
            return response()->json(['code'=>0,'msg'=>'您未绑定该卡','data'=>[]]);
        }
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
