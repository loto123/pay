<template>
  <div id="login">

    <div class="top flex flex-reverse">
      注册
    </div>

    <mt-field label="用户名" placeholder="请输入用户名"></mt-field>
    <mt-field label="邮箱" placeholder="请输入邮箱" type="email"></mt-field>
    <mt-field label="密码" placeholder="请输入密码" type="password"></mt-field>
    <mt-field label="手机号" placeholder="请输入手机号" type="tel"></mt-field>
    <mt-field label="网站" placeholder="请输入网址" type="url"></mt-field>
    <mt-field label="数字" placeholder="请输入数字" type="number"></mt-field>
    <mt-field label="生日" placeholder="请输入生日" type="date"></mt-field>
    <mt-field label="自我介绍" placeholder="自我介绍" type="textarea" rows="4"></mt-field>
  </div>

</template>

<style lang="scss" scoped>
@import "../../../sass/oo_flex.scss";

.top {
  height: 2em;
  width: 100%;
  font-size: 1.2em;
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

