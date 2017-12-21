<template>
  <div id="realAuth">
    <topBack title="修改支付密码"></topBack>
    <div class="realAuth-container">
      <div class="realAuth-box">
        <section class="account-container">
          <div class="account-box flex flex-align-center">
            <span>手机号:</span>
            <em class="flex-1 number">18390939299</em>
          </div>
        </section>
        <section class="input-wrap-box">
          <div class="input-wrap flex flex-align-center">
            <span>验证码:</span>
            <input type="text" placeholder="请输入验证码" class="flex-1">
            <mt-button type="default" class="flex-1" @click="sendYZM">发送验证码{{computedTime}}</mt-button>
          </div>
        </section>
      </div>
      <div class="submit-button flex flex-justify-center" @click="nextBtn">
        <mt-button type="primary" size="large">下一步</mt-button>
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
          id_number :null
        },
        computedTime:0  //倒数计时
      }
    },
    components: { topBack },
    methods: {
      nextBtn() {
        this.$router.push('/my/pay_password');
      },
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
    .account-box {
      height: 3em;
      border-top: 1px solid #d9d9d9;
      padding-left: 10px;
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