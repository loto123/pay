<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;
use JWTAuth;

/**
 *
 * @package App\Http\Controllers\Api
 */
class AccountController extends BaseController {

    /**
     * @SWG\Get(
     *   path="/account",
     *   summary="账号余额",
     *   tags={"店铺"},
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $user = $this->auth->user();
        /* @var $user User */
        return $this->json(['balance' => (float)$user->balance]);
    }
}