<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuth;
use Validator;

class TransferController extends Controller
{
    protected $user;

    public function __construct()
    {
//        $this->middleware("jwt.auth");
//        $this->user = JWTAuth::parseToken()->authenticate();
//        debug($this->user);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'shop_id' => 'bail|required',
                'price' => 'bail|required|numeric|min:0.1|max:99999',
            ],
            [
                'required' => trans('trans.required'),
                'numeric' => 'trans.numeric',
                'between' => 'trans.between',
//                'price.max' => trans('price.max'),
//                'price.required' => trans('price.required'),
//                'shop_id.required' => trans('shop_id.required'),
//                'shop_id.required' => trans('shop_id.required'),
            ]
        );
        if ($validator->fails()) {
            return response()->json(['code' => 0,'msg' => $validator->errors()->first(),'data' => []]);
        }
    }

}
