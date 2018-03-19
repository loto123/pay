<template>
  <div id="my-collection">
      <div id="top">
          <topBack :backUrl="'\/index\/'" style="color:#fff;background:#26a2ff;">
              
          </topBack>

          <div class="imgwrap flex flex-justify-center flex-align-center">
            <i class="iconfont myShop-icon common-icon">
              &#xe61c;
            </i>
          </div>
          <h3>公会</h3>
      </div>

      <div class="tab-menu flex flex-align-center flex-justify-center" >
        <div class="my-shop flex flex-align-center flex-justify-center " @click="goMyShop">我创建的公会</div>
        <div class="my-star flex flex-align-center flex-justify-center active" >我加入的公会</div>
      </div>

      <div class="shop-list flex flex-justify-around flex-wrap-on">

        <div class="list-wrap flex flex-align-center" v-for="item in shopList" :key = "item.id" @click="goDetail(item.id)">

          <div class="shop-item flex flex-justify-around flex-align-around">
            <img :src="item.logo"  alt="">
          </div>

          <h3 class="flex-6">{{SetString(item.name,10)}}</h3>

          <i class="iconfont flex-1">
            &#xe62e;
          </i>
        </div>
        
      </div>
  </div>
</template>

<style lang="scss" scoped>

#my-collection{
  min-height: 100vh;
  background: #eee;
}

#top {
  height: 10em;
  padding-top:2em;
  width: 100%;
  background: #26a2ff;

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
    margin-top: 1em;
    color: #fff;
    text-align: center;
    font-size: 1.2em;
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

  .list-wrap {
    width:98%;
    background: #fff;
    border-radius: 0.4em;
    height: 5em;
    margin-top:0.4em;

    .shop-item {
      width: 4em;
      height: 4em;
      margin-left: 1em;
      margin-right: 1em;
      background: #eee;
      border-radius: 0.4em;
      box-sizing: border-box;
      padding: 0.2em;
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
  }
  h3 {
    font-size:1em;
    padding-top:0.1em;
    padding-bottom: 0.1em;
  }
}
</style>


<script>
import topBack from "../../components/topBack"
import request from "../../utils/userRequest"
import Loading from "../../utils/loading"
import utils from "../../utils/utils"

export default {
  data(){
    return {
      shopList:[]
    }
  },

  created(){
    this.init();
  },

  methods: {
    goMyShop() {
      this.$router.push("/shop/");
    },

    goDetail(id){
      this.$router.push("/shop/shop_detail?"+"id="+id);
    },

    init(){
      Loading.getInstance().open();
      request.getInstance().getData("api/shop/lists").then(res=>{
        Loading.getInstance().close();
        this.shopList = res.data.data.data;
      }).catch(err=>{
        console.error(err);
        Loading.getInstance().close();
        
      });
    },

    SetString(str,len){
      return utils.SetString(str,len);
    }
    
  },
  components: { topBack }
};
</script>

