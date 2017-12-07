<template>
  <div id="shop">
      <div id="top">
          <topBack :backUrl="'\/index\/'" style="color:#fff;">
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

        <div class="shop-item flex flex-v flex-align-center" @click="goDetail('hello')">
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

          <h3>店铺1</h3>
          <p class="today-earn">今日收益:123456</p>
          <p class="all-earn">总收益:666666</p>
        </div>

        <div class="shop-item">
          
        </div>

        <div class="shop-item">
          
        </div>
      </div>

  </div>
</template>

<style lang="scss" scoped>

// #shop{
//   padding-top:1em;
// }
#top {
  height: 10em;
  width: 100%;
  background: #26a2ff;
  padding-top:2em;
  
  .top-message{
    box-sizing:border-box;
    width:100%;
    height: 100%;
    padding-right: 0.8em;
    padding-top:0.4em;
    position: relative;
    .notice{
      position: absolute;
      background:red;
      width:0.6em;
      height:0.6em;
      top:0.3em;
      right:0.6em;
      border-radius: 50%;
    }

    >i{
      font-size: 1.5em;
      color:#fff;
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
}
</style>

<script>
import topBack from "../../components/topBack";

export default {
  components: { topBack },
  methods:{
    goMyCollection(){
      this.$router.push('/shop/my_collection');
    },
    goMessagePage(){
      this.$router.push('/shop/message_list');
    },
    goDetail(e,evnet){
      this.$router.push('/shop/shop_detail');
      // console.log(e);
      // console.log(event);
    }
  }
};
</script>


