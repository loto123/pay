import Vue from 'vue'
import Mint from 'mint-ui'
import Router from 'vue-router'

import Index from '../view/Index/index.vue'
import Login from './login'
import MyAccount from './myAccount'
import MakeDeal from './makeDeal'
import My from './my'
import Shop from './shop'
import Inform from './inform'
import Share from './share'

import 'mint-ui/lib/style.css'
import '../../sass/oo_flex.scss'
import '../../sass/iconfont.scss'

// import Loading from '../utils/loading'

Vue.use(Mint)
Vue.use(Router)
// Vue.use(Loading);
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
    //我的
    my:My,

    shop:Shop,
    //消息
    inform:Inform,

    // 分享
    share:Share
};

var router = [];
for (var i in routerList) {
    router = router.concat(routerList[i]);
}

export default new Router({
    routes: router,
    // 新页面不记录滑动位置
    scrollBehavior (to, from, savedPosition) {
      return { x: 0, y: 0 }
    }
})
