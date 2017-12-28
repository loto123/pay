import Shop from '../view/Shop/shop.vue'
import MyCollection from '../view/Shop/myCollection.vue'
import MessageList from '../view/Shop/messageList.vue'
import ShopDetail from '../view/Shop/shopDetail.vue'
import ShopMember from '../view/Shop/shopMember.vue'
import Record from '../view/Shop/record.vue'
import RecordDetails from '../view/Shop/recordDetails.vue'
import DealManagement from '../view/Shop/dealManagement.vue'
import ShopAccount from '../view/Shop/shopAccount.vue'
import ShopOrder from '../view/Shop/shopOrder.vue'
import Withdraw from '../view/Shop/withdraw.vue'
import Give from '../view/Shop/give.vue'
import ShopShare from '../view/Shop/shopShare.vue'

export default [
    { path: '/shop', name: 'shop', component: Shop },
    { path: '/shop/my_collection', name: 'myCollection', component: MyCollection },
    { path: '/shop/message_list', name: "shopMessageList", component: MessageList },
    { path: '/shop/shop_detail', name: "shopDetail", component: ShopDetail },
    { path: '/shop/shop_member', name: "shopMember", component: ShopMember },
    { path: '/shop/deal_management', name: "dealManagement", component: DealManagement },
    { path: '/shop/shopAccount', name: "shopAccount", component: ShopAccount },
    { path: '/shop/shopAccount/withdraw', name: "shopWithdraw", component: Withdraw },
    { path: '/shop/shopAccount/give', name: "shopGive", component: Give },
    { path: '/shop/shopOrder', name: "shopOrder", component: ShopOrder },
    { path: '/shop/shopShare', name: "shopShare", component: ShopShare },                  // 店铺分享
    { path: '/shop/record', name: "record", component: Record },                  // 提转记录
    { path: '/shop/record/record_details', name: "recordDetails", component: RecordDetails }                  // 提转记录
    
    // { path: '/shop/shop_account', name: "shopDetail", component: ShopDetail },
]