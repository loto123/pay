<?php

namespace App\Http\Controllers\Api;

use App\Notice;
use App\Profit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use JWTAuth;
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
     *   @SWG\Parameter(
     *     name="page",
     *     in="path",
     *     description="页码",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="size",
     *     in="path",
     *     description="数目",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();

        $notice_type = ['App\Notifications\ProfitApply','App\Notifications\UserApply','App\Notifications\SystemApply'];
        $notice = $this->user->unreadNotifications()->whereIn('type',$notice_type)->paginate($request->input('size', 20));
        $list = [];
        if (!empty($notice) && count($notice)>0) {
            foreach($notice as $item) {
                $thumb = '';
                $title = $item->data['title'];
                $content = $item->data['content'];
                if($item->type == 'App\Notifications\ProfitApply') { //分润
                    $profit_table = (new Profit)->getTable();
                    $profit = Profit::leftJoin('users as u', 'u.id', '=', $profit_table.'.user_id')
                        ->where($profit_table.'.id',$item->data['param'])->select('proxy_amount','u.mobile as mobile', 'u.avatar as avatar')->first();
                    if(empty($profit)) {
                        continue;
                    }
                    $thumb = $profit->avatar??'';
                    $title = $profit->mobile;
                    $content = $profit->proxy_amount;
                }
                $list[$item->data['type']][] = [
                    'type' => $item->data['type'],
                    'notice_id' => $item->id,
                    'title' => $title,
                    'content' => $content,
                    'created_at' => (string)$item->created_at,
                    'thumb'=> $thumb,
                ];
            }


        }
        return response()->json(['code' => 1,'msg' => '','data' => $list]);
    }

    /**
     * @SWG\Post(
     *   path="/notice/create",
     *   summary="新建消息",
     *   tags={"消息"},
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="formData",
     *     description="接收消息的用户ID数组",
     *     required=true,
     *     type="array",
     *     @SWG\Items(
     *             type="integer",
     *             format="int32"
     *      )
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
        $param = $request->input('param');
        if (\App\Admin\Controllers\NoticeController::send($user_id_arr,$type,$content,$title,$param)) {
            return response()->json(['code' => 1,'msg' => '','data' => []]);
        } else {
            return response()->json(['code' => 0,'msg' => '失败','data' => []]);
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
     *     type="string"
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
                'notice_id' => 'bail|required',
            ],
            [
                'required' => trans('trans.required'),
            ]
        );
        if ($validator->fails()) {
            return response()->json(['code' => 0,'msg' => $validator->errors()->first(),'data' => []]);
        }
        $notice_id = $request->input('notice_id');
        $notice = $this->user->unreadNotifications()->where("id", $notice_id)->first();
        if (empty($notice)) {
            return response()->json(['code' => 0,'msg' => '消息不存在','data' => []]);
        }
        if($notice->type == 'App\Notifications\ProfitApply') {
            $profit_table = (new Profit)->getTable();
            $profit = Profit::leftJoin('users as u', 'u.id', '=', $profit_table.'.user_id')
                ->leftJoin('transfer_record as tr', 'tr.id', '=', $profit_table.'.record_id')
                ->where($profit_table.'.id',$notice->data['param'])
                ->select($profit_table.'.*','u.mobile as mobile','u.avatar as avatar','tr.transfer_id as transfer_id')->first();
            if (empty($profit)) {
                return response()->json(['code' => 0,'msg' => '分润不存在','data' => []]);
            }
            $data = [
                'amount' => $profit->proxy_amount,
                'type' => '分润',
                'time' => (string)$notice->created_at,
                'transfer_id' => $profit->transfer_id,
                'mobile' => $profit->mobile,
                'thumb' => $profit->avatar??'',
            ];
        } else {
            $data = [
                'time' => (string)$notice->created_at,
                'content'=> $notice->data['content'],
                'title' => $notice->data['title']
            ];
        }

        return response()->json(['code' => 1,'msg' => '','data' => $data]);
    }

    /**
     * @SWG\Post(
     *   path="/notice/delete",
     *   summary="清空消息",
     *   tags={"消息"},
     *   @SWG\Parameter(
     *     name="type",
     *     in="formData",
     *     description="消息类型",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request){
        $this->user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(),
            [
                'type' => 'bail|required|numeric',
            ],
            [
                'required' => trans('trans.required'),
                'numeric' => trans('trans.numeric'),
            ]
        );
        if ($validator->fails()) {
            return response()->json(['code' => 0,'msg' => $validator->errors()->first(),'data' => []]);
        }

        $notice_type = Notice::typeConfig();
        if(!isset($notice_type[$request->type])) {
            return response()->json(['code' => 0,'msg' => '消息类型不存在','data' => []]);
        }

        try{
            $this->user->unreadNotifications->where('type',$notice_type[$request->type])->markAsRead();
            return response()->json(['code' => 1,'msg' => '','data' => []]);
        } catch (\Exception $e) {
            return response()->json(['code' => 0,'msg' => '删除失败','data' => []]);
        }

    }





}
