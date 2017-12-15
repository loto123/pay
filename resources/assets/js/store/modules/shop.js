// 登录模块store

const shopStore = {
    state: {
        shopDetailId:null
    },

    mutations: {
       setShopDetailId(state,value){
        state.shopDetailId = value;
       }

    },
    actions: {
      shop_setShopDetailId({commit,state},value){
        commit("setShopDetailId",value);
      }
    }
}

export default shopStore