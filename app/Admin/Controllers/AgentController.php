<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function relation()
    {
        $params = [];
        return Admin::content(function (Content $content) use ($params) {
            $content->header("关系查询");
            $content->body(view('admin.agent.relation', $params));
        });
    }

    public function relation_update(Request $request)
    {
        $aid = $request->input("player_id");
        $user = User::where('mobile', $aid)->first();
        if (!$user) {
            return response()->json([
                'code' => -1,
                'msg' => '玩家ID不存在',
                'data' => []
            ]);
        }

        if (!Admin::user()->can("show_all_player") && $user->operator_id != Admin::user()->id) {
            return response()->json([
                'code' => -1,
                'msg' => '你只能查询自己的玩家和代理',
                'data' => []
            ]);
        }

        $view = 'admin.agent.relation_content';
        $data = compact("user", 'admin', 'agent');
        return response()->json([
            'code' => 0,
            'msg' => '',
            'data' => view($view, $data)->render()
        ]);
    }
}
