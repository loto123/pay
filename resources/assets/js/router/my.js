import My from '../view/My/my.vue'
import Set from '../view/My/set.vue'
import Referrer from '../view/My/referrer.vue'
import BankCardManage from '../view/My/bankCardManage.vue'
import RealAuth from '../view/My/realAuth.vue'
import AddBankCard from '../view/My/addBankCard.vue'
import CheckSettle from '../view/My/checkSettle.vue'
import LoginPassword from '../view/My/loginPassword.vue'
import VerfyCode from '../view/My/verfyCode.vue'
import PayPassword from '../view/My/payPassword.vue'
import About from '../view/My/about.vue'
import SettingPassword from '../view/My/settingPassword.vue'

export default [
    { path: '/my', name: 'my', component: My },
    { path:'/my/set', name:'set', component: Set },
    { path:'/my/set/about', name:'about', component: About },
    { path:'/my/referrer', name:'referrer', component: Referrer },
    { path:'/my/bankCardManage', name:'bankCardManage', component: BankCardManage },
    { path:'/my/realAuth', name:'realAuth', component: RealAuth },
    { path:'/my/bankCardManage/addBankCard', name:'addBankCard', component: AddBankCard },
    { path:'/my/checkSettle', name:'checkSettle', component: CheckSettle },
    { path:'/my/login_password', name:'loginPassword', component: LoginPassword },
    { path:'/my/verfy_code', name:'verfyCode', component: VerfyCode },
    { path:'/my/pay_password', name:'payPassword', component: PayPassword },
    { path:'/my/setting_password', name:'settingPassword', component: SettingPassword }
    
]