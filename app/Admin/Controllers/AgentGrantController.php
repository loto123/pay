<?php

namespace App\Admin\Controllers;

use App\AgentGrant;
use App\Http\Controllers\Controller;
use App\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AgentGrantController extends Controller
{
    private $limit = 20;

    public function query(Request $request)
    {
        if(Admin::user()->can('grant_agent')) {
            $player = $request->input('player');
            if($player) {
                $user = User::where('mobile',$player)->with('roles')->first();
                if($user) {
                    $user_grant_data = AgentGrant::getUserAllowGrantRoles($user);
                    if($user_grant_data['error']) {
                        $_error = $user_grant_data['error'];
                    }
                    $user_grant_roles = $user_grant_data['data'];

                } else {
                    $_error = '用户不存在';
                }
            }
        } else {
            $_error = '您没有操作权限';
        }

        $params = compact('player','user','_error','user_grant_roles');
        return Admin::content(function (Content $content) use ($params) {
            $content->header("代理授权");
            $content->body(view('admin.agent_grant.query', $params));
        });
    }

    public function grant(Request $request)
    {
        if(!Admin::user()->can('grant_agent')) {
            return response()->json(['code'=>-1,'msg'=>'您没有操作权限','data'=>[]]);
        }
        $player = $request->input('player');
        $role_id = $request->input('role');
        $user = User::where('mobile',$player)->with('roles')->first();
        if(!$user) {
            return response()->json(['code'=>-1,'msg'=>'用户不存在','data'=>[]]);
        }

        if($user->roles()->where('id',$role_id)->count()>0) {
            return response()->json(['code'=>-1,'msg'=>'用户已成为所选代理','data'=>[]]);
        }

        $grant_result = AgentGrant::grant(Admin::user(),$user,1,$role_id);
        if (!$grant_result) {
            return response()->json(['code'=>-1,'msg'=>'授权失败','data'=>[]]);
        }
        return response()->json(['code'=>0,'msg'=>'授权成功','data'=>[]]);
    }

    public function records(Request $request)
    {
        $player = $request->player;
        if(Admin::user()->can('grant_agent')) {
            $query = AgentGrant::query();
            if(!Admin::user()->isAdministrator()) {
                $query = $query->where('grant_by',Admin::user()->id);
            }
            if($player) {
                $query = $query->whereHas('agentGrantTo',function ($query) use($player){
                    $query->where('mobile',$player);
                });
            }
            $query = $query->orderBy('id','DESC');
            $records = $query->paginate($this->limit);
        }

        $params = compact('player','records');
        return Admin::content(function (Content $content) use ($params) {
            $content->header("授权记录");
            $content->body(view('admin.agent_grant.records', $params));
        });
    }
}
