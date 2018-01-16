<?php

/**
 * 推广员授权
 */

namespace App\Agent;

use App\Admin;
use App\Notifications\ConfirmExecuteResult;
use App\Notifications\UserConfirmCallback;
use App\Role;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PromoterGrant extends Model implements UserConfirmCallback
{
    const PROMOTER_ROLE_NAME = 'promoter';
    const UPDATED_AT = 'confirmed_at';//确认时间

    const CONFIRM_PENDING = 0; //等待确认
    const CONFIRM_ACCEPT = 1; //同意授权
    const CONFIRM_DENY = 2; //拒绝授权

    protected $table = 'agent_promoter_grant';
    protected $guarded = ['id'];

    public static function boot()
    {
        parent::boot();

        //添加授权
        static::created(function ($model) {
            //TODO 向用户发送一条确认消息
        });
    }

    /**
     * 授权人
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function grantBy()
    {
        return $this->by_admin ? $this->belongsTo(Admin::class, 'grant_by') : $this->belongsTo(User::class, 'grant_by');
    }

    /**
     * 被授权人
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function grantTo()
    {
        return $this->belongsTo(User::class, 'grant_to');
    }

    /**
     * 确认授权
     * @param string $selected_value
     * @param array $user_data
     */
    public function confirm($selected_value, $user_data = [])
    {
        try {
            DB::beginTransaction();
            if ($selected_value == self::CONFIRM_ACCEPT) {
                $this->grantTo->attachRole(Role::where('name', self::PROMOTER_ROLE_NAME)->first());
            }
            $this->grant_result = $selected_value;
            $this->save();
        } catch (\Exception $e) {
            DB::rollback();
            return ConfirmExecuteResult::fail('系统异常', $e);
        }
        DB::commit();
        return ConfirmExecuteResult::success($selected_value == self::CONFIRM_ACCEPT ? '已接受' : '已拒绝');

    }
}
