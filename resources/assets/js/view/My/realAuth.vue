<template>
  <div id="realAuth">
    <topBack title="实名认证"></topBack>
    <div class="realAuth-container">
      <div class="realAuth-box">
        <mt-field label="姓名" placeholder="请填写本人真实姓名" type="text" v-model="realInfo.name"></mt-field>
        <mt-field label="身份证号" placeholder="请填写本人身份证号" type="text" v-model="realInfo.id_number"></mt-field>
        <section class="account-container">
          <div class="account-box flex flex-align-center">
            <span>账号:</span>
            <em class="flex-1 number">18390939299</em>
          </div>
        </section>
        <section class="input-wrap-box">
          <div class="input-wrap flex flex-align-center">
            <span>验证码:</span>
            <input type="text" placeholder="请输入验证码" class="flex-1" v-model="realInfo.code">
            <mt-button type="default" class="flex-1" @click="sendYZM">发送验证码{{computedTime}}</mt-button>
          </div>
        </section>
      </div>
      <div class="submit-button flex flex-justify-center">
        <mt-button type="primary" size="large" @click="realAuth">确定</mt-button>
      </div>
    </div>
  </div>
</template>


<script>
  import topBack from "../../components/topBack";
  import request from '../../utils/userRequest';
  import { Toast } from "mint-ui";
  export default {
    data () {
      return {
        realInfo:{
          name :null,
          id_number :null,
          code:null
        },
        computedTime:null  //倒数计时
      }
    },
    components: { topBack },
    methods: {
      realAuth() {
        var _this=this;
        var data = this.realInfo;
        request.getInstance().postData('api/my/identify',data).then((res) => {
          Toast('认证成功');
          this.$router.push('/my'); //认证成功，回到我的页面
        }).catch((err) => {
          Toast({
            message: err.data.msg,
            duration: 800
          });
        })
      },
      //短信验证码
      sendYZM(){
        var _temp = {};
        _temp.mobile = this.$route.query.mobile;
        request.getInstance().postData("api/auth/sms",_temp).then((res) => {
          console.log(res);
          this.computedTime = 5;
          this.timer = setInterval(() => {
              this.computedTime --;
              console.log(this.computedTime); 
              if (this.computedTime == 0) {
                clearInterval(this.timer)
              }
          }, 1000)
        }).catch((err) => {
         console.log(err);
        })
      }
    }
  };
</script>

<style lang="scss" scoped>
  #realAuth {
    background: #efeef4;
    height: 100vh;
    padding-top: 2em;
  }

  .realAuth-box {
    border-bottom: 1px solid #d9d9d9;
  }

  .account-container {
    background: #fff;
    padding-left: 10px;
    .account-box {
      width: 100%;
      height: 3em;
      border-top: 1px solid #d9d9d9;
      span {
        display: inline-block;
        width: 105px;
      }
      .number {
        color: #666;
        font-size: inherit;
      }
    }
  }

  .input-wrap-box {
    background: #fff;
    padding-left: 10px;
  }

  .input-wrap {
    width: 100%;
    height: 3em;
    border-top: 1px solid #D9D9D9;
    span {
      display: inline-block;
      width: 105px;
    }
    .mint-button {
      font-size: 0.9em;
    }
    .mint-button--default {
      background: #fff;
    }
    input {
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
      box-sizing: border-box;
      width: 20%;
      font-size: inherit;
    }
  }

  .submit-button {
    width: 90%;
    margin: auto;
    margin-top: 3em;
  }
</style>