<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgentGrant extends Model
{
    //
    protected $table = 'agent_grant';

    const AGENT_ROLE_NAME = 'agent';

    //普通代理
    public static function getAgentRoleId()
    {
        return Role::where('name',self::AGENT_ROLE_NAME)->first()->id;
    }

    //可授权的代理的角色
    public static function getUserAllowGrantRoles($user)
    {
        //遍历可授权角色，从高至低
        //判断用户是否拥有该角色，如果没有则将该角色记录到用户可授权角色中，若有则中断循环
        try{
            $allow_roles_string = rtrim(config('admin.allow_grant_roles','big-agent>agent'),'>');
            $allow_roles_name = explode('>',$allow_roles_string);
        }
        catch (\Exception $e) {
            Log::info(['解析可授权代理角色出错'=>$e->getMessage()]);
            return ['data'=>[],'error'=>'配置项有误！请联系管理员'];
        }
        if (!$allow_roles_name) {
            return ['data'=>[],'error'=>'配置项有误！请联系管理员'];
        }

        $user_allow_roles_name = []; //被授权人可授权角色
        foreach ($allow_roles_name as $value) {
            if($user->hasRole($value)) {
                break;
            } else{
                $user_allow_roles_name[] = $value;
            }
        }
        if (!$user_allow_roles_name) {
            return ['data'=>[],'error'=>'暂无更高级代理'];
        }

        $allow_roles = Role::whereIn('name',$user_allow_roles_name)->select('id','display_name')->get();
        $user_allow_roles = [];
        if($allow_roles) {
            foreach ($allow_roles as $_role) {
                $user_allow_roles[] = [
                    'id' => $_role->id,
                    'display_name' => $_role->display_name
                ];
            }
            return ['data'=>$user_allow_roles,'error'=>''];
        } else {
            return ['data'=>[],'error'=>'配置有误'];
        }
    }

    //授权
    public static function grant($grant_by,$grant_to,$by_admin,$grant_roles)
    {
        $old_roles = '';
        if($grant_to->roles()->pluck('id')) {
            foreach ($grant_to->roles()->pluck('id') as $_role) {
                $old_roles .= $_role . ',';
            }
            $old_roles = rtrim($old_roles,',');
        }

        DB::beginTransaction();
        try {
            $agent_grant = new  AgentGrant();
            $agent_grant->grant_by = $grant_by->id;
            $agent_grant->grant_to = $grant_to->id;
            $agent_grant->by_admin = $by_admin;
            $agent_grant->old_roles = $old_roles;
            $agent_grant->new_roles = $old_roles . ',' . $grant_roles;
            $agent_grant->save();
            $grant_to->roles()->attach($grant_roles);
        }catch (\Exception $e){
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    //被授权人
    public function agentGrantTo()
    {
        return $this->belongsTo('App\User','grant_to');
    }

    //授权人
    public function agentGrantBy()
    {
        return $this->by_admin ? $this->belongsTo('App\Admin','grant_by')
            : $this->belongsTo('App\User','grant_by');
    }

    public function get_roles($role_ids)
    {
        $role_id_arr = explode(',',$role_ids);
        $role = Role::whereIn('id',$role_id_arr)->pluck('display_name');
        $role_names = '';
        if($role) {
            foreach ($role as $_role) {
                $role_names .= $_role . ',';
            }
        }
        return rtrim($role_names,',');
    }
}
