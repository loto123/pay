import MyAccount from '../view/MyAccount/myAccount.vue'
import Withdraw from '../view/MyAccount/withdraw.vue'
import Recharge from '../view/MyAccount/recharge.vue'
import Give from '../view/MyAccount/give.vue'
import Bill from '../view/MyAccount/bill.vue'
import BillDetails from '../view/MyAccount/billDetails.vue'
import StatusList from '../view/MyAccount//statusList.vue'

export default [
    { path: '/myAccount', name: 'myAccount', component: MyAccount },
    { path: '/myAccount/withdraw', name: 'withdraw', component: Withdraw },
    { path: '/myAccount/recharge', name: 'recharge', component: Recharge },
    { path: '/myAccount/give', name: 'give', component: Give },
    { path: '/myAccount/bill', name: 'bill', component: Bill },
    { path: '/myAccount/bill/bill_details', name: 'billDetails', component: BillDetails },
    { path: '/myAccount/withdraw/status_list', name: 'statusList', component: StatusList }

]