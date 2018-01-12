<?php

namespace App\Http\Middleware;

use Closure;
use Dingo\Api\Routing\Helpers;

class CheckProxy
{
    use Helpers;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$this->auth->user()->inRoles(['agent-vip', 'agent']) || $this->auth->user()->proxy_container) {
            return $this->response()->array([
                'code' => 3,
                'msg' => trans('trans.permission_denied'),
                'data' => new \stdClass()
            ]);
        }
        return $next($request);
    }
}
