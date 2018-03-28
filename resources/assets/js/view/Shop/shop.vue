<template>
  <div id="shop">
      <div id="top">
          <topBack :backUrl="'\/index\/'" style="color:#fff;background:#26a2ff;" >
              <div class="top-message flex flex-reverse" @click = "goMessagePage">
                <div class="news">
                  <img src="/images/news.png">
                </div>
                <span class="notice" v-if="messageCount">
                  {{this.messageCount>99?"99":this.messageCount}}
                </span>
              </div>
          </topBack>

          <div class="imgwrap flex flex-justify-center flex-align-center">
            <i class="iconfont myShop-icon common-icon">
              &#xe61c;
            </i>
          </div>
          
          <h3 class="money-text">{{total_profit}}<i class="diamond" style="margin-left: 0.5em;">&#xe6f9;</i></h3>
          <h3>公会总收益</h3>

      </div>
      
      <div class="tab-menu flex flex-align-center flex-justify-center" >
        <div class="my-shop flex flex-align-center flex-justify-center active">我创建的公会</div>
        <div class="my-star flex flex-align-center flex-justify-center" @click="goMyCollection">我加入的公会</div>
      </div>

      <div class="shop-list flex flex-justify-around flex-wrap-on">

        <div class="shop-item flex flex-align-center" @click="goDetail(item.id)"  v-for="item in shopList" :key ="item.id">

          <div class="img-wrap flex flex-justify-around flex-wrap-on flex-align-around ">
            <!-- <div class="notice"></div> -->
            <img :src="item.logo" alt="">
          </div>

          <div class="center-box flex-5">
            <h3>{{SetString(item.name,6)}}</h3>
            <p class="today-earn">今日收益:{{item.today_profit}} <i class="diamond">&#xe6f9;</i></p>
            <p class="all-earn">总收益:{{item.total_profit}}<i class="diamond">&#xe6f9;</i></p>
          </div>

          <div class="flex-1">
            <i class="iconfont">
              &#xe62e;
            </i>
          </div>
         
        </div>

        <div class="add-shop flex flex-v flex-align-center flex-justify-center" @click="addShop">
          <i class="iconfont ">
            &#xe600;
          </i>

          <h3>创建新公会</h3>

        </div>

      </div>

    <transition name="slide">

      <!-- 创建公会拉起 -->
       <section class="add-shop-tab" v-if="addShopTabStatus" >
          <top-back :title= "'填写公会信息'" :userAction = "'hide'"  v-on:hide="hide"></top-back>

          <div class= "item">
            <mt-field label="公会名称" placeholder="请设置公会名称"  v-model="openNewShop.name"></mt-field>
          </div>

          <div class= "item">
            <mt-field label="任务默认倍率" placeholder="请输入任务默认倍率(数字)" type="number" style="margin-top:0.4em;" v-model="openNewShop.rate"></mt-field>
          </div>

          <div class= "item">
            <mt-field label="设置公会佣金费率" :placeholder="'公会佣金费率不得超过' + guild_commission +'%'" type="number" style="margin-top:0.4em;" v-model="openNewShop.percent"></mt-field>
          </div>

          <div class="item open-deal-switch flex flex-align-center">
            <label for="" class="flex-7" style="padding-left:0.8em;">是否开启任务</label>
            <span class="flex-3 flex flex-reverse" style="padding-right:1em;">
              <mt-switch v-model="openNewShop.active"></mt-switch>
            </span>
          </div>

          <div class="pay-wrap flex flex-reverse flex-align-center">
            <div class="onsale">推广价:<i style="color:red;">￥0</i></div>
            <div class="origin">开店原价:<i >￥200</i></div>
          </div>

          <div class="btn-wrap">
            <mt-button type="primary" size = "large" @click="createShop" v-bind:disabled="!createShopSwitch">完成</mt-button>
          </div>
        </section>
    </transition>
  </div>
</template>

<style lang="scss" scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.5s ease;
}

.slide-enter,
.slide-leave-to {
  transform: translateY(100vh);
}

.diamond{
  font-size: 1em;
  margin-left: 0.4em;
}

#shop{
  min-height:100vh;
  background: #eee;
}

#top {
  height: 10em;
  width: 100%;
  background: #26a2ff;
  padding-top: 2em;

  .top-message {
    box-sizing: border-box;
    width: 100%;
    height: 100%;
    padding-right: 1em;
    padding-top:1em;
    
    .news{
      position: relative;
      width: 32px;
      img{
        display: block;
        width: 100%;
      }
    }
    .notice {
      position: absolute;
      background: red;
      width: 1.5em;
      height: 1.5em;
      right: 0.7em;
      top: 0.3em;
      border-radius: 50%;
      text-align: center;
      line-height: 1.5em;
      font-size: 0.1em;
    }
  }

  .imgwrap {
    width: 5em;
    height: 5em;
    background: #fff;
    border-radius: 50%;
    margin: 0 auto;
    i {
      display: block;
    }
    .myShop-icon {
      font-size: 3.5em;
      color: #26a2ff;
    }
  }

  h3 {
    margin-top: 0.4em;
    color: #fff;
    text-align: center;
    font-size: 0.9em;
  }

  .money-text {
    font-size: 1.3em;
  }
}

.tab-menu {
  width: 100%;
  height: 3em;
  background: #fff;

  > div {
    width: 50%;
    height: 100%;
    box-sizing: border-box;
  }

  .active {
    border-bottom: 0.2em solid #26a2ff;
    color: #26a2ff;
  }
}

.shop-list {

  padding-bottom:0.5em;

  // 公会列表
  .shop-item {
    width: 98%;
    min-height: 7em;
    border-radius: 0.2em;
    border: 1px solid #eee;
    background: #fff;
    margin-top: 0.2em;
    position: relative;
    box-sizing: border-box;
    padding-left: 0.5em;
    padding-right: 0.5em;

    .img-wrap {
      margin-top: 0.2em;
      border-radius: 0.4em;
      padding: 0.2em;
      box-sizing: border-box;
      background: #eee;
      width: 5.5em;
      height: 5.5em;
      position: relative;

      .notice {
        position: absolute;
        width: 0.9em;
        height: 0.9em;
        background: red;
        border-radius: 50%;
        right: -0.2em;
        top: -0.2em;
      }

      > img {
        width: 100%;
        height: 100%;
        display: block;
        margin-left: 1%;
        margin-top: 1%;
      }
    }

    .center-box{
      box-sizing: border-box;
      padding-left: 0.5em;
      padding-right: 0.5em;

      h3 {
        font-size: 1em;
        padding-top: 0.1em;
        padding-bottom: 0.1em;
        color:#555;
      }

      p {
        display: block;
        width: 100%;
        margin-top: 0.3em;
        color:#555;
      }

      .today-earn {
        font-size: 1em;
      }

      .all-earn {
        @extend .today-earn;
      }
    }
    
  }

  .add-shop {
    width: 95%;
    height: 5.5em;
    border-radius: 0.5em;
    margin-top: 0.4em;
    position: relative;
    background:#fff;
    border: 0.1em dashed #ddd;

    >i{
      font-size: 2.5em;
      color: #bbb;
      display: block;
      width: 1em;
      height: 1em;
    }

    h3{
      padding-top:0.3em;
      color:#999;
    }
  }
}

.add-shop-tab {
  width: 100%;
  height: 100vh;
  background: #eee;
  padding-bottom: 2em;
  position: fixed;
  top: 0em;
  left: 0em;
  z-index: 1001;
  padding-top: 2em;
  box-sizing: border-box;

  h3 {
    font-size: 1.8em;
    text-align: center;
    padding-top: 0.5em;
    padding-bottom: 0.5em;
  }

  p {
    line-height: 1.5;
    box-sizing: border-box;
    padding-left: 1em;
    padding-right: 1em;
    text-indent: 2em;
  }

  .item {
    width: 95%;
    margin: 0 auto;
  }

  .open-deal-switch {
    margin-top: 0.4em;
    height: 2.9em;
    background: #fff;
    border-top: 1px solid #ddd;
    border-bottom: 1px solid #ddd;
    box-sizing: border-box;
  }
  .pay-wrap {
    margin-top: 0.4em;
    height: 3em;
    width: 100%;
    box-sizing: border-box;
    padding-right: 1em;

    .onsale {
      // margin-right:2em;
    }

    .origin {
      margin-right: 1em;
    }
  }

  .btn-wrap {
    margin: 0 auto;
    margin-top: 2em;
    width: 95%;
  }
}
</style>

<script>
import topBack from "../../components/topBack"
import {Indicator,Toast} from 'mint-ui'
import request from '../../utils/userRequest'
import Loading from '../../utils/loading'
import utils from '../../utils/utils.js'

export default {
  components: { topBack },

  mounted(){
    this.getShopData();
  },

  data() {
    return {
      addShopTabStatus: false,       // 创建公会拉起状态
      dealStatus: true,              // 是否开启任务(创建公会tab)
      createShopSwitch: true,        // 防看止按钮多次点击的

      openNewShop:{
        name :null,
        rate :null,
        percent :null,
        active :true
      },

      shopList:[],
      total_profit:null,
      messageCount:null,             // 新消息数量
      guild_commission : 0           // 公会佣金费率上限

    };
  },
  methods: {

    // 路由跳转
    goMyCollection() {
      this.$router.push("/shop/my_collection");
    },
    goMessagePage() {
      this.$router.push("/shop/message_list");
    },
    goDetail(e, evnet) {
      this.$store.dispatch("shop_setShopDetailId",e);
      this.$router.push("/shop/shop_detail"+"?id="+e);
    },
    addShop() {
      this.addShopTabStatus = true;
    },
    hide() {
      this.addShopTabStatus = false;

      this.openNewShop = {
        name :null,
        rate :null,
        percent :null,
        active :true
      }

    },

    // 创建公会
    createShop(){
      
      var data = this.openNewShop;

      if((parseFloat(data.rate)*10).toString().indexOf(".")!=-1 || parseFloat(data.rate) < 0){
        Toast("请输入正确的任务默认倍率(允许有一位小数)");
        return;
      }

      if(data.rate > 99999){
        Toast("任务默认倍率不能超过99999");
        return;
      }

      this.createShopSwitch = false;

      Loading.getInstance().open();
      
      request.getInstance().postData("api/shop/create",data).then((res)=>{
        this.addShopTabStatus = false;
        this.getShopData();
        this.createShopSwitch = true;

          Loading.getInstance().close();
        
      }).catch((err)=>{
        Loading.getInstance().close();
        Toast(err.data.msg);
        this.createShopSwitch = true;
      });
    },

    // 数据处理
    getShopData(){

      Loading.getInstance().open();

      Promise.all([
          request.getInstance().getData("api/shop/lists/mine"),
          request.getInstance().getData("api/shop/profit"),
          request.getInstance().getData("api/shop/messages/count"),
          request.getInstance().getData("api/shop/settings")
          ])
        .then(res=>{
          this.shopList = res[0].data.data.data;
          this.total_profit = res[1].data.data.profit;
          this.messageCount = res[2].data.data.count;

          this.guild_commission = res[3].data.data.guild_commission;
          Loading.getInstance().close();
        })
        .catch(err=>{
          Loading.getInstance().close();
          Toast(err.data.msg);
          console.error(err);
        });
    },

    SetString(str,len){
      return utils.SetString(str,len);
    }

  }
};
</script>


