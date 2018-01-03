<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
        '/withdraw-notify/*',//支付通知不验证
        '/pay-notify/*',//支付通知不验证
        '/pay-result/*',//支付返回不验证
        'api/*'
    ];
}
