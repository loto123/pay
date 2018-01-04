<template>
  <div id="settleInfo">
    <topBack title="修改登录密码" style="background:#eee;"></topBack>
    <div class="settleInfo-container">
      <mt-field label="原密码" placeholder="请填写原密码" type="password" v-model="old_password"></mt-field>
      <mt-field label="新密码" placeholder="请填写新密码" type="password" v-model="new_password"></mt-field>
      <mt-field label="确认新密码" placeholder="请确认新密码" type="password" v-model="confirm_password"></mt-field>
    </div>
    <div class="password-btn flex flex-justify-center">
      <mt-button type="primary" size="large" @click="affirm">确认</mt-button>
    </div>
    <div class="forget-password-box">
      <div class="notice">密码长度必须在6-16个字符之间个字符</div>
      <div class="forget-password">
        <a href="javascript:;" @click="forgetPassWord">
          忘记原密码?
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
    data () {
      return {
        old_password:null,   //旧密码
        new_password:null,   //新密码
        confirm_password:null    //确认密码
      }
    },

    methods: {
      forgetPassWord(){
        this.$store.dispatch("setStep",1);
        this.$store.dispatch("setRefindPassWordState",true);
        localStorage.setItem("registStep",1);
        this.$router.push("/login/regist");
      },

      affirm() {
        var self=this;
        var data = {
          old_password: this.old_password,
          new_password:this.new_password,
          confirm_password:this.confirm_password
        }  
        if(!this.old_password){
          Toast('请填写原密码')
          return
        }else if(!this.new_password){
          Toast('请填写新密码')
          return
        }else if(!this.confirm_password){
          Toast('请确认新密码')
          return
        }else if(this.new_password !== this.confirm_password){
          Toast('两次输入的密码不一致')
          return
        }else if(this.old_password == this.new_password){
          Toast('新密码不能跟原密码一样')
          return
        }

        request.getInstance().postData('api/my/updatePassword',data)
          .then((res) => {
            Toast('密码修改成功');
            this.$router.push('/login');  //调转到登录页
          })
          .catch((err) => {
            console.error(err);
          })
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

  .password-btn {
    width: 96%;
    margin: auto;
    margin-top: 2em;
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
</style>