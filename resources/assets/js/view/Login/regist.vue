<template>
<!-- 注册模块 -->
    <div id="regist">
        <topBack></topBack>
        <section class="content step1" v-if="$store.state.regist.step==0?true:false">
            <h3>
                请输入推荐码
            </h3>
            <p>推荐码为推荐人的手机号</p>

            <section class="input-wrap">
                <mt-field label="推荐码" placeholder="请输入推荐人手机号" type="tel" v-model="inviteMobile"></mt-field>
            </section>

            <div class="submit-button flex flex-justify-center">
                <mt-button type="primary" size="large" v-on:click="comfirm">确认</mt-button>
            </div>
        </section>

        <transition name="fade">
            <section class="content step2" v-if="$store.state.regist.step==1?true:false">
                <h3>
                    {{$store.state.regist.refindPassword?"手机号验证":"输入手机号快速注册"}}
                </h3>

                <section class="input-wrap">
                    <mt-field label="手机号" placeholder="请输入手机号" type="tel" v-model="userAccountName"></mt-field>
                </section>

                <div class="submit-button flex flex-justify-center">
                    <mt-button type="primary" size="large" v-on:click="comfirm">{{$store.state.regist.refindPassword?"下一步":"注册"}}</mt-button>
                </div>

                <p class="agreement-btn" v-if="!$store.state.regist.refindPassword">注册代表你同意 <a href="javascript:;" @click="showAgreement"> 结算宝服务使用协议 </a> </p>
            </section>
        </transition>  

        <transition name="fade">
            <section class="content step3" v-if="$store.state.regist.step==2?true:false">
                <h3>
                    请输入验证码
                </h3>

                <p v-if="smsTimer">短信验证码已发送至{{userAccountName}}</p>

                <section class="input-wrap flex flex-align-center">
                    <span class="flex-1">验证码:</span>
                    <input type="text" placeholder="请输入验证码" class="flex-1" v-model="validCode">
                    <mt-button type="default" class="flex-1" @click="sendSMS">发送验证码{{smsTimer?"("+smsTimer+")":""}}</mt-button>
                    
                </section>

                <div class="submit-button flex flex-justify-center">
                    <mt-button type="primary" size="large" v-on:click="comfirm">确定</mt-button>
                </div>

            </section>
        </transition>  

        <transition name="fade">
            <section class="content step4" v-if="$store.state.regist.step==3?true:false">
                <h3>
                    设置登录密码
                </h3>

                <p>密码由8-16位数字、字母或符号组成</p>

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
                <p>
                  尊敬的用户，欢迎阅读本协议：
                </p>
                <p>
                  长沙找找公司依据本协议的规定提供服务，本协议具有合同效力。您必须完全同意以下所有条款并完成个人资料的填写，才能保证享受到更好的聚宝朋客服服务。您使用服务的行为将视为对本协议的接受，并同意接受本协议各项条款的约束。
                </p>

                <p>
                  用户必须合法使用网络服务，不作非法用途，自觉维护本网站的声誉，遵守所有使用网络服务的网络协议、规定、程序和惯例。
                </p>
                <p>
                  为更好的为用户服务，用户应向本网站提供真实、准确的个人资料，个人资料如有变更，应立即修正。如因用户提供的个人资料不实或不准确，给用户自身造成任何性质的损失，均由用户自行承担。
                </p>
                <p>
                  尊重个人隐私是找找公司的责任，找找公司在未经用户授权时不得向第三方（找找公司控股或关联、运营合作单位除外）公开、编辑或透露用户个人资料的内容，但由于政府要求、法律政策需要等原因除外。在用户发送信息的过程中和本网站收到信息后，本网站将遵守行业通用的标准来保护用户的私人信息。但是任何通过因特网发送的信息或电子版本的存储方式都无法确保100%的安全性。因此，本网站会尽力使用商业上可接受的方式来保护用户的个人信息，但不对用户信息的安全作任何担保。
                </p>
                <p>
                  本网站有权在必要时修改服务条例，本网站的服务条例一旦发生变动，将会在本网站的重要页面上提示修改内容，用户如不同意新的修改内容，须立即停止使用本协议约定的服务，否则视为用户完全同意并接受新的修改内容。根据客观情况及经营方针的变化，本网站有中断或停止服务的权利，用户对此表示理解并完全认同。
                </p>
                <p>
                  本保密协议的解释权归长沙找找公司所有。
                </p>
                <p>
                  长沙找找公司
                </p>
                <p>
                  2017年12月30日
                </p>

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
import Loading from '../../utils/loading'

export default {
  name: "regist",
  data() {
    return {
      agrementState: false,           // 用户协议开关
      findPasswordSwitch: false,      // 找回密码开关

      userAccountName:null,           // 用户名
      userPassword:null,              // 密码
      inviteMobile:null,              // 邀请人手机号
      validCode:null,                 // 验证码

      smsTimer:null,                     //短信验证倒计时

      mobile:null
    };
  },
  created () {
    this.init();
  },
  mounted() {
    if (this.$store.state.regist.refindPassword == true) {
      localStorage.setItem("findPasswordSwitch", this.findPasswordSwitch);
      localStorage.setItem("registStep", this.$store.state.regist.step);
    }

    var _step = localStorage.getItem("registStep");
    var _findPassWord = localStorage.getItem("findPasswordSwitch");

    console.log(_findPassWord);

    if(_step && _findPassWord == true){
      this.$store.dispatch("setRefindPassWordState",true);
      this.$store.dispatch("setStep",_step);
      // localStorage.removeItem("registStep");
    }
   
  },

  methods: {
    init(){
      this.mobile=this.$route.query.mobile;
      if (!this.mobile) {
        return
      }else{
        this.inviteMobile=this.mobile;
      }
    },
    comfirm() {
      if (this.$store.state.regist.step == 0) {
        // 输入推荐人手机号
        var _data = {
          invite_mobile:this.inviteMobile
        };
        Loading.getInstance().open();
        request.getInstance().postData("api/auth/valid",_data).then(res=>{
          Loading.getInstance().close();
          this.goNextStep();
        }).catch(err=>{
          Loading.getInstance().close();
          Toast("推荐人手机号有误");

        });
      }else if(this.$store.state.regist.step == 1){
        var _data = {
          mobile:this.userAccountName
        };
        // 找回密码
        if(this.$store.state.regist.refindPassword){
          Loading.getInstance().open();
          _data.exist = 1;
          request.getInstance().postData("api/auth/valid",_data).then(res=>{
            Loading.getInstance().close();
            this.goNextStep();
          }).catch(err=>{
            Loading.getInstance().close();
            Toast("手机号输入有误");
            console.error(err);
          });

        }else {
          // 输入注册手机号
          Loading.getInstance().open();
          request.getInstance().postData("api/auth/valid",_data).then(res=>{
            Loading.getInstance().close();
            this.goNextStep();
          }).catch(err=>{
            Loading.getInstance().close();
            Toast("注册手机号输入有误");
            console.error(err);
          });
        }

        
      }else if(this.$store.state.regist.step == 2){
        // 验证手机号
        var _data = {
          mobile:this.userAccountName,
          code:this.validCode
        }

        if(this.$store.state.regist.refindPassword == true){
          _data.exist = 1;
        }

        Loading.getInstance().open();
        request.getInstance().postData("api/auth/valid",_data).then(res=>{
          Loading.getInstance().close();
          this.goNextStep();
        }).catch(err=>{
          console.error(err);
          Loading.getInstance().close();
          Toast("验证码输入有误");
        });
      }
    },

    goNextStep() {
      var self = this;
      var auther = this.$route.query.oauth_user;

      if (this.$store.state.regist.step >= 3) {
        if(this.$store.state.regist.refindPassword == true){          // 重置密码

          if(this.userPassword.length<8){
            Toast("密码长度最少为8位");
            return;
          }

          var _data = {
            mobile :this.userAccountName,
            password :this.userPassword,
            code:this.validCode,
          };

          Loading.getInstance().open();

          request.getInstance().postData('api/auth/password/reset',_data).then(res=>{
            Loading.getInstance().close();
            
            Toast("密码设置成功，请重新登录...");
            setTimeout(()=>{
              this.$router.push('/index');
            },1000);
          }).catch(err=>{
            Loading.getInstance().close();            
          });
          return;

        }else {                                                      // 注册逻辑

          if(this.userPassword.length<8){
            Toast("密码长度最少为8位");
            return;
          }

          var data = {
            mobile :this.userAccountName,
            password :this.userPassword,
            code:this.validCode,
            invite_mobile:this.inviteMobile,
            oauth_user:auther
          }

          Loading.getInstance().open();

          request.getInstance().postData('api/auth/register',data).then(function(res){
              sessionStorage.setItem("_token",res.data.data.token);
              Loading.getInstance().close();
              Toast("注册成功");
              self.$router.push("/login");
            }).catch((err)=>{
              Loading.getInstance().close();
              console.error(err);
              Toast("注册失败");
            });
            return;
          }

        }

      this.$store.dispatch("addStep");
    },

    showAgreement() {
      this.agrementState = true;
    },
    closeAgreenment() {
      this.agrementState = false;
    },

    sendSMS(){
      if(this.smsTimer !=null){
        return;
      }
      var _data = {
        mobile:this.userAccountName
      };

      this.smsTimer = 60;
      var timer = setInterval(()=>{
        this.smsTimer--;

        if(this.smsTimer == 0){
          this.smsTimer = null;
          clearInterval(timer);
        }
      },1000);

      Loading.getInstance().open();
      request.getInstance().postData("api/auth/sms",_data).then(res=>{
        Loading.getInstance().close();
      }).catch(err=>{
        console.error(err);
        Loading.getInstance().close();
        
      });
    }
  },
  components: { topBack }
};
</script>

  
