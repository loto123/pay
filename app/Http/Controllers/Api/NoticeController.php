<?php

namespace App\Http\Controllers\Api;

use App\Notice;
use App\Notifications\ConfirmExecuteResult;
use App\Profit;
use App\SystemMessage;
use App\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JWTAuth;
use Validator;

class NoticeController extends BaseController
{
    //消息列表
    /**
     * @SWG\Get(
     *   path="/notice/index",
     *   summary="消息列表",
     *   tags={"消息"},
     *   @SWG\Parameter(
     *     name="type",
     *     in="query",
     *     description="消息类型：1：分润，2：用户注册，3：系统",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="offset",
     *     in="query",
     *     description="最后记录id",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     description="数目",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *          response=200,
     *          description="成功返回(type=1 分润消息)",
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
     *                      @SWG\Property(property="type", type="integer", example="1",description="消息类型 1：分润，2：注册，3：系统"),
     *                      @SWG\Property(property="notice_id", type="string", example="1",description="消息id"),
     *                      @SWG\Property(property="mobile", type="string", example="17673161856",description="分润来源者的账号"),
     *                      @SWG\Property(property="thumb", type="string", example="url",description="分润来源者的头像"),
     *                      @SWG\Property(property="amount", type="string", example="10",description="分润金额"),
     *                      @SWG\Property(property="created_at", type="string", example="2018-01-01 12:00:00",description="发布时间"),
     *                      @SWG\Property(property="operator_state", type="integer", example="1",description="是否可操作：1：是,0:不是"),
     *                      @SWG\Property(property="operator_options", type="array",
     *                           @SWG\Items(
     *                                @SWG\Property(property="text", type="string", example="确认",description="文本"),
     *                                @SWG\Property(property="color", type="string", example="#bbb",description="颜色")
     *                           )
     *                      ),
     *                      @SWG\Property(property="operator_res", type="array",description="处理结果，[]表示未处理",
     *                           @SWG\Items(
     *                                @SWG\Property(property="code", type="string", example="1",description="处理结果,1：成功，0：失败"),
     *                                @SWG\Property(property="message", type="string", example="已确认",description="显示结果")
     *                           )
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *     @SWG\Response(
     *          response=302,
     *          description="成功返回(type:2,3 用户注册、系统消息)",
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
     *                  @SWG\Property(property="count", type="integer", example="10",description="消息总数"),
     *                  @SWG\Items(
     *                      @SWG\Property(property="type", type="integer", example="1",description="消息类型 1：分润，2：注册，3：系统"),
     *                      @SWG\Property(property="notice_id", type="string", example="1",description="消息id"),
     *                      @SWG\Property(property="title", type="string", example="系统消息",description="消息标题"),
     *                      @SWG\Property(property="content", type="string", example="这是一条系统消息...",description="消息内容"),
     *                      @SWG\Property(property="created_at", type="string", example="2018-01-01 12:00:00",description="发布时间"),
     *                      @SWG\Property(property="has_detail", type="integer", example="1",description="是否可以点击详情，1：是，0：否"),
     *                      @SWG\Property(property="read_state", type="integer", example="1",description="消息读取状态，1：已读，0：未读"),
     *                      @SWG\Property(property="operator_state", type="integer", example="1",description="是否可操作：1：是,0:不是"),
     *                      @SWG\Property(property="operator_options", type="array",
     *                           @SWG\Items(
     *                                @SWG\Property(property="text", type="string", example="确认",description="文本"),
     *                                @SWG\Property(property="color", type="string", example="#bbb",description="颜色")
     *                           )
     *                      ),
     *                     @SWG\Property(property="operator_res", type="array",description="处理结果，[]表示未处理",
     *                           @SWG\Items(
     *                                @SWG\Property(property="code", type="string", example="1",description="处理结果,1：成功，0：失败"),
     *                                @SWG\Property(property="message", type="string", example="已确认",description="显示结果")
     *                           )
     *                      )
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
    public function index(Request $request)
    {
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
            return $this->json([], $validator->errors()->first(), 0);
        }
        $type = $request->type;
        if (!isset(Notice::typeConfig()[$type])) {
            return $this->json([], '请求的消息类型不存在', 0);
        }
        $notice_type = Notice::typeConfig()[$type];
        $notice_query = $this->user->notifications()->where('type', $notice_type);
        $count = $notice_query->count();
        if ($request->offset) {
            $last_notification = Notice::where("id", $request->offset)->first();
            if ($last_notification) {
                $notice_query->where('uid', "<", $last_notification->uid);
            }
        }
        $notice = $notice_query->orderBy("uid", "DESC")->limit($request->input('limit', 20))->get();
        $list = [];
        $has_detail = 1;
        if (!empty($notice) && count($notice)> 0) {
            switch ($type){
                case '1'://分润
                    foreach ($notice as $item) {
                        $profit_table = (new Profit)->getTable();
                        $profit = Profit::has('user')
                            ->where($profit_table . '.id', $item->data['param'])->first();
                        if (empty($profit)) {
                            continue;
                        }
                        $list_data = [
                            'type' => $type,
                            'notice_id' => $item->id,
                            'mobile' => $profit->user->mobile,
                            'thumb' => $profit->user->avatar??'',
                            'amount' => $profit->proxy_amount,
                            'created_at' => (string)$item->created_at,
                            'operator_state' => 0,
                            'operator_options' => new \stdClass(),
                            'operators_res' => new \stdClass(),
                            'read_state' => $item->read_at ? 1 : 0,
                            'has_detail' => $has_detail,
                        ];
                        //是否需要操作
                        if(!empty($item->data['operators'])) {
                            $operators = $item->data['operators'];
                            //判断操作是否过期
                            if(!empty($operators['expire_time'])
                                && strtotime((string)$item->created_at)+ $operators['expire_time'] < time()) {
                                //标志为已读
                                $item->markAsRead();
                                continue;
                            }
                            //判断是否已经操作过了
                            if(isset($operators['result']) && isset($operators['result']['code'])
                                && isset($operators['result']['message'])) {
                                $list_data['operators_res'] = $operators['result'];
                            } else if( !empty($operators['options'])) {
                                $list_data['operator_options'] = $operators['options'];
                                $list_data['operator_state'] = 1;
                                array_unshift($list,$list_data);
                                continue;
                            }
                        } else {
                            //将非操作消息都置为已读
                            $item->markAsRead();
                        }
                        $list[] = $list_data;
                    }
                    break;
                case '2'://用户注册
                    //用户注册的消息标志为已读
                    foreach ($notice as $value) {
                        $value->markAsRead();
                    }
                    $has_detail = 0;
                case '3'://系统消息
                    foreach ($notice as $item) {
                        $list_data = [
                            'type' => $type,
                            'notice_id' => $item->id,
                            'title' => $item->data['title'],
                            'content' => $item->data['content'],
                            'created_at' => (string)$item->created_at,
                            'link' => isset($item->data['param']['link']) ? $item->data['param']['link'] : "",
                            'operator_state' => 0,
                            'operator_options' => new \stdClass(),
                            'operators_res' => new \stdClass(),
                            'read_state' => $item->read_at ? 1 : 0,
                            'has_detail' => $has_detail,
                        ];
                        //是否需要操作
                        if(!empty($item->data['operators'])) {
                            $operators = $item->data['operators'];
                            //判断操作是否过期
                            if(!empty($operators['expire_time'])
                                && strtotime((string)$item->created_at)+ $operators['expire_time'] < time()) {
                                //标志为已读
                                $item->markAsRead();
                                continue;
                            }
                            //判断是否已经操作过了
                            if(isset($operators['result']) && isset($operators['result']['code'])
                                && isset($operators['result']['message'])) {
                                $list_data['operators_res'] = $operators['result'];
                            } else if( !empty($operators['options'])) {
                                $list_data['operator_options'] = $operators['options'];
                                $list_data['operator_state'] = 1;
                                array_unshift($list,$list_data);
                                continue;
                            }
                        }
                        $list[] = $list_data;
                    }
                    break;
            }
        }
        return $this->json(compact('count','list'));
    }

    /**
     * @SWG\Post(
     *   path="/notice/operator",
     *   summary="消息操作",
     *   tags={"消息"},
     *   @SWG\Parameter(
     *     name="notice_id",
     *     in="formData",
     *     description="消息id",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="selected_value",
     *     in="formData",
     *     description="选中按钮的值",
     *     required=true,
     *     type="integer"
     *   ),
     *  @SWG\Response(
     *        response=200,
     *        description="成功返回",
     *        @SWG\Schema(
     *            @SWG\Property(
     *                property="code",
     *                type="integer",
     *                example=1
     *            ),
     *            @SWG\Property(
     *                property="msg",
     *                type="string"
     *            ),
     *            @SWG\Property(
     *                property="data",
     *                type="object"
     *            )
     *        )
     *    ),
     *   @SWG\Response(
     *       response="default",
     *       description="错误返回",
     *       @SWG\Schema(ref="#/definitions/ErrorModel")
     *    )
     * )
     * @return \Illuminate\Http\Response
     */
    //消息操作
    public function operator(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(),
            [
                'notice_id' => 'bail|required',
                'selected_value' =>  'bail|required'
            ],
            [
                'required' => trans('trans.required'),
            ]
        );

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        $notice_id = $request->notice_id;
        $value = $request->selected_value;
        $notice = $this->user->notifications()->where("id", $notice_id)->first();
        $notice->markAsRead();
        $flag = false;
        $res = '';
        $message = '失败';

        //已经操作过的消息不能再操作
        if(!empty($notice) && isset($notice['data']['operators']) && empty($notice['data']['operators']['result'])) {
            $operators = $notice['data']['operators'];
            try{
                $res = call_user_func(unserialize($operators['callback_method']),$value,$operators['callback_params']);
            }catch (\Exception $e) {
                $message = '无法响应';
            }
            if(is_object($res) && $res->result == ConfirmExecuteResult::EXECUTE_SUCCESS) {
                $flag = true;
            }else {
//                Log::info($res->exception);
                $message ='请求失败,'.$res->message;
            }
        }else {
            return  $this->json([],'该消息无法操作',0);
        }

        if($flag) {
            try{
                $data = $notice['data'];
                $data['operators']['result'] = ['code'=>$res->result,'message'=>$res->message];
                $notice->update(['data' => $data]);
                return $this->json([],$res->prompt);
            } catch (\Exception $e) {
                return $this->json([],'请求失败，请稍后重试',0);
            }
        }else {
            return $this->json([],$message,0);
        }
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
            return $this->json([],$validator->errors()->first(),0);
        }

        $user_id_arr = $request->input('user_id');
        $type = $request->input('type');
        $content = $request->input('content');
        $title = $request->input('title');
        $param = $request->input('param');
        $operators = [];
        if($request->callback_method && $request->callback_params && $request->expire_time){
            $operators = [
                'callback_method' => $request->callback_method,
                'callback_params' => $request->callback_params??[],
                'expire_time' => $request->expire_time,
            ];
        }
        if (\App\Admin\Controllers\NoticeController::send($user_id_arr,$type,$content,$title,$param,$operators)) {
            return $this->json();
        } else {
            return $this->json([],'操作失败',0);
        }

    }

    /**
     * @SWG\Get(
     *   path="/notice/detail",
     *   summary="详情",
     *   tags={"消息"},
     *   @SWG\Parameter(
     *     name="notice_id",
     *     in="query",
     *     description="消息ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *          response=200,
     *          description="成功返回(type为1,分润详情)",
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
     *                  @SWG\Property(property="amount", type="string", example="10",description="入账金额"),
     *                  @SWG\Property(property="type", type="string", example="分润",description="类型"),
     *                  @SWG\Property(property="time", type="string", example="2018-01-01 12:00:00",description="时间"),
     *                  @SWG\Property(property="transfer_id", type="string", example="111111",description="交易单号"),
     *                  @SWG\Property(property="mobile", type="string", example="13333333333",description="分润来源的账号"),
     *                  @SWG\Property(property="thumb", type="string", example="url",description="分润来源的头像"),
     *                  @SWG\Property(property="operator_state", type="integer", example="1",description="是否可操作：1：是,0:不是"),
 *                      @SWG\Property(property="operator_options", type="array",
 *                           @SWG\Items(
 *                                @SWG\Property(property="text", type="string", example="确认",description="文本"),
 *                                @SWG\Property(property="color", type="string", example="#bbb",description="颜色")
 *                           )
 *                      ),
 *                     @SWG\Property(property="operator_res", type="array",description="处理结果，[]表示未处理",
 *                           @SWG\Items(
 *                                @SWG\Property(property="code", type="string", example="1",description="处理结果,1：成功，0：失败"),
 *                                @SWG\Property(property="message", type="string", example="已确认",description="显示结果")
 *                           )
 *                      )
     *              )
     *          )
     *      ),
     *     @SWG\Response(
     *          response=202,
     *          description="成功返回(type为3,系统详情)",
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
     *                  @SWG\Property(property="content", type="string", example="这是一条系统消息",description="消息内容"),
     *                  @SWG\Property(property="title", type="string", example="系统消息",description="消息标题"),
     *                  @SWG\Property(property="time", type="string", example="2018-01-01 12:00:00",description="时间"),
     *                  @SWG\Property(property="operator_state", type="integer", example="1",description="是否可操作：1：是,0:不是"),
 *                      @SWG\Property(property="operator_options", type="array",
 *                           @SWG\Items(
 *                                @SWG\Property(property="text", type="string", example="确认",description="文本"),
 *                                @SWG\Property(property="color", type="string", example="#bbb",description="颜色")
 *                           )
 *                      ),
 *                     @SWG\Property(property="operator_res", type="array",description="处理结果，[]表示未处理",
 *                           @SWG\Items(
 *                                @SWG\Property(property="code", type="string", example="1",description="处理结果,1：成功，0：失败"),
 *                                @SWG\Property(property="message", type="string", example="已确认",description="显示结果")
 *                           )
 *                      )
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
            return $this->json([],$validator->errors()->first(),0);
        }

        $notice_id = $request->input('notice_id');
        $notice = $this->user->notifications()->where("id", $notice_id)->first();
        if (empty($notice)) {
            return $this->json([],'消息不存在',0);
        }

        //已读标志，默认设置为已读
        $read_flag = true;
        //操作消息
        $operator_options = new \stdClass();
        $operators_res = new \stdClass();
        $operator_state = 0;
        if(!empty($notice->data['operators'])) {
            $read_flag = false; //操作消息不置为已读
            $operators = $notice->data['operators'];
            //判断是否已经操作过了
            if(isset($operators['result']) && isset($operators['result']['code'])
                && isset($operators['result']['message'])) {
                $operators_res = $operators['result'];
            } else if( !empty($operators['options'])) {
                $operator_options = $operators['options'];
                $operator_state = 1;
            }
        }

        //分润
        if($notice->type == 'App\Notifications\ProfitApply') {
            $profit_table = (new Profit)->getTable();
            $profit = Profit::leftJoin('transfer_record as tr', 'tr.id', '=', $profit_table.'.record_id')
                ->where($profit_table.'.id',$notice->data['param'])
                ->has('user')
                ->select($profit_table.'.*','tr.transfer_id as transfer_id')->first();
            if (empty($profit)) {
                return $this->json([],'分润不存在',0);
            }
            $data = [
                'amount' => $profit->proxy_amount,
                'type' => '分润',
                'time' => (string)$notice->created_at,
                'transfer_id' => Transfer::encrypt($profit->transfer_id),
                'mobile' => $profit->user->mobile,
                'thumb' => $profit->user->avatar??'',
                'operators_res' => $operators_res,
                'operator_options' => $operator_options,
                'operator_state' => $operator_state,
            ];
        }else {//其他
            //后台消息
            $content = $notice->data['content'];
            $title = $notice->data['title'];
            if(isset($notice->data['param']) && isset($notice->data['param']['message_id'])) {
                $system = SystemMessage::find($notice->data['param']['message_id']);
                $content = $system['content'];
                $title = $system['title'];
            }
            $data = [
                'time' => (string)$notice->created_at,
                'content'=> $content,
                'title' => $title,
                'operators_res' => $operators_res,
                'operator_options' => $operator_options,
                'operator_state' => $operator_state,
            ];
        }

        if($read_flag) {
            $notice->markAsRead();
        }
        return $this->json($data);
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
            return $this->json([],$validator->errors()->first(),0);
        }

        $notice_type = Notice::typeConfig();
        if(!isset($notice_type[$request->type])) {
            return $this->json([],'消息类型不存在',0);
        }

        try{
            $this->user->notifications()->where('type',$notice_type[$request->type])->whereNotNull('read_at')->delete();
            return $this->json();
        } catch (\Exception $e) {
            return $this->json([],'操作失败',0);
        }

    }


    /**
     * @SWG\Get(
     *   path="/notice/info",
     *   summary="消息模块基本数据",
     *   tags={"消息"},
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
     *                  @SWG\Property(property="1", type="object",
     *                      @SWG\Property(property="type", type="string", example="1",description="消息类型"),
     *                      @SWG\Property(property="unreadCount", type="string", example="10",description="未读消息总数"),
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

    public function info()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        //用户的未读消息
        $type_list = Notice::where('notifiable_id',$this->user->id)->whereNull('read_at')
            ->select(DB::raw('count(*) as type_count, type'))->groupBy('type')->get();
        //未读消息类型，去掉斜杆
        $type_data = [];
        if(!empty($type_list)) {
            foreach ($type_list as $item) {
                $type_data[stripslashes($item->type)] = $item->type_count;
            }
        }

        //配置的type
        $notice_type_list = Notice::typeConfig();
        $data = [];
        foreach ($notice_type_list as $key => $value) {
            $value = stripslashes($value);
            $data[] = [
                'type' => $key,
                'unreadCount' => isset($type_data[$value])?$type_data[$value]:0,
            ];
        }
        return $this->json($data);
    }

}
