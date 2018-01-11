<?php

/**
 * 推广员授权
 */

namespace App\Agent;

use App\Role;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PromoterGrant extends Model
{
    const PROMOTER_ROLE_NAME = 'promoter';

    use SoftDeletes;
    const UPDATED_AT = null;
    protected $table = 'agent_promoter_grant';
    protected $guarded = ['id'];

    public static function boot()
    {
        parent::boot();

        //删除授权
        static::deleting(function ($model) {
            $model->grantTo->roles()->detach(Role::where('name', self::PROMOTER_ROLE_NAME)->value('id'));
        });

        //添加授权
        static::created(function ($model) {
            $model->grantTo->attachRole(Role::where('name', self::PROMOTER_ROLE_NAME)->first());
        });
    }

    /**
     * 授权人
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function grantBy()
    {
        return $this->by_admin ? DB::table('admin_users')->find($this->grant_by) : $this->belongsTo(User::class, 'grant_by');
    }

    /**
     * 被授权人
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function grantTo()
    {
        return $this->belongsTo(User::class, 'grant_to');
    }
}
