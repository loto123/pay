<?php

namespace App;

use App\Pay\Model\Channel;
use App\Pay\Model\MasterContainer;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Skip32;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Class User
 * @package App
 * @property integer $id
 * @property string $name
 * @property string $mobile
 * @property string $password
 * @property float $balance
 * @property integer $container_id
 * @property MasterContainer $container
 * @property Channel $channel
 */
class User extends Authenticatable
{
    protected $keyType = 'string';

    use Notifiable;
    use EntrustUserTrait;
    use Skip32Trait;

    public function getAvatarAttribute($value) {
        return $value ? $value : asset("images/personal.jpg");
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'mobile', 'password', 'container_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    //发起的交易
    public function transfer()
    {
        return $this->hasMany('App\Transfer', 'user_id', 'id');
    }

    //交易记录
    public function transfer_record()
    {
        return $this->hasMany('App\TransferRecord', 'user_id', 'id');
    }

    //茶水费
    public function tips()
    {
        return $this->hasMany('App\TipRecord', 'user_id', 'id');
    }

    //产出利润
    public function output_profit()
    {
        return $this->hasMany('App\Profit', 'user_id', 'id');
    }

    //参与的交易
    public function involved_transfer()
    {
        return $this->hasMany('App\TransferUserRelation', 'user_id', 'id');
    }

    //代理
    public function parent()
    {
        return $this->hasOne('App\User', 'id', 'parent_id');
    }

    //运营
    public function operator()
    {
        return $this->hasOne('App\Admin', 'id', 'operator_id');

    }

    /**
     * 我管理的店铺
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shop()
    {
        return $this->hasMany('App\Shop', 'manager_id', 'id');
    }

    public function shop_tips() {
        return $this->hasManyThrough(TipRecord::class, Shop::class, 'manager_id', 'shop_id');
    }

    /**
     * 我参与的店铺
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function in_shops()
    {
        return $this->belongsToMany(Shop::class, (new ShopUser)->getTable(), 'user_id', 'shop_id');
    }

    public function wechat_user()
    {
        return $this->hasOne(OauthUser::class, 'user_id');
    }

    public function paypwd_record()
    {
        return $this->hasMany('App\PaypwdValidateRecord', 'user_id');
    }

    //子代理
    public function child_proxy()
    {
        return $this->hasMany('App\User', 'parent_id', 'id');
//        ->whereHas('roles', function ($query) {
//            $query->where('roles.name', 'like', 'agent%');
//        });
    }

    //子用户
    public function child_user()
    {
        return $this->hasMany('App\User', 'parent_id', 'id');
//            ->whereHas('roles', function ($query) {
//            $query->where('roles.name', 'user');
//        });
    }

    protected static $skip32_id = '0123456789abcdef0123';

    public function funds()
    {
        return $this->hasMany(UserFund::class, 'user_id');
    }

    public function container()
    {
        return $this->hasOne(MasterContainer::class, 'id', 'container_id');
    }

    public function channel()
    {
        return $this->hasOne(Channel::class, 'id', 'channel_id');
    }

    public function getBalanceAttribute()
    {
        return $this->container->balance;
    }

    public function pay_card() {
        return $this->hasOne(UserCard::class, 'id', 'pay_card_id');
    }

    public function check_pay_password($input) {
        $key = sprintf("PAY_PASSWORD_TIMES_%s_%d", date("Ymd"), $this->id);
        $times = Cache::get($key);
        if ($times && $times >= config("pay_pwd_validate_times", 5)) {
            throw new \Exception(trans("api.over_max_times"));
        }
        if (!Hash::check($input, $this->pay_password)) {
            if(!$times) {
                Cache::put($key, 1, Carbon::now()->addDay(1));
            }
            else {
                Cache::increment($key);
            }
            throw new \Exception(trans("api.error_pay_password"));
//            return $this->json(['times' => config("pay_pwd_validate_times", 5) - $times], trans("api.error_pay_password"),0);
        } else {
            return true;
        }
    }
}
