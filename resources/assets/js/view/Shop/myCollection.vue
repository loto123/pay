<template>
  <div id="my-collection">
      <div id="top">
          <topBack :backUrl="'\/index\/'">
              
          </topBack>

          <div class="imgwrap flex flex-justify-center flex-align-center">
            <i class="iconfont myShop-icon common-icon">
              &#xe61c;
            </i>
          </div>
          <h3>店铺</h3>
      </div>

      <div class="tab-menu flex flex-align-center flex-justify-center" >
        <div class="my-shop flex flex-align-center flex-justify-center " @click="goMyShop">我的店铺</div>
        <div class="my-star flex flex-align-center flex-justify-center active" >我的收藏</div>
      </div>

      <div class="shop-list flex flex-justify-around flex-wrap-on">
        <div class="list-wrap">
          <div class="shop-item flex flex-justify-around flex-wrap-on flex-align-around">
            <div class="notice"></div>
            <img src="/images/avatar.jpg" alt="">
            <!-- <img src="/images/avatar.jpg" alt="">
            <img src="/images/avatar.jpg" alt="">
            <img src="/images/avatar.jpg" alt="">
            <img src="/images/avatar.jpg" alt="">
            <img src="/images/avatar.jpg" alt="">
            <img src="/images/avatar.jpg" alt="">
            <img src="/images/avatar.jpg" alt="">
            <img src="/images/avatar.jpg" alt=""> -->
          </div>

        <h3>店铺111</h3>
      </div>

        
        <div class="shop-item">

        </div>
        <div class="shop-item">

        </div>
        <div class="shop-item">

        </div>
        <div class="shop-item">

        </div>
        <div class="shop-item">

        </div>
      </div>
  </div>
</template>

<style lang="scss" scoped>
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
  padding-top: 0.7em;
  .list-wrap {
    .shop-item {
      width: 4em;
      height: 4em;
      margin-left: 1em;
      margin-right: 1em;
      margin-top: 0.5em;
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
        width: 30%;
        height: 30%;
        display: block;
        margin-left: 1%;
        margin-top: 1%;
      }
    }
  }
  h3 {
    font-size:0.9em;
    text-align: center;
    padding-top:0.1em;
    padding-bottom: 0.1em;
  }
}
</style>


<script>
import topBack from "../../components/topBack";

export default {
  methods: {
    goMyShop() {
      this.$router.push("/shop/");
    }
  },
  components: { topBack }
};
</script>

