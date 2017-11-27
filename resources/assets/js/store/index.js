import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

const store = new Vuex.Store({
  state: {
    count: 0
  },

  //  更改store必须执行mutations
  mutations: {
    increment(state, step) {

      console.log(step);

      if (!step) {
        state.count++;
      } else {
        if (step.asyn) {
          setTimeout(() => {
            state.count += step;
          }, 1000);

        } else {
          state.count += step;
        }
      }
    }
  },
  // action 允许异步执行mutations
  actions: {
    increment({commit,state}, value) {
      commit("increment", value);
    }
  }
});

export default store;
