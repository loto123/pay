<template>
    <div id="recharge" class="recharge-container">
        <topBack title="充值"></topBack>
        <div class="recharge-box">
            <div class="title">充值金额</div>
            <div class="recharge-money">
                <label>￥</label>
                <input type="text" placeholder="请输入金额">
            </div>
            <div class="recharge-way">
                <div class="title">选择充值方式</div>
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
            <a href="javascript:;" class="recharge-btn"> 
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
.recharge-container {
  background: #eee;
  height: 100vh;
  padding-top: 2em;
  box-sizing: border-box;
}
.recharge-box {
  background: #fff;
  padding: 1em;
  margin: 0 0.5em;
  .tltle {
    font-size: 1em;
    color: #999;
  }
}
.recharge-money {
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

.recharge-way {
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
.recharge-btn {
  display: block;
  margin-top: 3em;
  margin-bottom: 1em;
}
</style>


