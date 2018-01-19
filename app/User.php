<?php

namespace App;

use App\Agent\Card;
use App\Agent\CardUse;
use App\Agent\PromoterGrant;
use App\Pay\Model\Channel;
use App\Pay\Model\MasterContainer;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
     * 我的卡使用记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function myCardUsing()
    {
        return $this->hasMany(CardUse::class, 'from')->with('card');
    }

    /**
     * 我持有的卡
     * @return mixed
     */
    public function myCardsHold()
    {
        return $this->hasMany(Card::class, 'promoter_id')->where('is_bound', 0)->with('type');
    }

    /**
     * 授权推广员
     * @param User $grantTo
     */
    public function grantPromoterTo(User $grantTo)
    {
        $grant = new PromoterGrant([
            'grant_by' => $this->getKey(),
            'by_admin' => false,
        ]);
        $grant->grantTo()->associate($grantTo);
        return $grant->save();
    }

    /**
     * 是否推广员
     * @return bool
     */
    public function isPromoter()
    {
        return $this->hasRole(PromoterGrant::PROMOTER_ROLE_NAME);
    }

    public function tips()
    {
        return $this->hasMany('App\TipRecord', 'user_id', 'id');
    }

    public function proxy_profit()
    {
        return $this->hasMany('App\Profit', 'proxy', 'id');
    }

    /*----------------代理VIP卡功能END----------------!>*/

    //缴纳的茶水费

    public function output_profit()
    {
        return $this->hasMany('App\Profit', 'user_id', 'id');
    }

    //获得利润

    public function involved_transfer()
    {
        return $this->hasMany('App\TransferUserRelation', 'user_id', 'id');
    }

    //产出利润

    public function parent()
    {
        return $this->hasOne('App\User', 'id', 'parent_id');
    }

    //参与的交易

    public function operator()
    {
        return $this->hasOne('App\Admin', 'id', 'operator_id');

    }

    //代理

    /**
     * 我管理的店铺
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shop()
    {
        return $this->hasMany('App\Shop', 'manager_id', 'id');
    }

    //运营

    public function shop_tips()
    {
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

    public function child_proxy()
    {
        return $this->hasMany('App\User', 'parent_id', 'id');
//        ->whereHas('roles', function ($query) {
//            $query->where('roles.name', 'like', 'agent%');
//        });
    }

    //子代理

    public function child_user()
    {
        return $this->hasMany('App\User', 'parent_id', 'id');
//            ->whereHas('roles', function ($query) {
//            $query->where('roles.name', 'user');
//        });
    }

    //子用户

    public function funds()
    {
        return $this->hasMany(UserFund::class, 'user_id');
    }

    public function container()
    {
        return $this->hasOne(MasterContainer::class, 'id', 'container_id');
    }

    public function proxy_container()
    {
        return $this->hasOne(MasterContainer::class, 'id', 'proxy_container_id')->withDefault([
            'balance' => 0,
        ]);
    }

    public function channel()
    {
        return $this->hasOne(Channel::class, 'id', 'channel_id');
    }

    public function getBalanceAttribute()
    {
        if($this->container) {
            return $this->container->balance;
        }
        return 0;
    }

    public function getProfitAttribute()
    {
        Log::info('User proxy_container:'.$this->proxy_container);
        if($this->proxy_container) {
            return $this->proxy_container->balance;
        }
        return 0;
    }

    public function pay_card()
    {
        return $this->hasOne(UserCard::class, 'id', 'pay_card_id');
    }

    public function check_pay_password($input)
    {
        $key = sprintf("PAY_PASSWORD_TIMES_%s_%d", date("Ymd"), $this->id);
        $times = Cache::get($key);
        if ($times && $times >= config("pay_pwd_validate_times", 5)) {
            throw new \Exception(trans("api.over_pay_password_max_times"));
        }
        if (!Hash::check($input, $this->pay_password)) {
            if (!$times) {
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

    public function proxy_withdraw()
    {
        return $this->hasMany(ProxyWithdraw::class, 'user_id');
    }

    public function getPercentAttribute($value)
    {
        return $value + $this->myVipProfitShareRate();
    }

    //分润提现记录

    /**
     * 我的vip分润加成比例(百分比)
     * @return float
     */
    public function myVipProfitShareRate()
    {
        $boundCard = $this->myVipCard();
        if (!$boundCard) {
            return 0;
        } else {
            if ($boundCard->is_frozen) {
                //冻结
                return 0;
            }

            if ($boundCard->expired_at !== null) {
                //过期判断
                return strtotime($boundCard->expired_at) > time() ? $boundCard->type->percent : 0;
            } else {
                return $boundCard->type->percent;
            }
        }
    }

    //代理分润百分比

    /**
     * 我目前被绑定的vip卡
     * @return Card|null
     */
    public function myVipCard()
    {
        $binding = $this->hasMany(CardUse::class, 'to')->where('type', CardUse::TYPE_BINDING)->orderByDesc('id')->with('card')->first();
        if ($binding) {
            return $binding->card;
        } else {
            return null;
        }
    }

    //vip卡的分销记录

    public function distributions()
    {
        return $this->hasMany('App\Agent\CardDistribution', 'to_promoter', 'id');
    }

    //持有的vip卡
    public function owner_cards()
    {
        return $this->hasMany('App\Agent\Card', 'owner', 'id');
    }

    //推广员的vip卡
    public function promoter_cards()
    {
        return $this->hasMany('App\Agent\Card', 'promoter_id', 'id');
    }



}
