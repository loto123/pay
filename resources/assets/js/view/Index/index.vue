<template>
  <div id="index">
      <section id="top">
          <div class="message flex flex-reverse flex-align-center" @click ="goInform">
            <i class="iconfont" style="color:#fff; font-size:1.5em;">
              &#xe626;
            </i>
          </div>
          <section class="transaction flex flex-justify-center">
            <a href="/#/myAccount">
              <div class="imggWrap flex flex-justify-center flex-align-center">
                    <img src="/images/avatar.jpg" alt="">
              </div>
              <h3>0.00</h3>
              <h4>账户余额(元)</h4>
            </a>
          </section>
      </section>

      <section class="content">
        <ul class="flex flex-wrap-on">

          <li class="flex flex-v flex-align-center">
            <a href="/#/myAccount" class="flex flex-v flex-align-center">
               <i class="iconfont account-icon common-icon">
                &#xe61e;
              </i>
              <h3>我的账户</h3>
            </a>
          </li>

          <li class="flex flex-v flex-align-center">
            <a href="/#/shop" class="flex flex-v flex-align-center">
              <i class="iconfont myShop-icon common-icon">
                &#xe61c;
              </i>
              <h3>我的店铺</h3>
            </a>
          </li>

          <li class="flex flex-v flex-align-center">
            <a href="/#/makeDeal/my_deal" class="flex flex-v flex-align-center">
              <i class="iconfont transaction-icon common-icon">
                &#xe63b;
              </i>
              <h3>我的交易</h3>
            </a>
          </li>
          
        </ul>
      </section>
      <tabBar :status="'index'"></tabBar> 
  </div>
     
</template>

<style lang="scss" scoped>
i {
  display: block;
}

#top {
  height: 12em;
  background: #26a2ff;
  box-sizing: border-box;

  .message{
    height:2em;
    width:100%;
    padding-right:1em;
    box-sizing: border-box;
  }

  .transaction {
    width: 100%;

    .imggWrap {
      width: 5em;
      height: 5em;
      // background: #fff;
      // border-radius: 50%;
      > img {
        width: 4em;
        height: 4em;
        border-radius: 50%;
        display: block;
        //   margin-top:0.4em;
      }
    }

    h3 {
      font-size: 1.8em;
      text-align: center;
      color: #fff;
    }

    h4 {
      color: #fff;
      font-size: 0.9em;
      margin-top: 0.6em;
    }
  }
}

.content {
  padding-top: 0.5em;
  box-sizing: border-box;

  ul {
    li {
      padding-top: 0.5em;
      box-sizing: border-box;
      width: 33.33%;
      height: 6em;

      .common-icon {
        font-size: 3em;
        color: #26a2ff;
      }
      h3 {
        color: #000;
        font-size: 0.95em;
      }
    }
  }
}
</style>


<script>
import tabBar from "../../components/tabBar";

export default {
  name: "index",
  components: { tabBar },
  methods:{
    goInform(){
      this.$router.push("/inform");
    }
  }
};
</script>