<template>
    <div id="withdraw" class="withdraw-container">
        <topBack title="提现"></topBack>
        <div class="withdraw-box">
            <div class="title">提现金额</div>
            <div class="withdraw-money">
                <label>￥</label>
                <input type="text" placeholder="请输入金额">
            </div>
            <div class="all-money flex">
                <div class="money">可提现余额 ¥<span>231321</span>, </div>
                <a href="javascript:;" class="all-withdraw">全部提现</a>
            </div>
            <div class="withdraw-way">
                <div class="title">提现到</div>
                <a class="list">
                    <div class="list-wrapper">
                        <div class="list-title">
                            <label class="radiolist-label">
                                <span class="mint-radio is-right">
                                    <input type="radio" class="radio-input" checked name="tool" value="银行卡">
                                    <span class="radio-core"></span>
                                </span>
                                <span class="radio-label">银行卡</span>
                            </label>
                        </div>
                    </div>
                </a>
                <a class="list">
                    <div class="list-wrapper">
                        <div class="list-title">
                            <label class="radiolist-label">
                                <span class="mint-radio is-right">
                                    <input type="radio" class="radio-input" name="tool" value="微信">
                                    <span class="radio-core"></span>
                                </span>
                                <span class="radio-label">微信</span>
                            </label>
                        </div>
                    </div>
                </a>
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
  components: { topBack }
};
</script>

<style lang="scss" scoped>
@import "../../../sass/oo_flex.scss";
.withdraw-container {
  background: #efeef4;
  height: 100vh;
  padding-top: 2em;
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
  height: 40px;
  line-height: 40px;
  border-bottom: 1px solid #ccc;
  vertical-align: middle;
  margin-top: 1.2em;
  input {
    border: none;
    padding: 7px 0;
    display: inline-block;
    width: 70%;
  }
}
.all-money {
  margin-top: 1em;
  font-size: 0.8em;
  .money {
    color: #666;
  }
  .all-withdraw {
    color: #199ed8;
    margin-left: 0.4em;
  }
}
.withdraw-way {
  margin-top: 2em;
  .title {
    color: #666;
    margin-bottom: 0.5em;
  }
}

.list {
  background-color: #fff;
  box-sizing: border-box;
  color: inherit;
  min-height: 48px;
  display: block;
  border-bottom: 1px solid #d9d9d9;
}
.list-title {
  width: 100%;
}
.list-wrapper {
  display: flex;
  align-items: center;
  box-sizing: border-box;
  min-height: inherit;
  padding: 0 10px;
}

.radio-core {
  box-sizing: border-box;
  display: inline-block;
  background-color: #fff;
  border-radius: 100%;
  border: 1px solid #ccc;
  position: relative;
  width: 20px;
  height: 20px;
  vertical-align: middle;
  &::after {
    content: " ";
    border-radius: 100%;
    top: 5px;
    left: 5px;
    position: absolute;
    width: 8px;
    height: 8px;
  }
}
.radiolist-label {
  display: block;
  width: 100%;
}
.radio-input {
  display: none;
}
.radio-label {
  vertical-align: middle;
}
.radio-input:checked + .radio-core {
  background-color: #26a2ff;
  border-color: #26a2ff;
  &::after {
    background-color: #fff;
  }
}
.withdraw-btn {
  display: block;
  margin-top: 3em;
  margin-bottom: 1em;
}
</style>


