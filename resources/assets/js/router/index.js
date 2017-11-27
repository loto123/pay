import Vue from 'vue'
import Router from 'vue-router'
import Element from 'element-ui'
// import 'element-ui/lib/theme-default/index.css'


import Index from '../components/ExampleComponent.vue'
import Login from '../view/Login/login.vue'
// Vue.component('Index', require('../components/ExampleComponent.vue'));
// import Blog from '@/view/blog/blog'
// import HousePrice from '@/view/housePrice/housePrice'
// import Couter from '@/view/counter/counter'
// import MyElement from '@/view/element/element'
// import Jquery from '@/view/jquery/jquery'

Vue.use(Router)
Vue.use(Element)

console.log(Element);

export default new Router({
    routes: [
        {
            path: '/index',
            name: 'Index',
            component: Index
        },
        {
            path: '/login',
            name: 'login',
            component: Login
        }
    ]
})
