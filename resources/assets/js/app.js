
// require('./bootstrap');
import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
Vue.config.debug = true;
new Vue({
    el: '#app',
    router,
    store,
    template: "<App/>",
    components: { App }
});
