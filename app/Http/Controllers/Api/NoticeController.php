<?php

namespace App\Http\Controllers\Api;

use App\Notice;
use App\Profit;
use App\Transfer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use PhpParser\Node\Expr\New_;
use Validator;

class NoticeController extends Controller
{
//    public function __construct()
//    {
//        $this->middleware("jwt.auth");
//    }

    //消息列表
    /**
     * @SWG\GET(
     *   path="/notice/index",
     *   summary="消息列表",
     *   tags={"消息"},
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $notice = Notice::whereIn('user_id', [$this->user->id,0])->select()->get();
        $list = [];
        if (!empty($notice) && count($notice)>0) {
            foreach($notice as $item) {
                $thumb = '';
                if($item->type == 1) { //分润
                    $profit_table = (new Profit)->getTable();
                    $profit = Profit::leftJoin('users as u', 'u.id', '=', $profit_table.'.user_id')
                        ->where($profit_table.'.id',$item->param)->select('u.mobile as mobile')->first();
                    if(empty($profit)) {
                        continue;
                    }
                    $thumb = $profit->thumb??'default.png';
                }
                $list[$item->type][] = [
                    'notice_id' => $item->id,
                    'thumb'=> $thumb,
                    'content' => $item->content,
                    'title' => $item->title,
                    'created_at' => (string)$item->created_at,
                ];
            }
        }
        $data = [
            [
                'type'=>'1',
                'name'=>'分润通知',
                'notices'=> isset($list[1])?$list[1]:[],
            ],
            [
                'type'=>'2',
                'name'=>'用户注册',
                'notices'=>isset($list[2])?$list[2]:[],
            ],
            [
                'type'=>'3',
                'name'=>'系统通知',
                'notices'=>isset($list[3])?$list[3]:[],
            ],
        ];
        return response()->json(['code' => 1,'msg' => '','data' => $data]);
    }

    /**
     * @SWG\Post(
     *   path="/notice/create",
     *   summary="新建消息",
     *   tags={"消息"},
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="formData",
     *     description="接收消息的用户ID",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="type",
     *     in="formData",
     *     description="消息类型：1：分润，2：用户注册，3：系统",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="content",
     *     in="formData",
     *     description="消息内容",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="title",
     *     in="formData",
     *     description="消息标题，根据type，默认值分别为 1：分润通知，2：用户注册，3：系统通知",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="param",
     *     in="formData",
     *     description="参数，当type=1时，代表分润id，必填",
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
        $validator = Validator::make($request->all(),
            [
                'user_id' => 'bail|required|array',
                'type' => 'bail|required',
                'content' => 'bail|required|',
            ],
            [
                'required' => trans('trans.required'),
                'array' => ':attribute必须为数组',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['code' => 0,'msg' => $validator->errors()->first(),'data' => []]);
        }
        $user_id_arr = $request->input('user_id');
        $type = $request->input('type');
        $content = $request->input('content');
        $title = $request->input('title');
        if(empty($title)) {
            switch ($type) {
                case 1:
                    $title = '分润通知';
                    break;
                case 2:
                    $title = '用户注册';
                    break;
                case 3:
                    $title = '系统通知';
                    break;
                default:
                    $title = '通知';
                    break;
            }
        }
        $param = $request->input('param');
        if ($type==1 && empty($param)) {
            return response()->json(['code' => 0,'msg' => '缺少参数param','data' => []]);
        }
        $data = [];
        $time = date('Y-m-d H:i:s');
        foreach ($user_id_arr as $user_id) {
            $data[] = [
                'user_id' => $user_id,
                'type' => $type,
                'title' => $title,
                'content' => $content,
                'param' => $param,
                'created_at' => $time,
                'updated_at' => $time,
            ];
        }
        DB::beginTransaction();
        try {
            DB::table('notices')->insert($data);
            DB::commit();
            return response()->json(['code' => 1,'msg' => '','data' => []]);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 0,'msg' => '数据插入失败','data' => []]);
        }
    }

    /**
     * @SWG\Post(
     *   path="/notice/detail",
     *   summary="详情",
     *   tags={"消息"},
     *   @SWG\Parameter(
     *     name="notice_id",
     *     in="formData",
     *     description="消息ID",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function detail(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(),
            [
                'notice_id' => 'bail|required|numeric',
            ],
            [
                'required' => trans('trans.required'),
                'numeric' => trans('trans.numeric'),
            ]
        );
        if ($validator->fails()) {
            return response()->json(['code' => 0,'msg' => $validator->errors()->first(),'data' => []]);
        }
        $notice_id = $request->input('notice_id');
        $notice = Notice::where('id', $notice_id)->where('user_id', $this->user->id)->first();
        if (empty($notice)) {
            return response()->json(['code' => 0,'msg' => '消息不存在','data' => []]);
        }

        $data = [];
        if($notice->type == 1) {
            $profit_table = (new Profit)->getTable();
            $profit = Profit::leftJoin('users as u', 'u.id', '=', $profit_table.'.user_id')
                ->leftJoin('transfer_record as tr', 'tr.id', '=', $profit_table.'.record_id')
                ->where($profit_table.'.id',$notice->param)
                ->select($profit_table.'.*','u.mobile as mobile','tr.transfer_id as transfer_id')->first();
            if (empty($profit)) {
                return response()->json(['code' => 0,'msg' => '分润不存在','data' => []]);
            }
            $data = [
                'amount' => $profit->proxy_amount,
                'type' => $notice->type,
                'time' => (string)$profit->created_at,
                'transfer_id' => $profit->transfer_id,
                'mobile' => $profit->mobile,
                'thumb' => $profit->thumb??'default.png',
            ];
        } else {
            $data = [
                'time' => (string)$notice->created_at,
                'content'=> $notice->content,
                'title' => $notice->title
            ];
        }


        return response()->json(['code' => 1,'msg' => '','data' => $data]);
    }

    /**
     * @SWG\Post(
     *   path="/notice/delete",
     *   summary="清空消息",
     *   tags={"消息"},
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function delete(){
        $this->user = JWTAuth::parseToken()->authenticate();
        Notice::where('user_id',$this->user->id)->where('created_at','<',date('Y-m-d H:i:s'))->delete();
        return response()->json(['code' => 1,'msg' => '','data' => []]);
    }
}
