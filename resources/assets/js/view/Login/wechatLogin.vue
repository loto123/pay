<template>
  <div id="we-chat">
    {{!bindMobile?"微信登录中...":"绑定微信中..."}}
  </div>
</template>

<style lang="scss" scoped>
</style>

<script>

  import request from '../../utils/userRequest'
  import utils from '../../utils/utils'
  import { Toast } from 'mint-ui'

  export default {
    data() {
      return {
        state: null,
        code: null,
        bindMobile:false
      }
    },
    created() {
      this.init();
    },
    methods: {
      init() {
        this.code = utils.getQueryString("code");
        this.state = utils.getQueryString("state");
        this.bindMobile = window.location.href.split("mobile=")[1];

        var _data = {
          code: this.code,
          state: this.state,
        };

        if (this.bindMobile) {
          _data.user_ticket = this.bindMobile;
        }

        request.getInstance().postData("api/auth/login/wechat", _data).then(res => {

          if (!res.data.data.token) {
            window.location.href = "/#/login/regist/" + "?oauth_user=" + res.data.data.oauth_user;
          } else {

            if(this.bindMobile){
              Toast("微信绑定成功");
            }else {
              Toast("微信登录成功");
            }

            request.getInstance().setToken(res.data.data.token);

            setTimeout(() => {
              window.location.href = "/#/index";
            }, 2000);
          }

        }).catch();
      }
    }

  }
</script>