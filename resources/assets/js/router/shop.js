import Shop from '../view/Shop/shop.vue'
import MyCollection from '../view/Shop/myCollection.vue'
import MessageList from '../view/Shop/messageList.vue'
import ShopDetail from '../view/Shop/shopDetail.vue'

export default [
    { path: '/shop', name: 'shop', component: Shop },
    { path: '/shop/my_collection', name: 'myCollection', component: MyCollection },
    { path: '/shop/message_list', name: "shopMessageList", component: MessageList },
    { path: '/shop/shop_detail', name: "shopDetail", component: ShopDetail }
]