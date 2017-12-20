<template>
    <div id="give" class="give-container">
        <topBack title="转账到店铺">
            <div class="flex flex-reverse" style="width:100%;padding-right:1em;box-sizing:border-box;" @click="goIndex">
              <i class="iconfont" style="font-size:1.4em;">&#xe602;</i>
            </div>
        </topBack>
        <div class="give-box">
            <div class="title">转账金额</div>
            <div class="give-money flex flex-justify-center">
                <label>￥</label>
                <input type="text" placeholder="请输入金额">
            </div>
            <div class="all-money flex">
                <div class="money">可转账余额 ¥<span>231321</span>, </div>
                <a href="javascript:;" class="all-giveAcc">全部转账</a>
            </div>
            <div class="give-store">
                <h3>选择转账店铺</h3>
                <select name="" id="store">
                    <option value="0">默认店铺</option>
                </select>
            </div>
            <a href="javascript:;" class="transAcc-btn"> 
                <mt-button type="primary" size="large">转账</mt-button>    
            </a>
        </div>
    </div>
</template>

<script>
import topBack from "../../components/topBack.vue";
export default {
  data() {
    return {};
  },
  components: { topBack },
  methods: {
    goIndex() {
      this.$router.push("/index");
    }
  }
};
</script>

<style lang="scss" scoped>
@import "../../../sass/oo_flex.scss";
.give-container {
  background: #eee;
  height: 100vh;
  padding-top: 2em;
  box-sizing: border-box;
}
.give-box {
  background: #fff;
  padding: 1em;
  margin: 0 0.5em;
  .tltle {
    font-size: 1em;
    color: #999;
  }
}
.give-money {
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
  .money {
    color: #666;
  }
  .all-giveAcc {
    color: #199ed8;
    margin-left: 0.4em;
  }
}
.give-store {
  margin-top: 2.5em;
  h3 {
    color: #999;
    font-size: 0.9em;
  }
  #store {
    margin-top: 0.5em;
    width: 100%;
    height: 40px;
    line-height: 40px;
    border: 1px solid #ccc;
  }
}
.transAcc-btn {
  display: block;
  margin-top: 3em;
  margin-bottom: 1em;
}
</style>


