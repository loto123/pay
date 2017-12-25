import Login from '../view/Login/login.vue'
import Regist from '../view/Login/regist.vue'
import WechatLogin from '../view/Login/wechatLogin.vue'

export default [
    { path: '/login', name: 'login', component: Login },
    { path: '/login/regist', name: 'regist', component: Regist },
    { path: '/login/weChatLogin', name: 'weChatLogin', component: Regist }
]