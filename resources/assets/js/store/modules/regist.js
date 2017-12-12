// 登录模块store

const registStore = {
    state: {
        "step": 0,
        "refindPassword":false
    },
    mutations: {
        addStep(state) {
            state.step += 1;
        },
        setStep(state, value) {
            state.step = value;
        },
        setRefindPassWordState(state,value){
            state.refindPassword = value;
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
        }
    }
}

export default registStore