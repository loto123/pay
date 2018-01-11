<?php

namespace App;

use App\Agent\Card;
use App\Agent\CardBinding;
use App\Agent\CardTransfer;
use App\Pay\Model\Channel;
use App\Pay\Model\MasterContainer;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Zizaco\Entrust\Traits\EntrustUserTrait;

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
    const STATUS_NORMAL = 0;
    const STATUS_BLOCK = 1;
    protected static $skip32_id = '0123456789abcdef0123';

    use Notifiable;
    use EntrustUserTrait;
    use Skip32Trait;
    protected $keyType = 'string';
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

    public function getAvatarAttribute($value)
    {
        return $value ? $value : asset("images/personal.jpg");
    }

    //交易记录

    public function transfer()
    {
        return $this->hasMany('App\Transfer', 'user_id', 'id');
    }

    public function transfer_record()
    {
        return $this->hasMany('App\TransferRecord', 'user_id', 'id');
    }

    //茶水费

    /*<!----------------代理VIP卡功能BEGIN-----------------*/

    /**
     * 我目前被绑定的vip卡
     * @return Card|null
     */
    public function myVipCard()
    {
        $binding = $this->hasMany(CardBinding::class, 'agent_id')->orderByDesc('id')->first();
        if ($binding) {
            return $binding->card;
        } else {
            return null;
        }
    }

    /**
     * 我的绑卡记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function myCardBindings()
    {
        return $this->hasMany(CardBinding::class, 'promoter_id');
    }

    /**
     * 我持有的卡
     * @return mixed
     */
    public function myCardsHold()
    {
        return Card::where('owner', $this->getKey())->whereNull('expired_at')->get();
    }

    /**
     * 我的转卡
     * @return mixed
     */
    public function myCardsTransfer()
    {
        return CardTransfer::where('from', $this->getKey())->get();
    }

    /*----------------代理VIP卡功能END----------------!>*/


    //产出利润

    public function tips()
    {
        return $this->hasMany('App\TipRecord', 'user_id', 'id');
    }

    //参与的交易

    public function output_profit()
    {
        return $this->hasMany('App\Profit', 'user_id', 'id');
    }

    //代理

    public function involved_transfer()
    {
        return $this->hasMany('App\TransferUserRelation', 'user_id', 'id');
    }

    //运营

    public function parent()
    {
        return $this->hasOne('App\User', 'id', 'parent_id');
    }

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

    //子代理

    public function paypwd_record()
    {
        return $this->hasMany('App\PaypwdValidateRecord', 'user_id');
    }

    //子用户

    public function child_proxy()
    {
        return $this->hasMany('App\User', 'parent_id', 'id');
//        ->whereHas('roles', function ($query) {
//            $query->where('roles.name', 'like', 'agent%');
//        });
    }

    public function child_user()
    {
        return $this->hasMany('App\User', 'parent_id', 'id');
//            ->whereHas('roles', function ($query) {
//            $query->where('roles.name', 'user');
//        });
    }

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
            throw new \Exception(trans("api.over_pay_password_max_times"));
        }
        if (!Hash::check($input, $this->pay_password)) {
            if(!$times) {
                Cache::put($key, 1, Carbon::now()->addDay(1));
            } else {
                Cache::increment($key);
            }
            throw new \Exception(trans("api.error_pay_password"));
//            return $this->json(['times' => config("pay_pwd_validate_times", 5) - $times], trans("api.error_pay_password"),0);
        } else {
            return true;
        }
    }
}
