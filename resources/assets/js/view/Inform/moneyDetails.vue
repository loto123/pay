<template>
    <div id="billDetails">
      <topBack title="详情"></topBack>
      <div class="details-content">
        <div class="money-box">
          <span>入账金额</span>
          <em>10</em>
        </div>
        <ul class="billDetails-list">
          <li>
            <div class="title">类型</div><div class="content">收入</div>
          </li>
          <li>
            <div class="title">时间</div><div class="content">2017-11-21 16:25:36</div>
          </li>
          <li>
            <div class="title">交易单号</div><div class="content">21321321321321321</div>
          </li>
          <li>
            <div class="title">账户余钱</div><div class="content">100.79</div>
          </li>
          <li>
            <div class="title">备注</div><div class="content">交易</div>
          </li>
        </ul>
      </div>
    </div>
</template>

<script>
import topBack from "../../components/topBack.vue";
export default {
  data() {
    return {
      showAlert: false
    };
  },
  methods: {
    show() {
      this.showAlert = true;
    },
    cancel() {
      this.showAlert = false;
    }
  },
  components: {
    topBack
  }
};
</script>
 
<style lang="scss" scoped>
@import "../../../sass/oo_flex.scss";
#billDetails {
  padding-top: 2em;
  background: #eee;
  height: 100vh;
  box-sizing: border-box;
}
.details-content{
  background: #fff;
  padding-bottom:7em;
}
.money-box {
  height: 40px;
  line-height: 40px;
  border-bottom: 1px solid #ccc;
  display: flex;
  justify-content: space-between;
  padding: .5em .7em 0 .7em;
}
.billDetails-list {
  li {
    margin-top: 1em;
    display: flex;
    justify-content: space-between;
    padding: 0 0.7em;
    .title {
      color: #333;
    }
    .content {
      color: #555;
    }
  }
}
</style>


