<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuth;
use Validator;

class TransferController extends Controller
{
    public function __construct()
    {
        $this->middleware("jwt.auth");
    }

    public function create(Request $request)
    {
        $this->user = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(),
            [
                'shop_id' => 'bail|required',
                'price' => 'bail|required|numeric|between:0.1,99999',
            ],
            [
                'required' => trans('trans.required'),
                'numeric' => trans('trans.numeric'),
                'between' => trans('trans.between'),
            ]
        );

        if ($validator->fails()) {
            return response()->json(['code' => 0,'msg' => $validator->errors()->first(),'data' => []]);
        }
    }

}
