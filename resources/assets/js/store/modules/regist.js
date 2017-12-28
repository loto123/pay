// 登录模块store

const registStore = {
    state: {
        "step": 0,
        "refindPassword":false,
        "name":"sangliang",
        "password":null,
        "mobile":null
    },

    mutations: {
        addStep(state) {
            state.step = parseInt(state.step)+ 1;
        },
        setStep(state, value) {
            state.step = value;
        },
        setRefindPassWordState(state,value){
            state.refindPassword = value;
        },

        setPassword(state,value){
            state.password = value
        },
        setAccountName(state,value){
            state.password = value
        }

    },
    actions: {
        addStep({ commit, state }) {
            commit("addStep");
        },
        setStep({ commit, state }, value) {
            commit("setStep", value)
        },
        setRefindPassWordState({ commit, state },value){
            commit("setRefindPassWordState",value);
        },
        regist_setPassword({ commit, state },value){
            commit("setPassword",value);
        },
        regist_setAccountName({ commit, state },value){
            commit("setAccountName",value);
        }
    }
}

export default registStore