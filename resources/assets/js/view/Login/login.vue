
<template>
  <!-- 登录模块 -->
  <div id="login">
    <div class="top flex flex-reverse flex-align-center">
      <!--<a href="javascript:;" @click = "regist">注册</a>-->
    </div>
    
    <div class="logo-wrap flex flex-v flex-align-center" >
      <div class="circle-wrap flex flex-align-center">
        <img src="/images/logo.png" alt="logo">
      </div> 
      <h3>聚宝朋</h3>
    </div>

    <div class="text-area flex flex-v flex-justify-center">
      <mt-field label="手机号" placeholder="请输入手机号"  v-model="mobile"></mt-field>
      <mt-field label="密码" placeholder="请输入密码" type="password" v-model="password"></mt-field>
    </div>
    
    <div class="login-button flex flex-justify-center">
      <mt-button type="primary" size="large" @click="login">登录</mt-button>
    </div>
    
    <div class="forget-password flex flex-reverse flex-align-center flex-justify-between">

      <a href="javascript:;" @click="forgetPassWord">
        忘记密码
      </a>

      <a href="javascript:;" @click="regist" >
        注册
      </a>
    </div>

    <div class="bottom flex flex-v flex-align-center">
      <hr>
      <div class="text">
        其他登录
      </div>
      <div class="login-type flex flex-v flex-align-center">
        <a href="javascript:;" class="flex flex-v flex-align-center" @click="weChatLogin">
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
  width: 90%;
  margin:auto;
  margin-top: 3em;
}

.forget-password{
  margin-top:2em;
  height: 2em;
  padding-right:2em;
  padding-left: 2em;
  box-sizing: border-box;

  >a{
    color:#26a2ff;
  }
}

.bottom {
  width: 100%;
  margin-top: 1em;
  height: 0.5em;
  
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
import  Loading from '../../utils/loading.js'

export default {
  name: "login",
  data() {
    return {
      name: null,
      mobile:null,
      password:null,
      userId:null,
      
      url:window.location.href.split('#')[0]
    };
  },
  computed: {
    count() {
      return this.$store.state.count;
    },

    username() {
      return this.$store.state.login.name;
    }
  },

  methods: {
    go() {
      this.$store.dispatch("increment", 15);
    },

    // 普通登录
    login() {
      var self = this;

      var data = {
        mobile: this.mobile,
        password:this.password
      }

      request.getInstance().postData('api/auth/login',data).then(function(res){
          self.userId = res.data.data.ticket;
          localStorage.setItem("login_id",res.data.data.id);
          // 微信登录控制，生产环境开启
          if(res.data.data.wechat == 0 && debug == 0){
             Toast("登录成功，正在跳转绑定微信...");
             setTimeout(()=>{
                 Loading.getInstance().open();
             },1000);

             return Promise.resolve(true);
          }

          request.getInstance().setToken(res.data.data.token);
          Toast("登录成功");
          var _url = localStorage.getItem("url");
          if(!_url){
            self.$router.push("/index");
          }else {
            localStorage.removeItem("url");
            setTimeout(()=>{
              window.location.href = _url;
            },1500);
          }
      }).then(res=>{
        if(res == true){
          // 是否需要绑定微信
          this.weChatBind(self.userId);
        }
      }).catch(function(err){
        Toast(err.data.msg);
      });

    },

    // 微信登录
    weChatLogin(){
      var _data={
        redirect_url:this.url+"#/login/weChatLogin"
      };

      request.getInstance().getData("api/auth/login/wechat/url",_data).then(res=>{
        window.location.href = res.data.data.url;
      }).catch(err=>{
        Toast(err.data.msg);
      });
    },

    weChatBind(mobile){
      var _data={
        redirect_url:this.url+"#/login/weChatLogin"+"?mobile="+ mobile
      };

      request.getInstance().getData("api/auth/login/wechat/url",_data).then(res=>{
        window.location.href = res.data.data.url;
        Loading.getInstance().close();

      }).catch(err=>{
        Toast(err.data.msg);
      });
    },

    commitName() {
      this.$store.dispatch("changeName", this.name);
    },

    regist(){
        this.$store.dispatch("setStep",0);
        localStorage.setItem("registStep",0);
        this.$store.dispatch("setRefindPassWordState",false);
        this.$router.push("/login/regist?"+"types=1");
    },

    // 忘记密码
    forgetPassWord(){
      this.$store.dispatch("setStep",1);
      this.$store.dispatch("setRefindPassWordState",true);
      localStorage.setItem("registStep",1);
      this.$router.push("/login/regist");
    }
  }
};

</script>

