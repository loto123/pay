<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IndexModule extends Model
{
    //
    public static $types = [0 => "我的", 1 => '扩展'];

    public function roles()
    {
        return $this->belongsToMany(Role::class,"index_module_roles", "module_id");
    }
}
