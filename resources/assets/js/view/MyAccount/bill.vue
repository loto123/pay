<template>
    <div id="bill">
      <topBack title="账单明细"></topBack>
      <a href="javascript:;" @click="show">筛选</a>
       <ul class="bill-list">
           <li class="">
               <a href="javascript:;" v-on:click="details" class="flex">
                  <div class="bill-content">
                      <h5>交易</h5>
                      <div class="time">2017-11-23  19:44:31</div>
                  </div>
                  <div class="bill-money active">+100</div>
                </a>
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
    },
    details() {
      this.$router.push({ path: "/myAccount/bill/bill_details" });
    }
  },
  components: {
    topBack
  }
};
</script>
 
<style lang="scss" scoped>
@import "../../../sass/oo_flex.scss";
#bill {
  padding-top: 2em;
}
.bill-list {
  li {
    border-top: 1px solid #ccc;
    padding: 0 0.7em;
    a {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 55px;
      line-height: 20px;
    }
    .bill-content {
      .time {
        color: #999;
        font-size: 0.8em;
      }
    }
    .bill-money {
      font-size: 1.2em;
    }
    .active {
      color: #00cc00;
    }
    &:last-child {
      border-bottom: 1px solid #ccc;
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


