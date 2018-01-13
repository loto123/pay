<template>
  <div id="index">
      <section id="top">
          <div class="message flex flex-reverse flex-align-center" @click ="goInform">
            <i class="iconfont" style="color:#fff; font-size:1.5em;">
              &#xe626;
            </i>

            <span class="notice" v-if="newMessage>0">
            </span>
          </div>
          <section class="transaction flex flex-justify-center">
            <a href="/#/myAccount">
              <div class="imggWrap flex flex-justify-center flex-align-center">
                    <img :src="avatar" alt="">
              </div>
              <h3>{{amount}}</h3>
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
            <a class="flex flex-v flex-align-center" @click="goShareProfit">
              <i class="iconfont transaction-icon common-icon">
                &#xe63b;
              </i>
              <h3>我的分润</h3>
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

          <li class="flex flex-v flex-align-center">
            <a class="flex flex-v flex-align-center" @click="goMyUsers">
              <i class="iconfont transaction-icon common-icon">
                &#xe621;
              </i>
              <h3>我的用户</h3>
            </a>
          </li>

          <li class="flex flex-v flex-align-center">
            <a href="/#/my_vip" class="flex flex-v flex-align-center">
              <i class="iconfont transaction-icon common-icon">
                &#xe6e1;
              </i>
              <h3>我的vip</h3>
            </a>
          </li>

          <li class="flex flex-v flex-align-center">
            <a href="/#/shareUser" class="flex flex-v flex-align-center">
              <i class="iconfont transaction-icon common-icon">
                &#xe64f;
              </i>
              <h3>展业</h3>
            </a>
          </li>
          <li class="flex flex-v flex-align-center">
            <a href="/#/vipCard" class="flex flex-v flex-align-center">
              <i class="iconfont transaction-icon common-icon">
                  &#xe639;
              </i>
              <h3>vip开卡</h3>
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
      position: relative;

      .notice{
          width: 0.6em;
          height:0.6em;
          background: red;
          border-radius: 50%;
          position: absolute;
          right:0.7em;
          top:0em;
      }
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
import Loading from "../../utils/loading"
import request from "../../utils/userRequest"
import {MessageBox , Toast} from 'mint-ui'

export default {
  name: "index",
  components: { tabBar },
  data(){
    return {
      amount:null,
      avatar:null,
      newMessage:0,
      isAgent:0
    }
  },
  created(){
    this.init();
  },
  methods:{
    goInform(){
      this.$router.push("/inform");
    },

    goShareProfit(){
      if(this.isAgent == 0){

        MessageBox({
            title: '温馨提示',
            message: '此功能只对代理开放，是否开通代理？?',
            confirmButtonText:'开通',
            showCancelButton: true
        }).then(res=>{
          request.getInstance().postData("api/proxy/create").then(res=>{
            Toast("成功开通代理...");
          }).catch(err=>{
            Toast(err.data.msg)
          })
        });

      }else if(this.isAgent == 1){
        this.$router.push("/share_profit");
      }
    },

    goMyUsers(){
      if(this.isAgent == 0){
       
        MessageBox({
            title: '温馨提示',
            message: '此功能只对代理开放，是否开通代理？?',
            confirmButtonText:'开通',
            showCancelButton: true
        }).then(res=>{
          request.getInstance().postData("api/proxy/create").then(res=>{
            Toast("成功开通代理...");
          }).catch(err=>{
            Toast(err.data.msg)
          })
        });

      }else if(this.isAgent == 1){
        this.$router.push("/my/my_users");
      }
    },

    init(){
      Loading.getInstance().open();
      request.getInstance().getData("api/index").then(res=>{
        this.amount = res.data.data.balance;
        this.avatar = res.data.data.avatar;
        this.newMessage = res.data.data.new_message;
        this.isAgent = res.data.data.is_agent;
        Loading.getInstance().close();
        
      }).catch(err=>{
        Loading.getInstance().close();
      });

    }
  }
};
</script>
