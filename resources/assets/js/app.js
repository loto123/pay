
require('./bootstrap');
import Vue from 'vue'
import App from './App.vue'

import router from './router'
import store from './store'

// Vue.component('example-component', require('./components/ExampleComponent.vue'));
//  Vue.component('App', require('./App.vue'));

const app = new Vue({
    el: '#app',
    router,
    store,
    template: "<App/>",
    components: { App }
});
