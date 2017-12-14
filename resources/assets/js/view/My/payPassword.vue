<template>
  <div id="settleInfo">
    <topBack title="修改支付密码"></topBack>
    <div class="settleInfo-container">
      <mt-field label="原密码" placeholder="请填写原支付密码" v-model="oldPassword"></mt-field>
      <mt-field label="新密码" placeholder="请填写新支付密码" type="email" v-model="newPassword"></mt-field>
      <mt-field label="确认新密码" placeholder="请再次输入新支付密码" type="password" v-model="affirmPassword"></mt-field>
    </div>
    <div class="forget-password-box">
      <div class="notice">支付密码必须为6位纯数字</div>
      <div class="forget-password">
        <a href="javascript:;" @click="forgetPassWord">
          忘记原支付密码？
        </a>
      </div>
    </div>
  </div>
</template>


<script>
import topBack from "../../components/topBack";

export default {
  components: { topBack }
};
</script>

<style lang="scss" scoped>
#settleInfo {
  background: #efeef4;
  height: 100vh;
  padding-top: 2em;
}
.forget-password-box{
  padding-left:10px;
  margin-top: 0.7em;
  font-size: 1em;
  .notice{
    color:#666;
    margin-bottom: 0.5em;
  }
}
.forget-password{
  box-sizing: border-box;
  >a{
    color:#26a2ff;
  }
}
</style>
