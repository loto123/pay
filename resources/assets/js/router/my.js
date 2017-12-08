import My from '../view/My/my.vue'
import Set from '../view/My/set.vue'
import Referrer from '../view/My/referrer.vue'
import BankCardManage from '../view/My/bankCardManage.vue'
import AddBankCard from '../view/My/addBankCard.vue'

export default [
    { path: '/my', name: 'my', component: My },
    { path:'/my/set', name:'set', component: Set },
    { path:'/my/referrer', name:'referrer', component: Referrer },
    { path:'/my/bankCardManage', name:'bankCardManage', component: BankCardManage },
    { path:'/my/bankCardManage/addBankCard', name:'addBankCard', component: AddBankCard }
]