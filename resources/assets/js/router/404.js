import ForOForPage from '../view/404/404.vue'
import NoticePage from '../view/404/notice.vue'

export default[
  {path:'*',name:'pageNotFound',component:ForOForPage},
  {path:'/notice',name:'noticePage',component:NoticePage}
]