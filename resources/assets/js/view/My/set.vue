<template>
    <div id="set">
        <topBack title="更多"></topBack>
        <section>
            <ul class="list">
                <li>
                    <mt-cell title="修改登录密码" is-link to="/my/login_password"></mt-cell>
                </li>
                <li @click="verfyCode">
                    <mt-cell title="设置支付密码" is-link></mt-cell>
                </li>
            </ul>
            <ul class="list mt1">
                <li v-if="api_user_feedback" @click="help">
                    <mt-cell title="帮助与反馈" is-link></mt-cell>
                </li>
                <li>
                    <mt-cell title="关于" is-link to="/my/set/about"></mt-cell>
                </li>
            </ul>
            <div class="exit" @click="exit">
                <mt-button type="danger" size="large">退出登录</mt-button>
            </div>
        </section>
    </div>
</template>


<script>
    import topBack from "../../components/topBack";
    import Loading from "../../utils/loading.js"
    import request from "../../utils/userRequest.js"
    import utils from "../../utils/utils.js"
    import {Toast} from "mint-ui"

    export default {
        data() {
            return {
                mobile: null,
                api_user_feedback:null
            }
        },
        components: { topBack },
        created(){
            this.init()
        },
        methods: {
            init(){
                request.getInstance().getData('api/index/settings')
               .then(res=>{
                   this.api_user_feedback=res.data.data.api_user_feedback
               })
               .catch(err=>{
                Toast(err.data.msg);
               })     
            },
            verfyCode() {
                request.getInstance().getData('api/my/info')
                    .then((res) => {
                        if (res.data.data.has_pay_password == 0) {
                            //调转到设置支付密码
                            this.$router.push('/my/setting_password');
                        } else {
                            this.mobile = this.$route.query.mobile;
                            this.$router.push('/my/pay_password?mobile=' + this.mobile);
                        }
                    })
                    .catch((err) => {
                        Toast(err.data.msg);
                    })
            },
            exit() {
                request.getInstance().removeToken();
                Toast("用户已经退出...");
                setTimeout(function () {
                    window.location.href = "/#/login"
                }, 2000);
            },
            help() {
                location.href = this.api_user_feedback;
            }
        }
    }
</script>

<style lang="scss" scoped>
    .mt1 {
        margin-top: 1em;
    }

    #set {
        background: #eee;
        padding-top: 2em;
        box-sizing: border-box;
        height: 100vh;
    }

    .list {
        border-bottom: 1px solid #d9d9d9;
        li {
            .mint-cell {
                background-image: none;
                background-size: 100% 1px;
                background-repeat: no-repeat;
                background-position: top;
            }
        }
    }

    .exit {
        width: 80%;
        margin: auto;
        margin-top: 4em;
    }
</style>