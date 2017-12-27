<?php

namespace App\Http\Controllers\Api;

use App\Notifications\ShopApply;
use App\Pay\Model\PayFactory;
use App\Shop;
use App\ShopUser;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 *
 * @package App\Http\Controllers\Api
 */
class ShopController extends BaseController {

    /**
     * @SWG\Post(
     *   path="/shop/create",
     *   summary="创建店铺",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     description="店铺名",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="rate",
     *     in="formData",
     *     description="倍率",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="percent",
     *     in="formData",
     *     description="抽水比例",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="active",
     *     in="formData",
     *     description="是否开启交易",
     *     required=true,
     *     type="boolean"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'rate' => 'required',
            'percent' => 'required',
            'active' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $user = $this->auth->user();
        $wallet = PayFactory::MasterContainer();
        $wallet->save();
        $shop  = new Shop();
        $shop->name = $request->name;
        $shop->manager_id = $user->id;
        $shop->price = $request->rate;
        $shop->fee = $request->percent;
        $shop->container_id = $wallet->id;
        $shop->save();
        return $this->json([$shop]);
    }

    /**
     * @SWG\Get(
     *   path="/shop/lists",
     *   summary="我参与的店铺",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     description="页码",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="size",
     *     in="query",
     *     description="数目",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function lists() {
        $user = $this->auth->user();
        $count = $user->in_shops()->where("status", Shop::STATUS_NORMAL)->count();
        $data = [];
        foreach ($user->in_shops()->where("status", Shop::STATUS_NORMAL)->get() as $_shop) {
            /* @var $_shop Shop */
            $data[] = [
                'id' => $_shop->en_id(),
                'name' => $_shop->name,
                'logo' => asset("images/personal.jpg")
            ];
        }
        return $this->json(['count' => $count, 'data' => $data]);
    }

    /**
     * @SWG\Get(
     *   path="/shop/lists/all",
     *   summary="我所有店铺（创建交易）",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     description="页码",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="size",
     *     in="query",
     *     description="数目",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request) {
        $limit = $request->input('size', 10);
        $user = $this->auth->user();
        $shops = [];
        $count = 0;
        foreach ($user->transfer as $_transfer) {
            $count += $_transfer->shop()->count();
            foreach ($_transfer->shop()->where("status", Shop::STATUS_NORMAL)->limit($limit)->get() as $_shop) {
                if (!isset($shops[$_shop->id])) {
                    $shops[$_shop->id] = $_shop;
                }
            }
        }
        $count += $user->shop()->where("status", Shop::STATUS_NORMAL)->count();
        if (count($shops) < $limit) {
            foreach ($user->shop()->where("status", Shop::STATUS_NORMAL)->limit($limit - count($shops))->get() as $_shop) {
                if (!isset($shops[$_shop->id])) {
                    $shops[$_shop->id] = $_shop;
                }
            }
        }
        $count += $user->in_shops()->where("status", Shop::STATUS_NORMAL)->count();
        if (count($shops) < $limit) {
            foreach ($user->in_shops()->where("status", Shop::STATUS_NORMAL)->limit($limit - count($shops))->get() as $_shop) {
                if (!isset($shops[$_shop->id])) {
                    $shops[$_shop->id] = $_shop;
                }
            }
        }

        $data = [];
        foreach ($shops as $_shop) {
            /* @var $_shop Shop */
            $data[] = [
                'id' => $_shop->en_id(),
                'name' => $_shop->name,
            ];
        }
        return $this->json(['count' => $count, 'data' => $data]);
    }

    /**
     * @SWG\Get(
     *   path="/shop/lists/mine",
     *   summary="我创建的店铺",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     description="页码",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="size",
     *     in="query",
     *     description="数目",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function my_lists() {
        $user = $this->auth->user();
        $count = $user->shop()->where("status", Shop::STATUS_NORMAL)->count();
        $data = [];

        foreach ($user->shop()->where("status", Shop::STATUS_NORMAL)->get() as $_shop) {
            /* @var $_shop Shop */
            $data[] = [
                'id' => $_shop->en_id(),
                'name' => $_shop->name,
                'logo' => asset("images/personal.jpg"),
                'today_profit' => 0,
                'total_profit' => 0
            ];
        }
        return $this->json(['count' => $count, 'data' => $data]);
    }

    /**
     * @SWG\Get(
     *   path="/shop/detail/{id}",
     *   summary="店铺详情",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="member_size",
     *     in="query",
     *     description="成员数目",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function detail($id, Request $request) {
        $user = $this->auth->user();

        $member_size = $request->input('member_size', 5);
        $shop = Shop::findByEnId($id);
        if (!$shop || $shop->status) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        if ($shop->user_id != $user->id && $shop->shop_user()->where("id", $user->id)->count() == 0) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        /* @var $shop Shop */
        $members = [];
        foreach ($shop->users()->limit($member_size)->get() as $_user) {
            /* @var $_user User */
            $members[] = [
                'id' => (int)$_user->id,
                'name' => $_user->name,
                'avatar' => $_user->avatar,

            ];
        }
        if ($shop->user_id == $user->id) {
            $data = [
                'id' => $shop->en_id(),
                'name' => $shop->name,
                'user_link' => $shop->use_link ? 1 : 0,
                'active' => $shop->active ? 1 : 0,
                'members' => $members,
                'members_count' => (int)$shop->users()->count(),
                'rate' => $shop->price,
                'percent' => $shop->fee,
                'created_at' => strtotime($shop->created_at),
                'logo' => asset("images/personal.jpg")
            ];
        } else {
            $data = [
                'id' => $shop->en_id(),
                'name' => $shop->name,
                'members' => $members,
                'members_count' => (int)$shop->users()->count(),
                'percent' => $shop->fee,
                'created_at' => strtotime($shop->created_at),
                'logo' => asset("images/personal.jpg")
            ];
        }
        return $this->json($data);
    }

    /**
     * @SWG\Get(
     *   path="/shop/members/{id}",
     *   summary="店铺成员详情",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="size",
     *     in="query",
     *     description="成员数目",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     description="页面",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function members($id, Request $request) {
        $size = $request->input('size', 20);
        $shop = Shop::findByEnId($id);
        if (!$shop || $shop->status) {
            return $this->json([], trans("api.error_shop_status"), 0);
        }
        $members = [];
        foreach ($shop->users()->paginate($size) as $_user) {
            /* @var $_user User */
            $members[] = [
                'id' => (string)$_user->en_id(),
                'name' => $_user->name,
                'avatar' => $_user->avatar,

            ];
        }
        return $this->json([
            'count' => (int)$shop->users()->count(),
            'members' => $members,
        ]);
    }

    /**
     * @SWG\Post(
     *   path="/shop/close/{id}",
     *   summary="解散店铺",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function close($id) {
        $shop = Shop::findByEnId($id);
        $shop->status = Shop::STATUS_CLOSED;
        $shop->save();
        return $this->json();
    }

    /**
     * @SWG\Post(
     *   path="/shop/quit/{id}",
     *   summary="退出店铺",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function quit($id) {
        $user = $this->auth->user();

        $shop = Shop::findByEnId($id);
        ShopUser::where('shop_id', $shop->id)->where("user_id", $user->id)->delete();
        return $this->json();
    }

    /**
     * @SWG\Post(
     *   path="/shop/update/{id}",
     *   summary="更新店铺",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="use_link",
     *     in="formData",
     *     description="是否开启邀请链接",
     *     required=false,
     *     type="boolean"
     *   ),
     *   @SWG\Parameter(
     *     name="active",
     *     in="formData",
     *     description="是否开启交易",
     *     required=false,
     *     type="boolean"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request) {
        $user = $this->auth->user();

        $shop = Shop::findByEnId($id);
        if ($request->name) {
            $shop->name = $request->name;
        }
        if ($request->use_link) {
            $shop->use_link = $request->use_link ? 1 : 0;
        }

        if ($request->active) {
            $shop->active = $request->active ? 1 : 0;
        }

        $shop->save();
        return $this->json();
    }

    /**
     * @SWG\Get(
     *   path="/shop/qrcode/{id}",
     *   summary="店铺二维码",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="size",
     *     in="query",
     *     description="二维码尺寸",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function qrcode($id, Request $request) {
//        $validator = Validator::make($request->all(), [
//            'width' => 'required',
//            'height' => 'required',
//        ]);
//
//        if ($validator->fails()) {
//            return $this->json([], $validator->errors()->first(), 0);
//        }
        $size = $request->input("size", 200);
        $shop = Shop::findByEnId($id);
        $user = $this->auth->user();
        /* @var $user User */
        $url = url(sprintf("/#/share/?shopId=%s&userId=%s", $shop->en_id(), $user->en_id()));
        $filename = md5($url."_".$size);
        Storage::disk('public')->put('qrcode/'.$filename.'.png', QrCode::format('png')->size($size)->margin(1)->generate($url));
        return $this->json(['url' => url('storage/qrcode/'.$filename.'.png')]);
    }

    /**
     * @SWG\Post(
     *   path="/shop/join/{id}",
     *   summary="加入店铺",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function join($id) {
        $user = $this->auth->user();
        $shop = Shop::findByEnId($id);
        //#todo
        Notification::send($shop->manager, new ShopApply(['user_id' => $user->id, 'shop_id' => $shop->id, 'type' => ShopApply::TYPE_APPLY]));
        return $this->json();
    }

    /**
     * @SWG\Get(
     *   path="/shop/account/{id}",
     *   summary="店铺帐户信息",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function account($id) {
        $shop = Shop::findByEnId($id);
        return $this->json(['balance' => (double)$shop->balance, 'today_profit' => 0, 'yesterday_profit' => 0, 'total_profit' => 0]);
    }

    /**
     * @SWG\Get(
     *   path="/shop/profit",
     *   summary="所有店铺收益信息",
     *   tags={"店铺"},
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function profit() {
        $user = $this->auth->user();

        return $this->json(['profit' => 0]);
    }

    /**
     * @SWG\Get(
     *   path="/shop/messages",
     *   summary="店铺消息",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="page",
     *     in="path",
     *     description="页码",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="size",
     *     in="path",
     *     description="数目",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function messages(Request $request) {
        $user = $this->auth->user();

        $data = [];
        foreach ($user->unreadNotifications()->where("type", "App\Notifications\ShopApply")->paginate($request->input('size', 20)) as $notification) {
            try {
                $user = User::find($notification->data['user_id']);
                $shop = Shop::find($notification->data['shop_id']);
                $data[] = [
                    'user_avatar' => asset("images/personal.jpg"),
                    'user_name' => $user->name,
                    'shop_name' => $shop->name,
                    'id' => $notification->id,
                    'type' => $notification->data['type']
                ];
            } catch (\Exception $e){}
        }
        return $this->json($data);
    }

    /**
     * @SWG\Get(
     *   path="/shop/messages/count",
     *   summary="店铺未读消息数",
     *   tags={"店铺"},
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function messages_count() {
        $user = $this->auth->user();

        return $this->json(['count' => (int)$user->unreadNotifications()->where("type", "App\Notifications\ShopApply")->count()]);
    }

    /**
     * @SWG\Post(
     *   path="/shop/agree",
     *   summary="店铺同意消息",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="消息id，不传同意全部",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function agree(Request $request) {
        $user = $this->auth->user();
        if ($request->id) {
            $notification = $user->unreadNotifications()->where("id", $request->id)->first();
            if ($notification) {
                $notification->markAsRead();
                try {
                    $user = User::find($notification->data['user_id']);
                    $shop = Shop::find($notification->data['shop_id']);
                    $exist = ShopUser::where("user_id", $user->id)->where("shop_id", $shop->id)->first();
                    if (!$exist) {
                        $shop_user = new ShopUser();
                        $shop_user->shop_id = $shop->id;
                        $shop_user->user_id = $user->id;
                        $shop_user->save();
                    }
                } catch (\Exception $e){}
            }
        } else {
            foreach ($user->unreadNotifications as $notification) {
                try {
                    $user = User::find($notification->data['user_id']);
                    $shop = Shop::find($notification->data['shop_id']);
                    $exist = ShopUser::where("user_id", $user->id)->where("shop_id", $shop->id)->first();
                    if (!$exist) {
                        $shop_user = new ShopUser();
                        $shop_user->shop_id = $shop->id;
                        $shop_user->user_id = $user->id;
                        $shop_user->save();
                    }
                } catch (\Exception $e){}
            }
            $user->unreadNotifications->markAsRead();
        }
        return $this->json();
    }

    /**
     * @SWG\Post(
     *   path="/shop/ignore",
     *   summary="店铺忽略消息",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="消息id,不传忽略全部",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function ignore(Request $request) {
        $user = $this->auth->user();
        if ($request->id) {
            $message = $user->unreadNotifications()->where("id", $request->id)->first();
            if ($message) {
                $message->markAsRead();
            }
        } else {
            $user->unreadNotifications->markAsRead();
        }
        return $this->json();
    }

    /**
     * @SWG\Post(
     *   path="/shop/invite/{shop_id}/{user_id}",
     *   summary="店铺邀请成员",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="shop_id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="path",
     *     description="被邀请人id",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function invite($shop_id, $user_id) {
        $shop = Shop::findByEnId($shop_id);
        $user = User::findByEnId($user_id);
        Notification::send($user, new ShopApply(['user_id' => $user->id, 'invite_id' => $this->auth->user()->id, 'shop_id' => $shop->id, 'type' => ShopApply::TYPE_INVITE]));
//        $user = $this->auth->user();
//        if ($request->id) {
//            $message = $user->unreadNotifications()->where("id", $request->id)->first();
//            if ($message) {
//                $message->markAsRead();
//            }
//        } else {
//            $user->unreadNotifications->markAsRead();
//        }
        return $this->json();
    }

    /**
     * @SWG\Get(
     *   path="/shop/user/search",
     *   summary="手机号搜索用户",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="mobile",
     *     in="path",
     *     description="手机号",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function user_search(Request $request) {
        $validator = Validator::make($request->all(),
            ['mobile' => 'bail|required']
        );
        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $user = User::where("mobile", $request->mobile)->first();
        if (!$user) {
            return $this->json([], 'ok', 1);
        }
        return $this->json([
            'avatar' => $user->avatar,
            'name' => $user->name,
            'id' => $user->en_id(),
            'mobile' => $user->mobile,
        ]);
    }

    /**
     * @SWG\Post(
     *   path="/shop/transfer/{shop_id}",
     *   summary="店铺转账到个人帐户",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="shop_id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="amount",
     *     in="formData",
     *     description="金额",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="path",
     *     description="被邀请人id",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function transfer($shop_id) {
        return $this->json();
    }

    /**
     * @SWG\Post(
     *   path="/shop/transfer/{shop_id}/{user_id}",
     *   summary="店铺转账到成员帐户",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="shop_id",
     *     in="path",
     *     description="店铺id",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="amount",
     *     in="formData",
     *     description="金额",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="path",
     *     description="成员id",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function transfer_member($shop_id, $user_id) {
        return $this->json();
    }
}