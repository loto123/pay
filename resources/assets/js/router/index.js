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
import ShareProfit from './shareProfit'
import Vip from './vip'
import ForOForPage from './404'
import SalePet from './dealList'
import Safety from './safety'
import MyReward from './myReward'
import AuthAgent from './authAgent'

import 'mint-ui/lib/style.css'
import '../../sass/oo_flex.scss'
import '../../sass/iconfont.scss'
import '../../sass/new.scss'

Vue.use(Mint)
Vue.use(Router)

var index = [
    { path: '/index', name: 'index', component: Index },
    { path: '/', name: 'indexDefault', component: Index },
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
    share:Share,

	// 分润
    shareProfit:ShareProfit ,
    
    // vip
    vip:Vip,

    //交易行
    salePet:SalePet,

    //安全保障
    safety:Safety,

    //我的赏金
    myReward:MyReward,

    //授权代理
    authAgent:AuthAgent,

    // 404页面
    notFound:ForOForPage
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
