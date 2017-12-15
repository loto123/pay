<?php
/**
 * Created by IntelliJ IDEA.
 * User: noname
 */

namespace App\Http\Controllers\Api;


use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;

class BaseController extends Controller {
    use Helpers;

    protected function json($data=[], $message = '', $code = 1) {
        $result = [
            'code' => (int)$code,
            'message' => $message,
            'data' => $data
        ];
        return $this->response()->array($result);
    }
}