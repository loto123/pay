import Inform from '../view/Inform/inform.vue'
import MoneyDetails from '../view/Inform/moneyDetails.vue'
import UserRegister from '../view/Inform/userRegister.vue'
import SystemInfo from '../view/Inform/systemInfo.vue'
import SystemDetails from '../view/Inform/systemDetails.vue'
export default [
    { path: '/inform', name: 'inform', component: Inform },
    { path: '/inform/money_details', name: 'moneyDetails', component: MoneyDetails },
    { path: '/userRegister', name: 'userRegister', component:UserRegister },
    { path: '/systemInfo', name: 'systemInfo', component:SystemInfo },
    { path: '/systemInfo/system_details', name: 'systemDetails', component:SystemDetails }
]