import Shop from '../view/Shop/shop.vue'
import MyCollection from '../view/Shop/myCollection.vue'

export default [
    { path: '/shop', name: 'shop', component: Shop },
    { path: '/shop/my_collection', name: 'myCollection', component: MyCollection }
]