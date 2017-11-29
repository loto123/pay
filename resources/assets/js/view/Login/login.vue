<template>
  <div id="login">

    <div class="top flex flex-reverse flex-align-center">
      注册
    </div>
    
  <div class="logo-wrap">

  </div>

    <div class="text-area flex-v flex-justify-center">
      <mt-field label="手机号" placeholder="请输入手机号" type="tel"></mt-field>
      <mt-field label="密码" placeholder="请输入密码" type="password"></mt-field>
    </div>
    
    <div class="login-button flex flex-justify-center">
      <mt-button type="primary" size="large">登录</mt-button>
    </div>
    
  </div>

</template>

<style lang="scss" scoped>
@import "../../../sass/oo_flex.scss";

.top {
  height: 2em;
  width: 100%;
  font-size: 1.2em;
  padding-right: 2em;
}

.text-area{
  margin-left: auto;
  margin-right:auto;
  margin-top:3em;
  width: 95%;

}

.login-button {
  width: 100%;
  height: 2em;
  margin-top: 5em;
}
</style>

<script>
import axios from "axios";

export default {
  name: "login",
  data() {
    return {
      name: null
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
    commitName() {
      this.$store.dispatch("changeName", this.name);
      console.log(Mint);
      // console.log(Toast);
      console.log(this);
      Mint.Toast("提示信息");
    }
  }
};
</script>

