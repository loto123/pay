import Vue from 'vue'
import Mint from 'mint-ui'
import Router from 'vue-router'
import Index from '../components/ExampleComponent.vue'

// 登录注册
import Login from './login'
import MyAccount from './myAccount'

import 'mint-ui/lib/style.css'
import '../../sass/oo_flex.scss'

Vue.use(Mint)
Vue.use(Router)

var index = [
    { path: '/index', name: 'index', component: Index },
]

var routerList = {
    login: Login,
    index: index,
    myAccount:MyAccount
};

var router = [];
for (var i in routerList) {
    router = router.concat(routerList[i]);
}

export default new Router({
    routes: router
})
