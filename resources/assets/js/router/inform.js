import Inform from '../view/Inform/inform.vue'
import UserRegister from '../view/Inform/userRegister.vue'
import SystemInfo from '../view/Inform/systemInfo.vue'
import InfoDetails from '../view/Inform/infoDetails.vue'
export default [
    { path: '/inform', name: 'inform', component: Inform },
    { path: '/userRegister', name: 'userRegister', component:UserRegister },
    { path: '/systemInfo', name: 'systemInfo', component:SystemInfo },
    { path: '/systemInfo/info_Details', name: 'infoDetails', component:InfoDetails }
]