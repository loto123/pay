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
use Illuminate\Support\Facades\Log;

class PromoterGrant extends Model implements UserConfirmCallback
{
    const PROMOTER_ROLE_NAME = 'promoter';
    const UPDATED_AT = null;//确认时间

    const CONFIRM_PENDING = 0; //等待确认
    const CONFIRM_ACCEPT = 1; //同意授权
    const CONFIRM_DENY = 2; //拒绝授权

    const CONFIRM_TIMEOUT = 60 * 60 * 24 * 3; //确认过期时间

    protected $table = 'agent_promoter_grant';
    protected $guarded = ['id'];

    public static function boot()
    {
        parent::boot();

        //添加授权
        static::created(function ($model) {
            if (!Admin\Controllers\NoticeController::send(
                [$model->grant_to],
                3,
                "用户 {$model->grantBy->name} 授权你为推广员",
                '授权消息',
                '',
                [
                    //回调函数
                    'callback_method' => [$model, 'confirm'],
                    'callback_params' => [],
                    //3天内确认
                    'expire_time' => self::CONFIRM_TIMEOUT,
                    //确认选项
                    'options' => [
                        self::CONFIRM_DENY => ['text' => '忽略', 'color' => 'rgba(188, 188, 188, 1)'],
                        self::CONFIRM_ACCEPT => ['text' => '接受', 'color' => 'rgba(0, 204, 0, 1)'],
                    ]
                ]
            )
            ) {
                Log::error('授权发送确认消息失败');

            }
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
        //已经是推广员直接忽略
        if ($this->grantTo->isPromoter()) {
            $this->grant_result = self::CONFIRM_DENY;
            $this->confirmed_at = date('Y-m-d H:i:s');
            $this->save();
            $result = ConfirmExecuteResult::success('已忽略');
            $result->prompt = '你已接受过邀请,已自动忽略';
            return $result;
        }

        DB::beginTransaction();
        try {
            if ($selected_value == self::CONFIRM_ACCEPT) {
                $this->grantTo->attachRole(Role::where('name', self::PROMOTER_ROLE_NAME)->first());
                $this->grant_result = self::CONFIRM_ACCEPT;
                $result = ConfirmExecuteResult::success('已接受');
            } else {
                $this->grant_result = self::CONFIRM_DENY;
                $result = ConfirmExecuteResult::success('已忽略');
            }
            $this->confirmed_at = date('Y-m-d H:i:s');
            $this->save();
        } catch (\Exception$e) {
            DB::rollback();
            return ConfirmExecuteResult::fail('系统异常', $e);
        }
        DB::commit();
        return $result;
    }
}
