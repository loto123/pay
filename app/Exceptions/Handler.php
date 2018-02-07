<?php

namespace App\Exceptions;

use Dingo\Api\Http\Response;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if (preg_match("#^/".$request->server->get('API_PREFIX')."/#i", $request->getPathInfo())) {
            if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['code'=>2, 'msg' => '用户未登录', 'data'=> new \stdClass()]);
            } else if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['code' => 2, 'msg' => '用户未登录', 'data' => new \stdClass()]);
            } else if ($exception instanceof \Tymon\JWTAuth\Exceptions\JWTException) {
                return response()->json(['code' => 2, 'msg' => '用户未登录', 'data' => new \stdClass()]);
            } else if ($exception instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException) {
                return response()->json(['code' => 2, 'msg' => '用户未登录', 'data' => new \stdClass()]);

            } else if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return response()->json(['code' => 0, 'msg' => '404 Not Found', 'data' => new \stdClass()], 404);
            } else if ($exception instanceof \Dingo\Api\Exception\RateLimitExceededException) {
                return response()->json(['code' => 0, 'msg' => '操作频繁', 'data' => new \stdClass()], 404);
            } else {
                return response()->json(['code'=>0, 'msg' => config("app.debug") ? $exception->getMessage() : 'error', 'data'=> new \stdClass()]);
            }
        }
        return parent::render($request, $exception);
    }
}
