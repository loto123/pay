<template>
  <div id="index">
      <section id="top">
          <section class="transaction flex flex-justify-center">
            <a href="javascript:;">
              <div class="imggWrap flex flex-justify-center flex-align-center">
                    <img src="/images/transaction.png" alt="">
              </div>
              <h3>发起交易</h3>
            </a>
          </section>
      </section>

      <section class="content">
        <ul class="flex flex-wrap-on">

          <li class="flex flex-v flex-align-center">
            <a href="javascript:;" class="flex flex-v flex-align-center">
               <i class="iconfont account-icon common-icon">
                &#xe61e;
              </i>
              <h3>我的账户</h3>
            </a>
          </li>

          <li class="flex flex-v flex-align-center">
            <a href="javascript:;" class="flex flex-v flex-align-center">
              <i class="iconfont myShop-icon common-icon">
                &#xe61c;
              </i>
              <h3>我的店铺</h3>
            </a>
          </li>

          <li class="flex flex-v flex-align-center">
            <a href="javascript:;" class="flex flex-v flex-align-center">
              <i class="iconfont transaction-icon common-icon">
                &#xe63b;
              </i>
              <h3>我的交易</h3>
            </a>
          </li>
          
        </ul>
      </section>
      <tabBar></tabBar> 
  </div>
     
</template>

<style lang="scss" scoped>

i{
  display: block;
}

#top {
  height: 12em;
  padding-top: 3.4em;
  background: #26a2ff;
  box-sizing: border-box;

  .transaction {
    width: 100%;

    .imggWrap {
      width: 5em;
      height: 5em;
      background: #fff;
      border-radius: 50%;
      > img {
        width: 3.5em;
        height: 3.5em;
        display: block;
        //   margin-top:0.4em;
      }
    }

    h3 {
      font-size: 1em;
      text-align: center;
      margin-top: 0.8em;
      color: #fff;
    }
  }
}

.content{
  padding-top:0.5em;
  box-sizing:border-box;

  ul{
    li{
      padding-top:0.5em;
      box-sizing:border-box;
      width:33.33%;
      height: 6em;

      .common-icon{
        font-size:3.5em;
        color:#26a2ff;
      }
      h3{
        color:#000;
      }
    }
  }
}
</style>


<script>
import tabBar from "../../components/tabBar";

export default {
  name: "index",
  components: { tabBar }
};
</script>