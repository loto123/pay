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
        if (preg_match("#^/".$request->server->get('API_PREFIX')."/#", $request->getPathInfo())) {
            if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['code'=>2, 'message' => 'token_expired', 'data'=> '']);
            } else if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['code'=>2, 'message' => 'token_invalid', 'data'=> '']);
            } else {
                return response()->json(['code'=>0, 'message' => config("app.debug") ? $exception->getMessage() : 'error', 'data'=> '']);
            }
        }
        return parent::render($request, $exception);
    }
}
