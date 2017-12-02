<template>
    <div id="billDetails-container">
      <a href="javascript:;" @click="show">筛选</a>
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
      <transition  name="slide">
        <div class="sel-type" v-if="showAlert">
          <div class="sel-type-box">
            <h2>选择交易类型</h2>
            <ul class="type-list">
              <li class="active">
                <a href="javascript:;">全部</a>
              </li>
              <li>
                <a href="javascript:;">交易</a>
              </li>
              <li>
                <a href="javascript:;">店铺转账</a>
              </li>
              <li>
                <a href="javascript:;">全部</a>
              </li>
              <li>
                <a href="javascript:;">交易</a>
              </li>
            </ul>
            <div class="cancel-btn">
               <a href="javascript:;" @click="cancel">
                  <mt-button type="default" size="large">取消</mt-button>
                </a>
            </div>
          </div>
         </div>
      </transition>
    </div>
</template>

<script>
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
  }
};
</script>
 
<style lang="scss" scoped>
@import "../../../sass/oo_flex.scss";
.money-box {
  height: 40px;
  line-height: 40px;
  border-bottom: 1px solid #ccc;
  display: flex;
  justify-content: space-between;
  padding: 0 0.7em;
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
.slide-enter-active,
.slide-leave-active {
  transition: all 1s ease;
}

.slide-enter,
.slide-leave-to {
  transform: translateY(100vh);
}
.sel-type-box {
  h2 {
    height: 2.8em;
    line-height: 2.8em;
    text-align: center;
    color: #333;
    border: 1px solid #f1f1f1;
    font-weight: 700;
  }
  .type-list {
    padding: 4%;
    overflow: hidden;
    li {
      width: 32%;
      float: left;
      text-align: center;
      height: 60px;
      line-height: 60px;
    }
    a {
      display: block;
      width: 100%;
      height: 100%;
    }
    .active {
      a {
        background: #50b56a;
        color: #fff;
      }
    }
  }
}
.sel-type {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.2);
  z-index: 1000;
  transition: all 0.3s ease-in-out;
}
.sel-type-box {
  position: absolute;
  z-index: 1002;
  width: 100%;
  background: #fff;
  bottom: 0;
  border-radius: 4px;
}
.cancel-btn {
  margin-top: 1.5em;
}
</style>


