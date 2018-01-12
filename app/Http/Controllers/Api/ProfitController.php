<?php
/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2018/1/11
 * Time: 14:13
 */

namespace App\Http\Controllers\Api;


use App\Profit;
use Illuminate\Http\Request;

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
            ]
        );
        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $query = $user->proxy_profit();
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
     *   path="/profit/show",
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
     *                  @SWG\Property(property="type", type="string", example=分润收益, description="类型"),
     *                  @SWG\Property(property="created_at", type="string", example=2018-01-12 14:35:46, description="创建时间"),
     *                  @SWG\Property(property="user_nick", type="string", example=傻逼, description="来源人昵称"),
     *                  @SWG\Property(property="user_mobile", type="string", example=13873152488, description="来源人账号"),
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
        $item = $user->proxy_profit()->where('id',$id)->first();
        //组装响应数据
        $data['proxy_amount'] = $item->proxy_amount;
        $data['type'] = '分润收益';
        $data['created_at'] = $item->created_at;
        $data['user_nick'] = $item->user->name;
        $data['user_mobile'] = $item->user->mobile;
        return $this->json($data, 'ok', 1);
    }

    public function withdraw(Request $request)
    {

    }

    public function withdrawCount(Request $request)
    {

    }

    public function withdrawData(Request $request)
    {

    }
}