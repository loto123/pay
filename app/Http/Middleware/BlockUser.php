<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Auth;

class BlockUser
{
    use Helpers;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( $this->auth->user()->status == User::STATUS_BLOCK )
        {
            return $this->response()->array([
                'code' => 2,
                'msg' => "user block",
                'data' => new \stdClass()
            ]);
        }
        return $next($request);
    }
}
