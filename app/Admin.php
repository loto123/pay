<?php

namespace App;

use Encore\Admin\Auth\Database\Administrator;

class Admin extends Administrator
{
    protected $table = 'admin_users';
    //业绩
    public function achievement()
    {
        return $this->hasMany('App\Profit', 'operator', 'id');
    }

    //子代理
    public function child_proxy()
    {
        return $this->hasMany('App\User', 'operator_id', 'id')->whereHas('roles', function ($query) {
            $query->where('name','like', 'agent%');
        });
    }

    //子用户
    public function child_user()
    {
        return $this->hasMany('App\User', 'operator_id', 'id')->whereHas('roles', function ($query) {
            $query->where('name', 'user');
        });
    }

    //推广员
    public function promoter()
    {
        return $this->hasMany('App\User', 'operator_id', 'id')->whereHas('roles', function ($query) {
            $query->where('name', 'promoter');
        });
    }

    //拥有的VIP卡
    public function card_stock()
    {
        return $this->hasMany('App\Agent\CardStock','operator','id');
    }
}
