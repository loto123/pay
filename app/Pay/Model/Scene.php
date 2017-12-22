<?php

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;


/**
 * 支付场景
 * Class Scene
 * @package App\Pay\Model
 */
class Scene extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $table = 'pay_scene';
}
