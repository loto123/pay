<?php

namespace App;

use App\Agent\Card;
use App\Agent\CardUse;
use App\Agent\PromoterGrant;
use App\Pay\Model\BillMatch;
use App\Pay\Model\Channel;
use App\Pay\Model\MasterContainer;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        return $this->hasOne(MasterContainer::class, 'id', 'proxy_container_id');
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

    /*
     * 检查某行为的剩余可执行次数
     * */
    public function check_action_times($action, $total_times)
    {
        $key = sprintf("ACTION_%s_%s_%d", strtoupper($action),date("Ymd"), $this->id);
        $times = Cache::get($key);
        if ($times && $times >= $total_times) {
            return false;
        } else if (!$times) {
            Cache::put($key, 1, Carbon::now()->addDay(1));
            return $total_times-$times-1;
        } else {
            Cache::increment($key);
            return $total_times-1;
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


    public function pets() {
        return $this->hasMany(Pet::class, "user_id", "id");
    }

    public function pet_records() {
        return $this->hasMany(PetRecord::class, "to_user_id", "id");
    }

    /**
     *  用户可售宠物
     */
    public function pets_for_sale() {
        return $this->hasMany(Pet::class, "user_id", "id")->whereIn("status", [Pet::STATUS_HATCHED, Pet::STATUS_UNHATCHED]);
    }

    /**
     * 批量生成宠物
     * @param integer $nums
     * @param integer $type 0=宠物蛋 1=宠物
     * @param integer $source 0=系统初始赠送 1=交易产生 2=订单取消补偿
     * @param string $order 订单号
     * @return array
     */
    public function batch_create_pet($num, $type = Pet::TYPE_PET, $source = PetRecord::TYPE_TRANSFER, $order = "", $transfer_id = 0) {
        $pets = [];
        #todo
        for ($k = 0 ; $k < $num; $k++){
            if ($type == Pet::TYPE_EGG && $source == PetRecord::TYPE_NEW) {
                if ($this->pet_left_times() == 0) {
                    return null;
                }
            }
            $pet = new Pet();
            $pet->user_id = $this->id;
            $pet->status = $type == Pet::TYPE_EGG ? Pet::STATUS_UNHATCHED : Pet::STATUS_HATCHING;
            if ($pet->status == Pet::STATUS_HATCHING) {
                \App\Jobs\Pet::dispatch($pet);
            }
            $record = new PetRecord();
            $record->to_user_id = $this->id;
            $record->type = $source;
            $record->order = $order;
            $record->transfer_id = $transfer_id;
            DB::beginTransaction();
            try {
                $pet->save();
                $record->pet_id = $pet->id;
                $record->save();
            } catch (\Exception $e){
                DB::rollBack();
                return null;
            }
            DB::commit();
            $pets[] = $pet;
        }
        return $pets;
    }

    /**
     * 生成宠物
     * @param integer $type 0=宠物蛋 1=宠物
     * @param integer $nums
     * @param integer $source 0=系统初始赠送 1=交易产生 2=订单取消补偿
     * @return Pet
     */
    public function create_pet($type = Pet::TYPE_PET, $source = PetRecord::TYPE_TRANSFER) {
        $key = sprintf("PET_FREE_TIMES_%s_%d", date("Ymd"), $this->id);
        if ($type == Pet::TYPE_EGG && $source == PetRecord::TYPE_NEW) {
            if ($this->pet_left_times() == 0) {
                return null;
            }
            Cache::store('redis')->increment($key);
        }
        $pet = new Pet();
        $pet->user_id = $this->id;
        $pet->status = $type == Pet::TYPE_EGG ? Pet::STATUS_UNHATCHED : Pet::STATUS_HATCHING;
        $record = new PetRecord();
        $record->to_user_id = $this->id;
        $record->type = $source;
//        $record->order = $order;
        DB::beginTransaction();
        try {
            $pet->save();
            $record->pet_id = $pet->id;
            $record->save();
        } catch (\Exception $e){
            DB::rollBack();
            Cache::store('redis')->forget($key);
            return null;
        }
        DB::commit();
        if ($pet->status == Pet::STATUS_HATCHING) {
            \App\Jobs\Pet::dispatch($pet);
        }
        return $pet;
    }

    /**
     * 可领取宠物蛋次数
     * @return int
     */
    public function pet_left_times() {
        $total = (int)config("pet_free_times", 3);
        $key = sprintf("PET_FREE_TIMES_%s_%d", date("Ymd"), $this->id);
        if (!Cache::store('redis')->has($key)) {
            $count = (int)$this->pet_records()->where("created_at", ">=", date("Y-m-d 00:00:00"))->where("type", PetRecord::TYPE_NEW)->count();
            Cache::store('redis')->put($key, $count, 60*24);
        } else {
            $count = Cache::store('redis')->get($key);
        }
        if ($count >= $total) {
            return 0;
        } else {
            return $total - $count;
        }
    }

    //用户的买单
    public function bill_match()
    {
        return $this->hasMany(BillMatch::class);
    }

    //对字符串做掩码处理
    public static function formatNum($num,$pre=0,$suf=4)
    {
        $prefix = '';
        $suffix = '';
        if($pre>0) {
            $prefix = substr($num, 0, $pre);
        }
        if ($suf>0){
            $suffix = substr($num, 0-$suf, $suf);
        }
        $maskBankCardNo = $prefix . str_repeat('*', strlen($num)-$pre-$suf) . $suffix;
        $maskBankCardNo = rtrim(chunk_split($maskBankCardNo, 4, ' '));
        return $maskBankCardNo;
    }

}
