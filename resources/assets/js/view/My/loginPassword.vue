<template>
  <div id="settleInfo">
    <topBack title="修改登录密码"></topBack>
    <div class="settleInfo-container">
      <mt-field label="原密码" placeholder="请填写原密码" v-model="old_password"></mt-field>
      <mt-field label="新密码" placeholder="请填写新密码" type="email" v-model="new_password"></mt-field>
      <mt-field label="确认新密码" placeholder="请再次输入密码" type="password" v-model="confirm_password"></mt-field>
    </div>
    <div class="password-btn flex flex-justify-center">
      <mt-button type="primary" size="large" @click="affirm">确认</mt-button>
    </div>
    <div class="forget-password-box">
      <div class="notice">密码长度必须在6-16个字符之间个字符</div>
      <div class="forget-password">
        <a href="javascript:;">
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
      affirm() {
        var self=this;
        var data = {
          old_password: this.old_password,
          new_password:this.new_password,
          confirm_password:this.confirm_password
        }  
        request.getInstance().postData('api/my/updatePassword',data)
          .then((res) => {
            console.log(res);
            Toast('密码修改成功');
            this.$router.push('/login');  //调转到登录页
          })
          .catch((err) => {
            console.log(err);
          })
      }
    }
  };
</script>

<style lang="scss" scoped>
  #settleInfo {
    background: #efeef4;
    height: 100vh;
    padding-top: 2em;
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