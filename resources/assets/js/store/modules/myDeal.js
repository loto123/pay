// 任务模块store
const myDealStore = {
  state: {
    dealData:null
  },
  mutations: {
    setDealData(state,value) {
      state.dealData = value;
    }
  },
  actions: {
    deal_setMyData({commit,state},value){
      commit('setDealData',value);
    }
  }
}

export default myDealStore