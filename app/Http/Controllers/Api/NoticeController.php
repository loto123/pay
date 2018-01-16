<?php

namespace App\Http\Controllers\Api;

use App\Notice;
use App\Notifications\ConfirmExecuteResult;
use App\Notifications\UserConfirmCallback;
use App\Profit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
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
     *                      @SWG\Property(property="created_at", type="string", example="2018-01-01 12:00:00",description="发布时间")
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
     *                      @SWG\Property(property="created_at", type="string", example="2018-01-01 12:00:00",description="发布时间")
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
        $notice_query = $this->user->unreadNotifications()->where('type', $notice_type);
        $count = $notice_query->count();
        if ($request->offset) {
            $last_notification = Notice::where("id", $request->offset)->first();
            if ($last_notification) {
                $notice_query->where('uid', "<", $last_notification->uid);
            }
        }
        $notice = $notice_query->orderBy("uid", "DESC")->limit($request->input('limit', 20))->get();
        $list = [];
        if (!empty($notice) && count($notice)> 0) {
            switch ($type){
                case '1'://分润
                foreach ($notice as $item) {
                    $operator_state = 0;
                    $operator_options = [];
                    $profit_table = (new Profit)->getTable();
                    $profit = Profit::leftJoin('users as u', 'u.id', '=', $profit_table . '.user_id')
                        ->where($profit_table . '.id', $item->data['param'])->select('proxy_amount', 'u.mobile as mobile', 'u.avatar as avatar')->first();
                    if (empty($profit)) {
                        continue;
                    }
                    //是否需要操作
                    if(!empty($item->data['operators'])) {
                        $operators = unserialize($item->data['operators']);
                        //判断操作是否过期
                        if(!empty($operators['expire_time'])
                            && strtotime((string)$item->created_at)+ $operators['expire_time'] < time()) {
                            continue;
                        }
                        if(!empty($operators['options']) && is_array($operators['options'])) {
                            $operator_options = $operators['options'];
                        }
                        $operator_state = 1;
                    }
                    $list[] = [
                        'type' => $type,
                        'notice_id' => $item->id,
                        'mobile' => $profit->mobile,
                        'thumb' => $profit->avatar??'',
                        'amount' => $profit->proxy_amount,
                        'created_at' => (string)$item->created_at,
                        'operator_state' => $operator_state,
                        'operator_options' => $operator_options
                    ];
                }
                break;
                case '2'://用户注册
                case '3'://系统消息
                    foreach ($notice as $item) {
                        $operator_state = 0;
                        $operator_options = [];
                        //是否需要操作
                        if(!empty($item->data['operators'])) {
                            $operators = $item->data['operators'];
                            //判断操作是否过期
                            if(!empty($operators['expire_time'])
                                && strtotime((string)$item->created_at)+ $operators['expire_time'] < time()) {
                                continue;
                            }
                            if(!empty($operators['options']) && is_array($operators['options'])) {
                                $operator_options = $operators['options'];
                            }
                            $operator_state = 1;
                        }
                        $list[] = [
                            'type' => $type,
                            'notice_id' => $item->id,
                            'title' => $item->data['title'],
                            'content' => $item->data['content'],
                            'created_at' => (string)$item->created_at,
                            'operator_state' => $operator_state,
                            'operator_options' => $operator_options
                        ];
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
        $notice = $this->user->unreadNotifications()->where("id", $notice_id)->first();
        if(!empty($notice) && isset($notice['operators'])) {
            $operators = $notice['operators'];
            try{
                $res = call_user_func($operators['callback_method'],serialize(compact($value,$operators['callback_params'])));
            } catch (\Exception $e) {
                return $this->json([], '无法响应', 0);
            }
            if($res == ConfirmExecuteResult::EXECUTE_SUCCESS) {
                return $this->json();
            } else {
                return $this->json([],'请求失败',0);
            }

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
        $operators = [
            'callback_method' => $request->callback_method,
            'callback_params' => $request->callback_params??[],
            'expire_time' => $request->expire_time,
        ];
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
     *                  @SWG\Property(property="time", type="string", example="2018-01-01 12:00:00",description="时间")
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
        $notice = $this->user->unreadNotifications()->where("id", $notice_id)->first();
        if (empty($notice)) {
            return $this->json([],'消息不存在',0);
        }
        if($notice->type == 'App\Notifications\ProfitApply') {
            $profit_table = (new Profit)->getTable();
            $profit = Profit::leftJoin('users as u', 'u.id', '=', $profit_table.'.user_id')
                ->leftJoin('transfer_record as tr', 'tr.id', '=', $profit_table.'.record_id')
                ->where($profit_table.'.id',$notice->data['param'])
                ->select($profit_table.'.*','u.mobile as mobile','u.avatar as avatar','tr.transfer_id as transfer_id')->first();
            if (empty($profit)) {
                return $this->json([],'分润不存在',0);
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
            $this->user->unreadNotifications->where('type',$notice_type[$request->type])->markAsRead();
            return $this->json();
        } catch (\Exception $e) {
            return $this->json([],'操作失败',0);
        }

    }





}
