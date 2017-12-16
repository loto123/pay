import My from '../view/My/my.vue'
import Set from '../view/My/set.vue'
import Referrer from '../view/My/referrer.vue'
import BankCardManage from '../view/My/bankCardManage.vue'
import RealAuth from '../view/My/realAuth.vue'
import AddBankCard from '../view/My/addBankCard.vue'
import CheckSettle from '../view/My/checkSettle.vue'
import LoginPassword from '../view/My/loginPassword.vue'
import PayPassword from '../view/My/payPassword.vue'
export default [
    { path: '/my', name: 'my', component: My },
    { path:'/my/set', name:'set', component: Set },
    { path:'/my/referrer', name:'referrer', component: Referrer },
    { path:'/my/bankCardManage', name:'bankCardManage', component: BankCardManage },
    { path:'/my/realAuth', name:'realAuth', component: RealAuth },
    { path:'/my/bankCardManage/addBankCard', name:'addBankCard', component: AddBankCard },
    { path:'/my/checkSettle', name:'checkSettle', component: CheckSettle },
    { path:'/my/login_password', name:'loginPassword', component: LoginPassword },
    { path:'/my/pay_password', name:'payPassword', component: PayPassword }
]