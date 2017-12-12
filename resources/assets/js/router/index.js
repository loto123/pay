import Vue from 'vue'
import Mint from 'mint-ui'
import Router from 'vue-router'

import Index from '../view/Index/index.vue'
import Login from './login'
import MyAccount from './myAccount'
import MakeDeal from './makeDeal'
import My from './my'
import Shop from './shop'

import 'mint-ui/lib/style.css'
import '../../sass/oo_flex.scss'
import '../../sass/iconfont.scss'

Vue.use(Mint)
Vue.use(Router)

var index = [
    { path: '/index', name: 'index', component: Index },
]

var routerList = {
    // 登录
    login: Login,
    // 首页
    index: index,
    // 我的账户
    myAccount:MyAccount,
    // 发起交易
    makedeal:MakeDeal,

    my:My,
    
    shop:Shop
};

var router = [];
for (var i in routerList) {
    router = router.concat(routerList[i]);
}

export default new Router({
    routes: router
})
