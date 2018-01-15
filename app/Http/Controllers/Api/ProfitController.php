<?php
/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2018/1/11
 * Time: 14:13
 */

namespace App\Http\Controllers\Api;


use App\ProxyWithdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProfitController extends BaseController
{
    /**
     * @SWG\GET(
     *   path="/profit/index",
     *   summary="我的分润首页",
     *   tags={"我的分润"},
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
     *                  @SWG\Property(property="yesterday", type="double", example=9.9,description="昨日收益"),
     *                  @SWG\Property(property="today", type="double", example=9.9, description="今日收益"),
     *                  @SWG\Property(property="profit", type="double", example=9.9, description="可提现收益"),
     *                  @SWG\Property(property="total", type="double", example=9.9, description="累计收益"),
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
        $user = JWTAuth::parseToken()->authenticate();
        $yesterday = $user->proxy_profit()->where('created_at', '>=', date('Y-m-d', strtotime('-1 day')))->where('created_at', '<', date('Y-m-d'))->sum('proxy_amount');
        $today = $user->proxy_profit()->where('created_at', '>=', date('Y-m-d'))->where('created_at', '<', date('Y-m-d', strtotime('+1 day')))->sum('proxy_amount');
        $profit = $user->profit;
        $total = $user->proxy_profit()->sum('proxy_amount');
        return $this->json(['yesterday' => $yesterday, 'today' => $today, 'profit' => $profit, 'total' => $total], 'ok', 1);
    }

    /**
     * @SWG\GET(
     *   path="/profit/balance",
     *   summary="个人可提现余额",
     *   tags={"我的分润"},
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
     *                  @SWG\Property(property="profit", type="double", example=9.9, description="可提现收益"),
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
    public function balance()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $profit = $user->profit;
        return $this->json(['profit' => $profit], 'ok', 1);
    }

    /**
     * @SWG\POST(
     *   path="/profit/count",
     *   summary="月收益总额",
     *   tags={"我的分润"},
     *  @SWG\Parameter(
     *     name="date",
     *     in="formData",
     *     description="月(2017-12形式) 不传默认查询当前月",
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
     *                  type="object",
     *                  @SWG\Property(property="total", type="double", example=9.9, description="月收益总额"),
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
    public function count(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(),
            [
                'date' => 'bail|date_format:Y-m',
            ],
            [
                'required' => trans('trans.required'),
                'date_format' => trans('trans.date_format'),
            ]
        );
        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $date = $request->input('date', date('Y-m'));
        $begin = $date . '-1';
        $end = date("Y-m-d", strtotime("$begin +1 month"));
        $total = $user->proxy_profit()->where('created_at', '>=', $begin)->where('created_at', '<', $end)->sum('proxy_amount');
        return $this->json(['total' => $total], 'ok', 1);
    }

    /**
     * @SWG\POST(
     *   path="/profit/data",
     *   summary="收益明细",
     *   tags={"我的分润"},
     *  @SWG\Parameter(
     *     name="limit",
     *     in="formData",
     *     description="每页条数",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="offset",
     *     in="formData",
     *     description="起始位置(初始默认0或者不传该参数 后续传最后一条数据的id)",
     *     required=false,
     *     type="integer"
     *   ),
     *  @SWG\Parameter(
     *     name="date",
     *     in="formData",
     *     description="月(2017-12形式)",
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
     *                  type="array",
     *                  description="收益明细",
     *                  @SWG\Items(
     *                          @SWG\Property(property="id", type="string", example="1234567",description="收益记录ID"),
     *                          @SWG\Property(property="proxy_percent", type="integer", example=9, description="分成比例"),
     *                          @SWG\Property(property="proxy_amount", type="double", example=9.9, description="收益"),
     *                          @SWG\Property(property="created_at", type="string", example="2017-12-22 10:19:23",description="创建时间"),
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
    public function data(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(),
            [
                'limit' => 'bail|integer',
                'date' => 'bail|date_format:Y-m',
            ]
        );
        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $query = $user->proxy_profit();
        if ($request->date) {
            $date = $request->input('date', date('Y-m'));
            $begin = $date . '-1';
            $end = date("Y-m-d", strtotime("$begin +1 month"));
            $query->where('created_at', '>=', $begin)->where('created_at', '<', $end);
        }
        if ($request->limit) {
            $query->limit($request->limit);
        }
        if ($request->offset) {
            $query->where('id', '<', $request->offset);
        }
        $list = $query->select('id', 'proxy_percent', 'proxy_amount', 'created_at')->orderBy('created_at', 'DESC')->get();
        return $this->json($list, 'ok', 1);
    }

    /**
     * @SWG\GET(
     *   path="/profit/show/{id}",
     *   summary="收益明细详情",
     *   tags={"我的分润"},
     *  @SWG\Parameter(
     *     name="id",
     *     in="formData",
     *     description="收益记录ID",
     *     required=false,
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
     *                  type="object",
     *                  @SWG\Property(property="proxy_amount", type="double", example=9.9, description="分润收益"),
     *                  @SWG\Property(property="type", type="string", example="分润收益", description="类型"),
     *                  @SWG\Property(property="created_at", type="string", example="2018-01-12 14:35:46", description="创建时间"),
     *                  @SWG\Property(property="user_nick", type="string", example="傻逼", description="来源人昵称"),
     *                  @SWG\Property(property="user_mobile", type="string", example="13873152488", description="来源人账号"),
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
    public function show($id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $item = $user->proxy_profit()->where('id', $id)->first();
        //组装响应数据
        $data['proxy_amount'] = $item->proxy_amount;
        $data['type'] = '分润收益';
        $data['created_at'] = (string)$item->created_at;
        $data['user_nick'] = $item->user->name;
        $data['user_mobile'] = $item->user->mobile;
        return $this->json($data, 'ok', 1);
    }

    /**
     * @SWG\POST(
     *   path="/profit/withdraw",
     *   summary="提现",
     *   tags={"我的分润"},
     *  @SWG\Parameter(
     *     name="amount",
     *     in="formData",
     *     description="提现金额",
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
     *                  type="object",
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
    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|min:0',
        ]);
        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->profit < $request->amount) {
            return $this->json([], trans("profit_not_enough_money"), 0);
        }
        try {
            if (!$user->check_pay_password($request->password)) {
                return $this->json([], trans("api.error_pay_password"), 0);
            }
        } catch (\Exception $e) {
            return $this->json([], $e->getMessage(), 0);
        }
        DB::beginTransaction();
        try {
            //容器
            $user->proxy_container->transfer($user->container, $request->amount, 0, false, false);
            //提现记录
            $recorder = new ProxyWithdraw();
            $recorder->amount = $request->amount;
            $recorder->user_id = $user->id;
            $recorder->save();
            DB::commit();
            return $this->json([], trans('proxy_withdraw_success'), 1);
        } catch (\Exception $e) {
            DB::rollback();
        }
        return $this->json([], trans('proxy_withdraw_failed'), 0);
    }

    /**
     * @SWG\POST(
     *   path="/profit/withdraw/count",
     *   summary="月提现总额",
     *   tags={"我的分润"},
     *  @SWG\Parameter(
     *     name="date",
     *     in="formData",
     *     description="月(2017-12形式) 不传默认查询当前月",
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
     *                  type="object",
     *                  @SWG\Property(property="total", type="double", example=9.9, description="月提现总额"),
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
    public function withdrawCount(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(),
            [
                'date' => 'bail|date_format:Y-m',
            ],
            [
                'required' => trans('trans.required'),
                'date_format' => trans('trans.date_format'),
            ]
        );
        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $date = $request->input('date', date('Y-m'));
        $begin = $date . '-1';
        $end = date("Y-m-d", strtotime("$begin +1 month"));
        $total = $user->proxy_withdraw()->where('created_at', '>=', $begin)->where('created_at', '<', $end)->sum('amount');
        return $this->json(['total' => $total], 'ok', 1);
    }

    /**
     * @SWG\POST(
     *   path="/profit/withdraw/data",
     *   summary="提现记录",
     *   tags={"我的分润"},
     *  @SWG\Parameter(
     *     name="limit",
     *     in="formData",
     *     description="每页条数",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="offset",
     *     in="formData",
     *     description="起始位置(初始默认0或者不传该参数 后续传最后一条数据的id)",
     *     required=false,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="date",
     *     in="formData",
     *     description="月(2017-12形式)",
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
     *                  type="array",
     *                  description="收益明细",
     *                  @SWG\Items(
     *                          @SWG\Property(property="id", type="string", example="1234567",description="提现记录ID"),
     *                          @SWG\Property(property="amount", type="double", example=9.9, description="提现金额"),
     *                          @SWG\Property(property="created_at", type="string", example="2017-12-22 10:19:23",description="创建时间"),
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
    public function withdrawData(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(),
            [
                'limit' => 'bail|integer',
                'date' => 'bail|date_format:Y-m',
            ]
        );
        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $query = $user->proxy_withdraw();
        if ($request->date) {
            $date = $request->input('date', date('Y-m'));
            $begin = $date . '-1';
            $end = date("Y-m-d", strtotime("$begin +1 month"));
            $query->where('created_at', '>=', $begin)->where('created_at', '<', $end);
        }
        if ($request->limit) {
            $query->limit($request->limit);
        }
        if ($request->offset) {
            $query->where('id', '<', ProxyWithdraw::decrypt($request->offset));
        }
        $list = $query->select('id', 'amount', 'created_at')->orderBy('created_at', 'DESC')->get();
        foreach ($list as $key => $value) {
            $list[$key]['id'] = $value->en_id();
        }
        return $this->json($list, 'ok', 1);
    }

    /**
     * @SWG\GET(
     *   path="/profit/withdraw/show/{id}",
     *   summary="提现详情",
     *   tags={"我的分润"},
     *  @SWG\Parameter(
     *     name="id",
     *     in="formData",
     *     description="收益记录ID",
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
     *                  type="object",
     *                  @SWG\Property(property="id", type="string", example="12345689", description="提现单号"),
     *                  @SWG\Property(property="amount", type="double", example=9.9, description="提现金额"),
     *                  @SWG\Property(property="type", type="string", example="提现", description="类型"),
     *                  @SWG\Property(property="created_at", type="string", example="2018-01-12 14:35:46", description="创建时间"),
     *                  @SWG\Property(property="user_mobile", type="string", example="13873152488", description="提现账号"),
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
    public function withdrawShow($id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $item = $user->proxy_withdraw()->where('id', ProxyWithdraw::decrypt($id))->first();
        //组装响应数据
        $data['id'] = $item->en_id();
        $data['amount'] = $item->amount;
        $data['type'] = '提现';
        $data['created_at'] = (string)$item->created_at;
        $data['user_mobile'] = $item->user->mobile;
        return $this->json($data, 'ok', 1);
    }
}