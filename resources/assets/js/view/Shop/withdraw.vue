<template>
    <div id="withdraw" class="withdraw-container">
        <topBack title="提现到个人账户">
            <div class="flex flex-reverse" style="width:100%;padding-right:1em;box-sizing:border-box;" @click="goIndex">
              <i class="iconfont" style="font-size:1.4em;">&#xe602;</i>
            </div>
        </topBack>
        <div class="withdraw-box">
            <div class="title">提现金额</div>
            <div class="withdraw-money flex flex-justify-center">
                <label>￥</label>
                <input type="text" placeholder="请输入金额">
            </div>
            <div class="all-money flex">
                <div class="money">店铺可提现余额 ¥<span>231321</span>, </div>
                <a href="javascript:;" class="all-withdraw">全部提现</a>
            </div>
            <a href="javascript:;" class="withdraw-btn"> 
                <mt-button type="primary" size="large">提现</mt-button>    
            </a>
        </div>
    </div>
</template>

<script>
import topBack from "../../components/topBack.vue";
export default {
  data() {
    return {}
  },
  components: { topBack },
  methods: {
    goIndex(){
      this.$router.push('/index');
    }
  }
};
</script>

<style lang="scss" scoped>
@import "../../../sass/oo_flex.scss";
.withdraw-container {
  background: #eee;
  height: 100vh;
  padding-top: 2em;
  box-sizing: border-box;
}
.withdraw-box {
  background: #fff;
  padding: 1em;
  margin: 0 0.5em;
  .tltle {
    font-size: 1em;
    color: #999;
  }
}
.withdraw-money {
  border-bottom: 1px solid #ccc;
  vertical-align: middle;
  margin-top: 2em;
  font-size:1.2em;
  padding: 0.2em 0;
  input {
    border: none;
    outline: none;
    width: 100%;
    font-size:0.9em;
  }
}
.all-money {
  margin-top: 1em;
  font-size: 1em;
  .money {
    color: #666;
  }
  .all-withdraw {
    color: #199ed8;
    margin-left: 0.4em;
  }
}
.withdraw-btn {
  display: block;
  margin-top: 3em;
  margin-bottom: 1em;
}
</style>


