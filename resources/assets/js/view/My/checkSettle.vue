<template>
  <div id="settleInfo">
    <topBack title="结算卡信息"></topBack>
    <div class="settleInfo-container">
      <div class="account flex flex-align-center">
        <div class="account-title">账号</div>
        <div class="account-number">321321321</div>
      </div>
      <ul class="info-content-list">
        <li>
          <div class="content-box flex flex-align-center">
            <div class="title">真实姓名</div>
            <div class="content">刘立夫</div>
          </div>
          <div class="content-box flex flex-align-center">
            <div class="title">身份证号码</div>
            <div class="content">430********21325132121</div>
          </div>
          <div class="content-box flex flex-align-center">
            <div class="title">银行卡号(储蓄卡)</div>
            <div class="content">6542*******3101321132</div>
          </div>
          <div class="content-box flex flex-align-center">
            <div class="title">所属银行</div>
            <div class="content">中国工商银行</div>
          </div>
        </li>
      </ul>
      <a href="javascript:;" class="btn" @click = "showPassword">
        <mt-button type="primary" size="large" >更换结算卡</mt-button>
      </a>
    </div>

    <passWorld :setSwitch="showPasswordTag" v-on:hidePassword = "hidePassword"></passWorld>
  </div>
</template>

<script>
import topBack from "../../components/topBack";
import passWorld from "../../components/password"

export default {
  components: { topBack , passWorld},
  data(){
    return {
      showPasswordTag:false       // 密码弹出开关
    }
  },
  methods:{
    showPassword(){
      this.showPasswordTag = true;
    },

    hidePassword(){
      this.showPasswordTag = false;
    }

  }
};
</script>

<style lang="scss" scoped>
#settleInfo {
  background: #eee;
  height: 100vh;
  padding-top: 2em;
  box-sizing:border-box;
}
.account {
  height: 3em;
  line-height: 3em;
  background: #fff;
  padding-left: 1em;
  .account-title {
    width: 8em;
    margin-right: 1em;
    color: #999;
    font-size: 0.8em;
  }
  .account-number {
    font-size: 0.8em;
  }
}

.info-content-list {
  margin-bottom: 3em;
  li {
    background: #fff;
    padding: 0 1em;
    border: 1px solid #d9d9d9;
    .content-box {
      height: 3em;
      line-height: 3em;
      border-bottom: 1px solid #d9d9d9;
      &:nth-child(2) {
        border-bottom: 1px solid #aaa;
      }
      &:last-child {
        border-bottom: none;
      }
      .title {
        width: 8em;
        margin-right: 1em;
        color: #999;
        font-size: 0.8em;
      }
      .content {
        font-size: 0.8em;
      }
    }
  }
}
</style>
