<template>
<!-- 注册模块 -->
    <div id="regist">
        <topBack></topBack>
        <section class="content step1" v-if="step==0?true:false">
            <h3>
                请输入推荐码
            </h3>
            <p>推荐码为推荐人的手机号</p>

            <section class="input-wrap">
                <mt-field label="推荐码" placeholder="请输入推荐人手机号" type="tel"></mt-field>
            </section>

            <div class="submit-button flex flex-justify-center">
                <mt-button type="primary" size="large" v-on:click="comfirm">确认</mt-button>
            </div>
        </section>

        <transition name="fade">
            <section class="content step2" v-if="step==1?true:false">
                <h3>
                    {{findPasswordSwitch?"手机号验证":"输入手机号快速注册"}}
                </h3>

                <section class="input-wrap">
                    <mt-field label="手机号" placeholder="请输入手机号" type="tel" v-model="userAccountName"></mt-field>
                </section>

                <div class="submit-button flex flex-justify-center">
                    <mt-button type="primary" size="large" v-on:click="goNextStep">{{findPasswordSwitch?"下一步":"注册"}}</mt-button>
                </div>

                <p class="agreement-btn" v-if="!findPasswordSwitch">注册代表你同意 <a href="javascript:;" @click="showAgreement"> 结算宝服务使用协议 </a> </p>
            </section>
        </transition>  

        <transition name="fade">
            <section class="content step3" v-if="step==2?true:false">
                <h3>
                    请输入验证码
                </h3>

                <p>短信验证码已发送至1008666666</p>

                <section class="input-wrap flex flex-align-center">
                    <span class="flex-1">验证码:</span>
                    <input type="text" placeholder="请输入验证码" class="flex-1">
                    <mt-button type="default" class="flex-1">发送验证码(10)</mt-button>
                    
                </section>

                <div class="submit-button flex flex-justify-center">
                    <mt-button type="primary" size="large" v-on:click="goNextStep">确定</mt-button>
                </div>

            </section>
        </transition>  

        <transition name="fade">
            <section class="content step4" v-if="step==3?true:false">
                <h3>
                    设置登录密码
                </h3>

                <p>密码又8-16位数字、字母或符号组成</p>

                <section class="input-wrap ">
                    <mt-field label="密码" placeholder="请输入登录密码" type="password" v-model="userPassword"></mt-field>
                </section>

                <div class="submit-button flex flex-justify-center">
                    <mt-button type="primary" size="large" v-on:click="goNextStep">确定</mt-button>
                </div>

            </section>
        </transition>  

        <transition name="slide">
            <section class="agreement-main" v-if="agrementState" v-on:click="closeAgreenment">
                <h3>用户协议</h3>
                <p>用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议用户协议</p>
            </section>
        </transition>
    </div>
    
</template>

<style lang="scss" scoped>

.fade-enter-active {
  transition: opacity 1s;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: all 1s ease;
}

.slide-enter,
.slide-leave-to {
  transform: translateY(100vh);
}

.content {
  h3 {
    font-size: 1.9rem;
    text-align: center;
    margin-top: 2em;
  }

  > p {
    color: #999;
    text-align: center;
    margin-top: 1.4em;
  }

  .input-wrap {
    margin-top: 2em;
  }

  .submit-button {
    margin-top: 2em;
  }

  .agreement-btn {
    > a {
      color: #26a2ff;
    }
  }
}

.step3 .input-wrap {
  width: 100%;
  height: 3em;
  border-bottom: 1px solid #eee;

  .mint-button {
    font-size: 0.9em;
  }
  .mint-button--default {
    background: #fff;
  }
}

.step3 .input-wrap {
  padding-left: 1em;
  padding-right: 1em;
  box-sizing: border-box;
}

.step3 .input-wrap input {
  border: none;
  outline: none;
  text-rendering: auto;
  color: initial;
  letter-spacing: normal;
  word-spacing: normal;
  text-transform: none;
  text-indent: 0px;
  text-shadow: none;
  display: inline-block;
  text-align: start;
  height: 2em;
  padding-left: 1em;
  padding-right: 1em;
  box-sizing: border-box;
  width: 20%;
}

// 用户协议详情
.agreement-main {
  width: 100%;
  height: 100vh;
  background: #fff;
  padding-bottom: 2em;
  padding-top: 1em;
  position: absolute;
  top: 0em;
  left: 0em;
  h3 {
    font-size: 1.8em;
    text-align: center;
    padding-top: 0.5em;
    padding-bottom: 0.5em;
  }

  p {
    line-height: 1.5;
    box-sizing: border-box;
    padding-left: 1em;
    padding-right: 1em;
    text-indent: 2em;
  }
}
</style>

<script>
import topBack from "../../components/topBack";
import request from "../../utils/userRequest.js"
import { Toast } from 'mint-ui';

export default {
  name: "regist",
  data() {
    return {
      step: 0,
      agrementState: false,
      findPasswordSwitch: false,

      userAccountName:null,
      userPassword:null
    };
  },

  mounted() {
    if (this.$store.state.regist.refindPassword == true) {
      this.findPasswordSwitch = this.$store.state.regist.refindPassword;
      this.step = this.$store.state.regist.step;

      localStorage.setItem("findPasswordSwitch", this.findPasswordSwitch);
      localStorage.setItem("registStep", this.step);
    }
  },
  methods: {
    comfirm() {
      if (this.step == 0) {
        this.step = 1;
      }
    },

    goNextStep() {
      var self = this;

      if (this.step >= 3) {
        var data = {
          mobile :this.userAccountName,
          password :this.userPassword,
          name:"sangliang"
        }

        request.getInstance().postData('api/auth/register',data).then(function(res){

          if(res.data.code == 0){
            sessionStorage.setItem("_token",res.data.data.token);
            Toast("注册成功");
            self.$router.push("/login");
          }

          console.log(res);
        });
        return;
      }
      this.step = this.step + 1;
    },

    showAgreement() {
      this.agrementState = true;
    },
    closeAgreenment() {
      this.agrementState = false;
    }
  },
  components: { topBack }
};
</script>

  
