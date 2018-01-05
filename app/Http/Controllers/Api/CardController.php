<?php

namespace App\Http\Controllers\Api;

use App\Bank;
use App\Pay\Impl\Heepay\Heepay;
use App\Pay\Impl\Heepay\Reality;
use App\Pay\Impl\Heepay\SmallBatchTransfer;
use App\PayInterfaceRecord;
use App\User;
use App\UserCard;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Validator;
use Illuminate\Support\Facades\Log;

class CardController extends BaseController
{

    /**
     * @SWG\GET(
     *   path="/card/index",
     *   summary="银行卡列表",
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
     *                  type="array",
     *                  @SWG\Items(
     *                  @SWG\Property(property="card_id", type="integer", example="1",description="银行卡id"),
     *                  @SWG\Property(property="card_num", type="string", example="123456******1234",description="银行卡号"),
     *                  @SWG\Property(property="bank", type="string", example="中国银行",description="开户行"),
     *                  @SWG\Property(property="card_type", type="string", example="储蓄卡",description="卡类型"),
     *                  @SWG\Property(property="card_logo", type="string", example="url",description="银行卡logo"),
     *                  @SWG\Property(property="is_pay_card", type="integer", example=1,description="是否是结算卡 1：是，0：否"),
     *                  ),
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
    public function index()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        if($this->user->identify_status != 1) {
            return $this->json([],'未实名认证，该功能不可用',0);
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
        return $this->json($data);
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
    public function create(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        //字段验证
        $validator = Validator::make($request->all(),
            [
                'card_num' => 'bail|required|digits_between:16,19',
                'bank_id' => 'bail|required',
//                'mobile' => 'required|regex:/^1[34578][0-9]{9}$/',
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
            return $this->json([],$validator->errors()->first(),0);
        }
        $cache_key = "SMS_".$this->user->mobile;
        $cache_value = Cache::get($cache_key);
//        Log::info(['cache'=>[$cache_key=>$cache_value]]);
        if (!$cache_value || !isset($cache_value['code']) || !$cache_value['code'] || $cache_value['code'] != $request->code || $cache_value['time'] < (time() - 300)) {
            return $this->json([],'验证码已失效或填写错误',0);
        }
        Cache::forget($cache_key);

        //同一用户只能绑定一次
        $card_list = UserCard::where('user_id',$this->user->id)->where('card_num',$request->card_num)->first();
        if (!empty($card_list) && count($card_list)>0) {
            return $this->json([],'已经绑定的银行卡不能重复绑定',0);
        }

        if(!isset($this->user->channel['platform_id'])) {
            return $this->json([],'用户没有分配通道',0);
        }

        //添加记录
        $bill_id = $this->createUniqueId();
        try{
            $pay_record = new PayInterfaceRecord();
            $pay_record->bill_id = $bill_id;
            $pay_record->user_id = $this->user->id;
            $pay_record->type = UserCard::AUTH_TYPE;
            $pay_record->platform = Heepay::PLATFORM;
            $pay_record->save();
        } catch (\Exception $e) {
            return $this->json([],'记录无法生成',0);
        }
        //鉴权
        $auth_res = Reality::authentication(
            $pay_record->id,
            $bill_id,
            date('YmdHis'),
            $request->card_num,
            $this->user->id_number,
            $this->user->name
        );
        if ($auth_res !== true) {
            return $this->json([],$auth_res,0);
        }

        $cards = new UserCard();
        $cards->user_id = $this->user->id;
        $cards->card_num = $request->card_num;
        $cards->bank_id = $request->bank_id;
        $cards->holder_name = $this->user->name;
        $cards->holder_id = $this->user->id_number;
        $cards->holder_mobile = $this->user->mobile;
        $cards->province = $request->province;
        $cards->city = $request->city;
        $cards->branch = $request->branch??NULL;
        $cards->save();
        if(empty($this->user->pay_card_id)) {
            $this->user->pay_card_id =$cards->id;
            $this->user->save();
        }
        return $this->json();
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
            return $this->json([],$validator->errors()->first(),0);
        }
        $card_id = $request->input('card_id');

        $card = UserCard::where('id',$card_id)->where('user_id',$this->user->id)->first();
        $user_card_count = UserCard::where('user_id',$this->user->id)->count();
        if ( !empty($card) && count($card)>0 && $user_card_count>0) {
            if ($user_card_count == 1){
                $card->delete();
                User::where('id',$this->user->id)->update(['pay_card_id'=>NULL]);
                return $this->json();
            }else if($this->user->pay_card_id == $card_id) {
                return $this->json([],'操作无效，请先更换结算卡',0);
            }else {
                $card->delete();
                return $this->json();
            }
        } else {
            return $this->json([],'您未绑定该卡',0);
        }
    }

    /**
     * @SWG\GET(
     *   path="/card/getBanks",
     *   summary="银行列表",
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
     *                  @SWG\Property(property="id", type="integer", example="1",description="银行id"),
     *                  @SWG\Property(property="name", type="string", example="中国银行",description="银行名称")
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
    public function getBanks() {
        $this->user = JWTAuth::parseToken()->authenticate();
        $platform_bank = DB::table('pay_banks_support as pbs')->join('pay_channel as pc','pc.platform_id','=','pbs.platform_id')
            ->where('pc.id',$this->user->channel_id)->pluck('pbs.bank_id');
        $data = [];
        if (!empty($platform_bank) && count($platform_bank)>0){
            $query = Bank::whereIn('id',$platform_bank)->select()->get();
            if(!empty($query) && count($query)>0) {
                foreach ($query as $item) {
                    $data[] = [
                        'id' => $item->id,
                        'name' => $item->name,
                    ];
                }
            }
        }
        return $this->json($data);
    }

    /**
     * @SWG\GET(
     *   path="/card/getBankCardParams",
     *   summary="添加银行卡支付通道需要参数（开户行省市）",
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
     *                  type="array",
     *                  @SWG\Items(
     *                      @SWG\Property(property="湖南省", type="array",
     *                          @SWG\Items(
     *                              @SWG\Property(property="长沙市", type="string"),
     *                          )
     *                      ),
     *                  )
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

    //生成订单号
    public static function createUniqueId()
    {
        return date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }
}
