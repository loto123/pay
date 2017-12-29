<template>
  <div id="share" class="flex flex-justify-center flex-align-center">
      <div class="top flex flex-align-center">
          <h3>加入店铺</h3>
      </div>

      <div class="content-wrap-shop flex flex-v flex-align-center" v-if="!isUser">
        <div class="info flex flex-align-center">
          <img :src="logo" alt="">
          <h3>{{shopName}}</h3>
        </div>

        <div class="qr-code flex flex-align-center flex-justify-center">
          <img :src="QRCode" alt="">
        </div>
      </div>
      
      <div class="content-wrap-user" v-if="isUser">
        <div class="info flex flex-v flex-align-center">
          <img :src="logo" alt="">
          <h1>{{shopName+'('+membersCount+'人)'}}</h1>
          <h2>{{manager}} 创建于 {{timer}}</h2>
        </div>

        <div class="submit">
          <mt-button type="primary" size="large" @click="submit">申请加入</mt-button>
        </div>
      </div>

  </div>
</template>

<style lang="scss" scoped>
#share{
  box-sizing: border-box;
  padding-top:2em;
  height:100vh;
  width:100%;
  background:#26a2ff;

  .top{
      position: fixed;
      width:100%;
      height: 2em;
      top:0em;
      left: 0em;

      h3{
          color:#fff;
          text-align:center;
          display: block;
          width:100%;
      }
  }

  .content-wrap-shop{
    margin-top:-3.5em;
    width:90%;
    height:22em;
    background: #fff;
    border-radius:1em;

    .info{
      height: 5em;
      width:100%;
      box-sizing: border-box;
      padding:1em;

      >img{
        width:4em;
        height: 4em;
        border-radius:0.6em;
        display: block;
      }

      >h3{
        margin-left: 1em;
        font-size: 1.2em;
        font-weight: bold;
      }
    }

    .qr-code{
      width:13em;
      height:13em;
      background:#eee;
      border-radius: 0.6em;

      >img{
        width: 90%;
        height:90%;

      }
    }

    >h3{
      margin-top:1em;
      font-size:0.9em;
      color:#555;
      text-align:center;
    }
  }

  .content-wrap-user{
    margin-top:-3.5em;
    width:90%;
    height:22em;
    background: #fff;
    border-radius:1em;

    .info{
      height: auto;
      width:100%;
      padding-top:1em;
      box-sizing: border-box;

      >img{
        width:4.5em;
        height:4.5em;
        border-radius:0.6em;
      }

      h1{
        margin-top:0.4em;
      }
      h2{
        margin-top:0.4em;
        color:#777;
      }
    }

    .submit{
      margin:0 auto;
      width:80%;
      margin-top:5em;
    }
  }
}
</style>

<script>
import request from "../../utils/userRequest"
import Loading from "../../utils/loading"
import moment from 'moment'
import {Toast} from 'mint-ui'

export default {

  created(){
    this.init();
  },

  data(){
      return {
          shopId :null,
          userId :null,
          isUser: true,
          logo:null,
          shopName:null,
          membersCount:null,
          manager:null,
          timer:null

      }
  },
  methods:{
      init(){
        Loading.getInstance().open();

        this.shopId = this.$route.query.shopId;
        this.userId = this.$route.query.userId;

        request.getInstance().getData("api/shop/summary/"+this.shopId).then(res=>{
          this.shopName = res.data.data.name;
          this.membersCount = res.data.data.members_count;
          this.logo = res.data.data.logo;
          this.timer = moment(res.data.data.created_at*1000).format("YYYY-MM-DD");
          this.manager = res.data.data.manager;
          Loading.getInstance().close();
          
        }).catch(err=>{
          Loading.getInstance().close();
        });
      },
      submit(){
        Loading.getInstance().open();

        if(!request.getInstance().getToken()){
            Loading.getInstance().close();
            localStorage.setItem("url",window.location.href);

            Toast("当前用户未登录");
            setTimeout(()=>{
                this.$router.push("/login");
            },1000)
        }

        request.getInstance().postData("api/shop/join/"+this.shopId).then(res=>{
          Loading.getInstance().close();
          Toast("申请加入店铺成功");
        }).catch(error=>{
          Loading.getInstance().close();
        });
      }
  }
}
</script>


