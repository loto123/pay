<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profit extends Model
{
    protected $table = 'profit_record';

    //分润来源用户
    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    //分润所属代理
//    public function proxys() {
//        return $this->belongsTo('App\User', 'proxy', 'id');
//    }
}
