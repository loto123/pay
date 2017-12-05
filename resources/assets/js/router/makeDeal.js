import MakeDeal from '../view/MakeDeal/makeDeal.vue'
import MakeDealDetail from '../view/MakeDeal/makeDealDetail.vue'
// import Regist from '../view/Login/regist.vue'

export default [
    { path: '/makeDeal', name: 'makeDeal', component: MakeDeal },
    { path: '/makeDeal/deal_detail', name: 'dealDetail', component: MakeDealDetail }
    
    // { path: '/login/regist', name: 'regist', component: Regist }
]