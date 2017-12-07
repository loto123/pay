<template>
  <div id="shop-detail">
      <topBack style="color:#fff;"></topBack>
      
      <div class="top flex flex-v flex-align-center">
        <div class="img-wrap flex flex-justify-center flex-align-center flex-wrap-on">
            <img src="/images/avatar.jpg" alt="" class="avatar">
            <img src="/images/avatar.jpg" alt="" class="avatar">
            <img src="/images/avatar.jpg" alt="" class="avatar">
            <img src="/images/avatar.jpg" alt="" class="avatar">
            <img src="/images/avatar.jpg" alt="" class="avatar">
            
            <img src="/images/avatar.jpg" alt="" class="avatar">
            <img src="/images/avatar.jpg" alt="" class="avatar">
            <img src="/images/avatar.jpg" alt="" class="avatar">
            <img src="/images/avatar.jpg" alt="" class="avatar">
        </div>
        <h3 style="margin-top:0.5em;">店铺1111</h3>
        <h3>店铺id:123123123</h3>
      </div>

        <div class="menu flex ">
            <div class="menu-item flex flex-v flex-align-center flex-justify-around">
                <i class="iconfont">
                    &#xe61e;
                </i>
                <h3>店铺账户</h3>
            </div>

            <div class="menu-item flex flex-v flex-align-center flex-justify-around">
                <i class="iconfont">
                    &#xe63b;
                </i>
                <h3>交易管理</h3>
            </div>
            <div class="menu-item flex flex-v flex-align-center flex-justify-around">
                <i class="iconfont">
                    &#xe603;
                </i>
                <h3>
                    店铺订单
                </h3>
            </div>
            
        </div>
  </div>
</template>

<style lang="scss" scoped>
#shop-detail {
    background: #eee;

  .top {
    padding-top: 2em;
    height: 10em;
    background: #26a2ff;
    box-sizing: border-box;

    .img-wrap {
      width: 4.5em;
      height: 4.5em;
      background: #eee;
      border-radius: 0.3em;
      margin-top: 0.5em;
      padding: 0.2em;

      .avatar {
        margin-top: 1%;
        margin-left: 1%;
        width: 30%;
        height: 30%;
      }
    }

    h3 {
      padding-top: 0.2em;
      padding-bottom: 0.2em;
      color: #fff;
      font-size: 0.9em;
    }
  }

  .menu {
    height: 6em;

    .menu-item {
      width: 25%;
      height: 100%;
      box-sizing:border-box;
      padding-top:0.4em;

      > i {
        display: block;
        font-size: 3em;
      }

      h3{
          font-size:0.9em;
      }
    }
  }
}
</style>

<script>
import topBack from "../../components/topBack";

export default {
  name: "shopDetail",
  components: { topBack }
};
</script>

