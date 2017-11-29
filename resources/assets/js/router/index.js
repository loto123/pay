import Vue from 'vue'
import Mint from 'mint-ui'

import Router from 'vue-router'
import Index from '../components/ExampleComponent.vue'
import Login from '../view/Login/login.vue'
import Myaccount from '../view/MyAccount/myAccount.vue'//我的账户
import 'mint-ui/lib/style.css'
// import '../../sass/oo_flex.scss'

Vue.use(Mint)
Vue.use(Router)
export default new Router({
    routes: [
        {
            path: '/index',
            name: 'index',
            component: Index
        },
        {
            path: '/login',
            name: 'login',
            component: Login
        },
        {
            path:'/myAccount',
            name:'/myAccount',
            component:Myaccount
        }
    ]
})
