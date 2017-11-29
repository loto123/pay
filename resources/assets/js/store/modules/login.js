// 登录模块store

const loginModule = {
    state: {
        "name": 'sangliang'
    },
    mutations: {
        // 更改名字
        changeName(state, name) {
            state.name = name;
        }
    },
    actions: {
        changeName({ commit, state }, value) {
            commit("changeName", value)
        }
    }
}

export default loginModule