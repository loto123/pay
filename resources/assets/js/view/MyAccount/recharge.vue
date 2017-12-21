<template>
  <div id="recharge" class="recharge-container">
    <topBack title="充值">
      <div class="flex flex-reverse" style="width:100%;padding-right:1em;box-sizing:border-box;" @click="goIndex">
        <i class="iconfont" style="font-size:1.4em;">&#xe602;</i>
      </div>
    </topBack>
    <div class="recharge-box">
      <div class="title">充值金额</div>
      <div class="recharge-money flex flex-justify-center">
        <label>￥</label>
        <input type="text" placeholder="请输入金额">
      </div>
      <div class="recharge-way">
        <div class="title">选择充值方式</div>
        <div class="list-wrap">
          <mt-radio align="right" title="" v-model="choiseValue" :options="['选项A', '选项B', '选项C']">
          </mt-radio>
        </div>
      </div>
      <a href="javascript:;" class="recharge-btn" @click="recharge">
        <mt-button type="primary" size="large">充值</mt-button>
      </a>
    </div>
  </div>
</template>

<script>
  import axios from "axios";
  import request from '../../utils/userRequest';
  import topBack from "../../components/topBack.vue";

  export default {
    data() {
      return {
        money: null,
        rechargeWay: null,
        "choiseValue": null
      };
    },
    components: { topBack },
    props:["showSwitch","optionsList"],
    methods: {
      hideTab() {
        this.$emit("hideDropList", this.choiseValue);
      },
      goIndex() {
        this.$router.push("/index");
      },
      recharge() {
        var self = this;
        var temp = {};
        temp.money = money;
        temp.rechargeWay = rechargeWay;
        request.getInstance.postData('api/account/charge', temp)
          .then((res) => {
            console.log(res);
          })
          .catch((err) => {
            console.err(err);
          })
      },
      watch: {
        "choiseValue": 'hideTab'
      }
    }
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
    border-bottom: 1px solid #ccc;
    vertical-align: middle;
    margin-top: 2em;
    font-size: 1.2em;
    padding: 0.2em 0;
    input {
      border: none;
      outline: none;
      width: 100%;
      font-size: 0.9em;
    }
  }

  .recharge-btn {
    display: block;
    margin-top: 3em;
    margin-bottom: 1em;
  }
</style>