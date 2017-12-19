<template>
  <div id="shop">
      <div id="top">
          <topBack :backUrl="'\/index\/'" style="color:#fff;background:#26a2ff;" >
              <div class="top-message flex flex-reverse" @click = "goMessagePage">
                <i class="iconfont">&#xe626;</i>
                <span class="notice">

                </span>
              </div>
          </topBack>

          <div class="imgwrap flex flex-justify-center flex-align-center">
            <i class="iconfont myShop-icon common-icon">
              &#xe61c;
            </i>
          </div>
          
          <h3 class="money-text">689523236.56元</h3>
          <h3>店铺总收益(元)</h3>

      </div>
      
      <div class="tab-menu flex flex-align-center flex-justify-center" >
        <div class="my-shop flex flex-align-center flex-justify-center active">我的店铺</div>
        <div class="my-star flex flex-align-center flex-justify-center" @click="goMyCollection">我的收藏</div>
      </div>

      <div class="shop-list flex flex-justify-around flex-wrap-on">

        <div class="shop-item flex flex-v flex-align-center" @click="goDetail(item.id)"  v-for="item in shopList" :key ="item.id">
          <div class="img-wrap flex flex-justify-around flex-wrap-on flex-align-around">
            <div class="notice"></div>
            
            <img src="/images/avatar.jpg" alt="">
            <img src="/images/avatar.jpg" alt="">
            <img src="/images/avatar.jpg" alt="">
            <img src="/images/avatar.jpg" alt="">
            <img src="/images/avatar.jpg" alt="">
            <img src="/images/avatar.jpg" alt="">
            <img src="/images/avatar.jpg" alt="">
            <img src="/images/avatar.jpg" alt="">
            <img src="/images/avatar.jpg" alt="">
            
          </div>

          <h3>{{item.name}}</h3>
          <p class="today-earn">今日收益:123456</p>
          <p class="all-earn">总收益:666666</p>
        </div>

        <div class="add-shop flex flex-v flex-align-center flex-justify-center" @click="addShop">
          <div class="flex flex-align-center flex-justify-center">
            <i class="iconfont">
              &#xe600;
            </i>
          </div>
          
          <h3>开新店</h3>

        </div>

      </div>

    <transition name="slide">

      <!-- 创建店铺拉起 -->
       <section class="add-shop-tab" v-if="addShopTabStatus" >
          <top-back :title= "'填写店铺信息'" :userAction = "'hide'"  v-on:hide="hide"></top-back>

          <div class= "item">
            <mt-field label="店铺名称" placeholder="请设置店铺名称"  v-model="openNewShop.name"></mt-field>
          </div>

          <div class= "item">
            <mt-field label="设置单价" placeholder="请输入单价(数字)" type="number" style="margin-top:0.4em;" v-model="openNewShop.rate"></mt-field>
          </div>

          <div class= "item">
            <mt-field label="设置抽水比率" placeholder="设置抽水比率(小数)" type="number" style="margin-top:0.4em;" v-model="openNewShop.percent"></mt-field>
          </div>

          <div class="item open-deal-switch flex flex-align-center">
            <label for="" class="flex-7" style="padding-left:0.8em;">是否开启交易</label>
            <span class="flex-3 flex flex-reverse" style="padding-right:1em;">
              <mt-switch v-model="openNewShop.active"></mt-switch>
            </span>
          </div>

          <div class="pay-wrap flex flex-reverse flex-align-center">
            <div class="onsale">推广价:<i style="color:red;">￥0</i></div>
            <div class="origin">开店原价:<i >￥200</i></div>
          </div>

          <div class="btn-wrap">
            <mt-button type="primary" size = "large" @click="createShop">完成</mt-button>
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

#top {
  height: 10em;
  width: 100%;
  background: #26a2ff;
  padding-top: 2em;

  .top-message {
    box-sizing: border-box;
    width: 100%;
    height: 100%;
    padding-right: 0.8em;
    padding-top: 0.4em;
    position: relative;
    .notice {
      position: absolute;
      background: red;
      width: 0.6em;
      height: 0.6em;
      top: 0.3em;
      right: 0.6em;
      border-radius: 50%;
    }

    > i {
      font-size: 1.5em;
      color: #fff;
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
  // 店铺列表
  .shop-item {
    width: 8em;
    min-height: 7em;
    border-radius: 0.4em;
    border: 1px solid #eee;
    margin-top: 1em;
    position: relative;

    .img-wrap {
      margin-top: 0.2em;
      border-radius: 0.4em;
      padding: 0.2em;
      box-sizing: border-box;
      background: #eee;
      width: 3.6em;
      height: 3.6em;
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
        width: 30%;
        height: 30%;
        display: block;
        margin-left: 1%;
        margin-top: 1%;
      }
    }

    h3 {
      font-size: 0.95em;
      padding-top: 0.1em;
      padding-bottom: 0.1em;
    }

    p {
      display: block;
      width: 100%;
      text-align: center;
      background: #eee;
      margin-top: 0.1em;
    }

    .today-earn {
      font-size: 0.9em;
    }

    .all-earn {
      font-size: 0.9em;
    }
  }

  .add-shop {
    width: 8em;
    min-height: 7em;
    margin-top: 1em;
    position: relative;

    > div {
      border-radius: 0.4em;
      width:85%;
      height: 85%;
      border: 1px dashed #eee;

      > i {
        font-size: 4em;
        color: #bbb;
      }
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
  padding-top: 1em;
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
import {Indicator} from 'mint-ui'
import request from '../../utils/userRequest'
import Loading from '../../utils/loading'

export default {
  components: { topBack },

  mounted(){
    this.getShopData();
  },

  data() {
    return {
      addShopTabStatus: false,      // 创建店铺拉起状态
      dealStatus: true,             // 是否开启交易(创建店铺tab)

      openNewShop:{
        name :null,
        rate :null,
        percent :null,
        active :true
      },

      shopList:[]
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
      console.log(e);
      this.$store.dispatch("shop_setShopDetailId",e);
      this.$router.push("/shop/shop_detail"+"?id="+e);
      console.log(event);
    },
    addShop() {
      this.addShopTabStatus = true;
    },
    hide() {
      this.addShopTabStatus = false;
    },

    // 创建店铺
    createShop(){
      var self = this;
      var data = this.openNewShop;

      request.getInstance().postData("api/shop/create",data).then(function(res){
        console.log(res);
        self.addShopTabStatus = false;
        self.getShopData();
      }).catch((err)=>{
        console.error(err);
      });
    },

    // 数据处理
    getShopData(){
      var self = this;
      Loading.getInstance().open();
      request.getInstance().getData("api/shop/lists/mine").then(function(res){
        self.shopList = res.data.data.data;
        console.log(res);
        Loading.getInstance().close();
        
      }).catch(function(e){
        Loading.getInstance().close();
        console.error(e);
      });
    }

  }
};
</script>


