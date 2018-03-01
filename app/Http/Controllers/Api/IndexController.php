<?php

namespace App\Http\Controllers\Api;

use App\Notice;
use App\Pay\Model\Channel;
use App\Pay\Model\DepositMethod;
use App\Pay\Model\Scene;
use App\Pay\Model\WithdrawMethod;
use App\User;
use App\UserFund;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;

/**
 *
 * @package App\Http\Controllers\Api
 */
class IndexController extends BaseController {

    /**
     * @SWG\Get(
     *   path="/index",
     *   summary="首页",
     *   tags={"首页"},
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
     *                  @SWG\Property(property="avatar", type="string", example="url",description="用户头像"),
     *                  @SWG\Property(property="balance", type="double", example=123.4,description="用户余额"),
     *                  @SWG\Property(property="new_message", type="boolean", example=0,description="是否有新消息"),
     *                  @SWG\Property(property="is_agent", type="boolean", example=0,description="是否为代理"),
     *                  @SWG\Property(property="is_promoter", type="boolean", example=0,description="是否为推广员"),
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
    public function index(){
        $user = $this->auth->user();
        /* @var $user User */
        return $this->json([
            'name' => $user->name,
            'avatar' => $user->avatar,
            'balance' => $user->container->balance,
            'new_message' => $user->unreadNotifications()->whereIn("type", Notice::typeConfig())->count() > 0 ? 1 : 0,
            'is_agent' => $user->hasRole("agent") ? 1 : 0,
            'is_promoter' => $user->hasRole("promoter") ? 1 : 0,
        ]);
    }

    /**
     * @SWG\Get(
     *   path="/index/safe",
     *   summary="安全保障",
     *   tags={"首页"},
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
     *                  @SWG\Property(property="users", type="integer", example=123,description="累计服务用户"),
     *                  @SWG\Property(property="days", type="integer", example=234,description="累计服务天数"),
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
    public function safe() {
        $start_date = config("safe_runtime", date("Ymd"));
        $diff = (new DateTime($start_date))->diff(new DateTime())->format('%a') + 1;

        return $this->json(['users' => User::count(), 'days' => $diff]);
    }
}