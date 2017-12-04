import Vue from 'vue'
import Mint from 'mint-ui'
import Router from 'vue-router'

import Index from '../view/Index/index.vue'

// 登录注册
import Login from './login'
import MyAccount from './myAccount'
import MakeDeal from './makeDeal'
import My from './my'

import 'mint-ui/lib/style.css'
import '../../sass/oo_flex.scss'
import '../../sass/iconfont.scss'

Vue.use(Mint)
Vue.use(Router)

var index = [
    { path: '/index', name: 'index', component: Index },
]

var routerList = {
    login: Login,
    index: index,
    myAccount:MyAccount,
    makedeal:MakeDeal,
    my:My
};

var router = [];
for (var i in routerList) {
    router = router.concat(routerList[i]);
}

export default new Router({
    routes: router
})
