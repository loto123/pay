import MyAccount from '../view/MyAccount/myAccount.vue'
import Withdraw from '../view/MyAccount/withdraw.vue'
import Give from '../view/MyAccount/give.vue'
import Bill from '../view/MyAccount/bill.vue'
import BillDetails from '../view/MyAccount/billDetails.vue'

export default [
    { path: '/myAccount', name: 'myAccount', component: MyAccount },
    { path: '/myAccount/withdraw', name: 'withdraw', component: Withdraw },
    { path: '/myAccount/give', name: 'give', component: Give },
    { path: '/myAccount/bill', name: 'bill', component: Bill },
    { path: '/myAccount/billDetails', name: 'billDetails', component: BillDetails }
]