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


      </section>
      <tabBar></tabBar> 
      首页
  </div>

     
</template>

<style lang="scss" scoped>
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
</style>


<script>
import tabBar from "../../components/tabBar";

export default {
  name: "index",
  components: { tabBar }
};
</script>