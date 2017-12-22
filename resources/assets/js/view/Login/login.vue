
<template>
  <!-- 登录模块 -->
  <div id="login">
    <div class="top flex flex-reverse flex-align-center">
      <a href="javascript:;" @click = "regist">注册</a>
    </div>
    
    <div class="logo-wrap flex flex-v flex-align-center" >
      <div class="circle-wrap flex flex-align-center">
        <img src="/images/logo.png" alt="logo">
      </div> 
      <h3>游戏宝</h3>
    </div>

    <div class="text-area flex flex-v flex-justify-center">
      <mt-field label="手机号" placeholder="请输入手机号" type="tel" v-model="mobile"></mt-field>
      <mt-field label="密码" placeholder="请输入密码" type="password" v-model="password"></mt-field>
    </div>
    
    <div class="login-button flex flex-justify-center">
      <mt-button type="primary" size="large" @click="login">登录</mt-button>
    </div>
    
    <div class="forget-password flex flex-reverse flex-align-center">
      <a href="javascript:;" @click="forgetPassWord">
        忘记密码
      </a>
    </div>

    <div class="bottom flex flex-v flex-align-center">
      <hr>
      <div class="text">
        其他登录
      </div>
      <div class="login-type flex flex-v flex-align-center">
        <a href="javascript:;" class="flex flex-v flex-align-center">
          <img src="/images/weichat_login.png" alt="">
          <p>微信登录</p>
        </a>
      </div>
    </div>

  </div>

</template>

<style lang="scss" scoped>
.top {
  height: 2em;
  width: 100%;

  padding-right: 0.8em;
  box-sizing: border-box;
  > a {
    font-size: 1em;
  }
}

.logo-wrap {
  width: 100%;
  height: auto;

  .circle-wrap {
    width: 7em;
    height: 7em;
    border-radius: 50%;
    border: 1px solid #eee;
  }

  img {
    display: block;
    width: 5em;
    height: 5em;
    margin: 0 auto;
  }

  h3 {
    font-size: 1.3em;
    text-align: center;
    margin-top: 0.7em;
  }
}

.text-area {
  margin-left: auto;
  margin-right: auto;
  margin-top: 2em;
  width: 95%;
}

.login-button {
  width: 100%;
  margin-top: 3em;
}

.forget-password{
  height: 2em;
  padding-right:1em;
  box-sizing: border-box;

  >a{
    color:#26a2ff;
  }
}

.bottom {
  width: 100%;
  // margin-top: 5em;
  height: 5em;

  hr {
    border: none;
    border-top: 1px solid #eee;
    height: 0;
    width: 100%;
  }

  .text {
    margin-top: -1.2em;
    width: 8em;
    height: 1em;
    background: #fff;
    text-align: center;
    color: #999;
  }

  .login-type {
    margin-top: 1em;
    width: 100%;
    img {
      width: 2em;
      display: block;
    }
    p {
      text-align: center;
      font-size: 0.8em;
      color: #999;
    }
  }
}
</style>

<script>
import axios from "axios";
import { Toast } from "mint-ui";
import request from '../../utils/userRequest'

export default {
  name: "login",
  data() {
    return {
      name: null,
      mobile:null,
      password:null
    };
  },
  computed: {
    count() {
      return this.$store.state.count;
    },

    username() {
      console.log(this);
      return this.$store.state.login.name;
    }
  },

  methods: {
    go() {
      this.$store.dispatch("increment", 15);
    },

    login() {
      var self = this;

      var data = {
        mobile: this.mobile,
        password:this.password
      }

      request.getInstance().postData('api/auth/login',data).then(function(res){
        console.log(res);
          sessionStorage.setItem("_token",res.data.data.token);
          Toast("登录成功");
          self.$router.push("/index");
      }).catch(function(err){
        console.log(err);
        Toast(err.data.message);
      });
    },
    commitName() {
      this.$store.dispatch("changeName", this.name);
    },

    regist(){
        this.$store.dispatch("setStep",0);
        this.$store.dispatch("setRefindPassWordState",false);
        this.$router.push("/login/regist");
    },

    // 忘记密码
    forgetPassWord(){
      
      this.$store.dispatch("setStep",1);
      this.$store.dispatch("setRefindPassWordState",true);
      this.$router.push("/login/regist");
    }


  }
};
</script>

