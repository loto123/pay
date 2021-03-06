import Vue from 'vue'
import Vuex from 'vuex'

import registStore from './modules/regist'
import shopStore from './modules/shop'
import myDealStore from './modules/myDeal'

Vue.use(Vuex);

const store = new Vuex.Store({
  strict: false,// 生产环境记得关闭
  modules: {
    regist: registStore,
    shop:shopStore,
    myDeal:myDealStore
  }
  //  更改store必须执行mutations
  // mutations: {
  //   increment(state, step) {
  //     if (!step) {
  //       state.count++;
  //     } else {
  //       if (step.asyn) {
  //         setTimeout(() => {
  //           state.count += step;
  //         }, 1000);

  //       } else {
  //         state.count += step;
  //       }
  //     }
  //   }
  // },
  // // action 允许异步执行mutations
  // actions: {
  //   increment({ commit, state }, value) {
  //     commit("increment", value);
  //   }
  // }
});

export default store;
