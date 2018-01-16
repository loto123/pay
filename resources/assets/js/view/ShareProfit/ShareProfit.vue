<template>
  <div id = "share-profit">
    <div class="top">
            <topBack :title="'我的分润'" style="background: #26a2ff; color:#fff;">
              <div class="flex flex-reverse" style="width:100%;padding-right:1em;box-sizing:border-box;"
                @click="goRecord"
              >
                提转记录
              </div>
            </topBack>
      
      <div class="profit-wrap flex flex-align-center">
        <div class="left flex flex-v flex-align-center flex-justify-around">
          <div class="money">{{yesterday_profit}}</div>
          <div class="title">昨日收益(元)</div>
        </div>
        <div class="right flex flex-v flex-align-center flex-justify-around">
          <div class="money">{{today_profit}}</div>
          <div class="title">今日收益(元)</div>
        </div>
      </div> 
    </div>  

        <div class="content flex flex-v flex-align-center">
            <div class="rest-money flex flex-v flex-align-center flex-justify-start">
                <div class="money-text">
                    {{balance}}
                </div>
                <h3>当前可提现分润</h3>
                <h4>单位(元)</h4>
            </div>

            <div class="withdraw-cash" @click="goWithdraw">
              <mt-button type="primary" size="large" style="background:#06d29d;">提现到个人账户</mt-button>
            </div>
            <div class="transfer-accounts" >
              <!-- <mt-button type="primary" size="large" >转账给店铺会员</mt-button> -->
            </div>

            <div class="foot flex flex-v flex-align-center">
              <div class="text">
                  {{total_profit}}
              </div>
              <div class="title">
                共累计收益（元）
              </div>
            </div>
        </div>
  </div>  
</template>
<style lang="scss" scoped>

#share-profit {
  padding-top: 2em;
  height: 39.7em;
  background: #ecf6ff;

  .top {
    height: 8em;
    background: #26a2ff;

    .profit-wrap {
      height: 4em;
      width: 90%;
      margin: 0 auto;
      padding-top: 2em;
      .left {
        height: 4em;
        box-sizing: border-box;
        border-right: 1px solid #fff;
        width: 50%;

        > div {
          color: #fff;
        }

        .money {
          font-size: 1.4em;
        }
      }

      .right {
        height: 4em;
        width: 50%;

        > div {
          color: #fff;
        }

        .money {
          font-size: 1.4em;
        }
      }
    }
  }
  .content {
    width: 95%;
    height: 32em;
    background: #fff;
    margin: 0 auto;
    margin-top: -1em;

    .rest-money {
      width: 12em;
      height: 11em;
      background: url("/images/circle.png") no-repeat center center;
      background-size: cover;
      margin-top: 2em;

      .money-text {
        color: #26a2ff;
        font-size: 2.2em;
        margin-top: 2.2em;
      }

      h3 {
        font-size: 1em;
        margin-top: 1em;
        color: #999;
      }

      h4 {
        font-size: 0.9em;
        margin-top: 1.5em;
        color: #999;
      }
    }

    .withdraw-cash {
      margin-top: 4em;
      width: 70%;
    }

    .transfer-accounts {
      margin-top: 1.5em;
      width: 70%;
    }
  }

  .foot {
    margin-top: 2.5em;
    background: url("/images/blue_bg.png") no-repeat center center;
    width: 100%;
    height: 6em;

    .text {
      color: #fff;
      font-size: 1.5em;
      margin-top: 1.2em;
    }
    .title {
      color: #fff;
      margin-top: 0.4em;
    }
  }
}
</style>

<script>
import topBack from "../../components/topBack";
import Loading from "../../utils/loading";
import request from "../../utils/userRequest";
import {Toast} from "mint-ui"

export default{
  components:{ topBack},
  created(){
    this.init();
  },
  data(){
    return{
      balance:null,
      shopId :null,
      today_profit:null,
      yesterday_profit:null,
      total_profit:null
    }
  },
  methods:{
    goWithdraw(){
      this.$router.push('/profit_record/withdraw/');
      console.log("Go with draw");
      // this.$router.push();
    },
   
    init(){
      
      Loading.getInstance().open();
      request.getInstance().getData("api/profit/index").then(res=>{
        this.balance = res.data.data.profit;
        this.today_profit = res.data.data.today;
        this.yesterday_profit = res.data.data.yesterday;
        this.total_profit = res.data.data.total;
        Loading.getInstance().close();
      }).catch(err=>{
        Toast(err.data.msg);
        Loading.getInstance().close();
      });
    },
    record(){
      
    },
    goRecord(){
      this.$router.push("/profit_record");
    }
  }
}
</script>
