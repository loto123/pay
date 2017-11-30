import MyAccount from '../view/MyAccount/myAccount.vue'
import Withdraw from '../view/MyAccount/withdraw.vue'
import Bill from '../view/MyAccount/bill.vue'

export default [
    { path: '/myAccount', name: 'myAccount', component: MyAccount },
    { path: '/myAccount/withdraw', name: 'withdraw', component: Withdraw },
    { path: '/myAccount/bill', name: 'bill', component: Bill }
]