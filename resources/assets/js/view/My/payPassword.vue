<template>
  <div id="settleInfo">
    <topBack title="设置支付密码" style="background:#eee;"></topBack>
    <div class="settleInfo-container">
      <mt-field label="原密码" placeholder="请填写原支付密码" type="password" v-model="old_pay_password"></mt-field>
      <mt-field label="新密码" placeholder="请填写新支付密码" type="password" v-model="new_pay_password"></mt-field>
      <mt-field label="确认新密码" placeholder="请再次输入新支付密码" type="password" v-model="confirm_pay_password"></mt-field>
    </div>
    <div class="password-btn flex flex-justify-center">
      <mt-button type="primary" size="large" @click="affirm">确定</mt-button>
    </div>
    <div class="forget-password-box">
      <div class="notice">支付密码必须为6位纯数字</div>
      <div class="forget-password">
        <a @click="goVerfyCode">
          忘记原支付密码？
        </a>
      </div>
    </div>
  </div>
</template>


<script>
  import axios from "axios";
  import request from '../../utils/userRequest';
  import topBack from "../../components/topBack";
  import { Toast } from "mint-ui";
  export default {
    components: { topBack },
    data() {
      return {
        mobile:null,
        old_pay_password : null,   //旧密码
        new_pay_password : null,   //新密码
        confirm_pay_password: null    //确认密码
      }
    },
    methods: {
      affirm() {
        var self = this;
        var _data = {
          old_pay_password: this.old_pay_password,
          new_pay_password: this.new_pay_password,
          confirm_pay_password: this.confirm_pay_password
        }

        if(!this.old_pay_password){
          Toast('请填写原支付密码')
          return
        }else if(!this.new_pay_password){
          Toast('请填写新支付密码')
          return
        }else if(this.new_pay_password.length !=6){
          Toast("新支付密码必须为6位纯数字");
          return;
        }else if(!this.confirm_pay_password){
          Toast('请确认新支付密码')
          return
        }else if(this.new_pay_password !== this.confirm_pay_password){
          Toast('两次新密码不一致')
          return
        }

        request.getInstance().postData('api/my/updatePayPassword', _data)
          .then((res) => {
            Toast('密码修改成功');
            this.$router.push('/my/set');
          })
          .catch((err) => {
            Toast(err.data.msg);
          })
      },

      goVerfyCode(){
        this.mobile=this.$route.query.mobile
        this.$router.push("/my/verfy_code?mobile="+this.mobile);
      }
    }

  };
</script>

<style lang="scss" scoped>
  #settleInfo {
    background: #eee;
    height: 100vh;
    padding-top: 2em;
    box-sizing: border-box;
  }

  .forget-password-box {
    padding-left: 10px;
    margin-top: 0.7em;
    font-size: 1em;
    .notice {
      color: #666;
      margin-bottom: 0.5em;
    }
  }

  .forget-password {
    box-sizing: border-box;
    >a {
      color: #26a2ff;
    }
  }
  .password-btn {
    width: 96%;
    margin: auto;
    margin-top: 2em;
  }
</style>