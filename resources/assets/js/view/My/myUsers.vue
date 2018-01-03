<template>
  <div id="my-users">
    <topBack
      :title="'我的用户'"
      style="background: #26a2ff; color:#fff;"
    >
    </topBack>

    <div class="users-count">
      <h3>{{indexData.total}}</h3>
      <h4>我的用户数（个）</h4>
    </div>

    <div class="shop-users flex flex-align-center" @click = "showShopUserList">
      <span class="user-icon flex-2">
        <i class="iconfont">
          &#xe6a3;
        </i>
      </span>
      <h3 class="flex-9">店主用户</h3>
      <div class="flex-1">{{indexData.manager_total}}</div>
      <span class="flex-1" >
        <i class="iconfont">
          &#xe62e;
        </i>
      </span>
    </div>

    <transition name="fade">
      <ul class="shop-users-list" v-if="isShopUserListShow">
        <li class="flex flex-align-center" v-for="item in shopUsers">
          <span class="flex-1"><img :src="item.avatar" alt=""></span>
          <div class="flex-3">{{item.name}}</div>
          <div class="flex-2">{{item.mobile}}</div>
        </li>
      </ul>
    </transition>

    <div class="common-users flex flex-align-center" @click="showCommonUserList">
      <span class="user-icon flex-2">
        <i class="iconfont">
          &#xe6a3;
        </i>
      </span>
      <h3 class="flex-9">普通用户</h3>
      <div class="flex-1">{{indexData.member_total}}</div>
      <span class="flex-1" >
         <i class="iconfont">
          &#xe62e;
        </i>
      </span>
    </div>

    <transition name="fade">
      <ul class="common-users-list" v-if="isCommonUserListShow">
        <li class="flex flex-align-center" v-for="item in commomUsers">
          <span class="flex-1"><img :src="item.avatar" alt=""></span>
          <div class="flex-3">{{item.name}}</div>
          <div class="flex-2">{{item.mobile}}</div>
        </li>
      </ul>

    </transition>

  </div>
</template>

<style lang="scss" scoped>

.fade-enter-active {
  transition: opacity 1s;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}

#my-users{
  padding-top:2em;
  min-height: 100vh;
  background: #eee;
  box-sizing: border-box;

  .users-count{
    height: 6em;
    background: #26a2ff;
    
    h3{

      padding-top:0.5em;
      font-size: 1.7em;
      color:#fff;
      text-align: center;
    }

    h4{
      padding-top:0.8em;
      color:#fff;
      text-align: center;
      font-size: 0.9em;
    }

  }

  .shop-users{
    width:100%;
    height: 3em;
    background: #fff;
    padding-left: 1em;
    padding-right: 1em;
    box-sizing: border-box;

    .user-icon{

      i{
        font-size:1.8em;
        color:#f3ca7e;
      }
    }
  }

  .shop-users-list{
    li{
      height: 3em;
      width:100%;
      padding-left: 1em;
      padding-right:1em;
      box-sizing: border-box;

      img{
        width:2em;
        height:2em;
        border-radius: 0.2em;
      }

    }
  }

  .common-users{
    @extend .shop-users;
    margin-top:0.2em;
    .user-icon{
      i{
        color:#7dc5eb;
      }
    }
  }

  .common-users-list{
    @extend .shop-users-list;
  }

}

</style>

<script>

import topBack from '../../components/topBack.vue'
import Loading from '../../utils/loading'
import request from '../../utils/userRequest'
import {Toast} from 'mint-ui'

export default {
  components:{topBack},
  created(){
    this.init();
  },
  data(){
    return {
      shopUsers:[],                // 店主用户
      commomUsers:[],               // 普通用户
      isShopUserListShow:false,
      isCommonUserListShow:false,
      indexData:{}
    }
  },
  methods:{
    init(){
      Loading.getInstance().open();

      request.getInstance().getData('api/proxy/members/count').then(res=>{
        console.log(res);

        this.indexData = res.data.data;
        Loading.getInstance().close();
      }).catch(err=>{

      });
    },

    // 显示店主用户
    showShopUserList(){
      if(this.indexData.manager_total == 0){
        Toast("当前店主用户为0");
        return;
      }

      if(this.isShopUserListShow == true){
        this.isShopUserListShow = false;
        return;
      }

      var _data = {
        type:0
      }
      Loading.getInstance().open();
      request.getInstance().getData("api/proxy/members",_data).then(res=>{
        this.shopUsers = res.data.data.list;
        Loading.getInstance().close();
        this.isShopUserListShow = true;
      }).catch(err=>{
        Toast(err.data.msg);
      });

    },

    hideShopUserList(){
      this.isShopUserListShow = false;
    },

    // 显示普通用户
    showCommonUserList(){
      if(this.indexData.member_total == 0){
        Toast("当前普通用户为0");
        return;
      }

      if(this.isCommonUserListShow == true){
        this.isCommonUserListShow = false;
        return;
      }

      var _data = {
        type:1
      }
      Loading.getInstance().open();
      request.getInstance().getData("api/proxy/members",_data).then(res=>{
        this.commomUsers = res.data.data.list;
        Loading.getInstance().close();
        this.isCommonUserListShow = true;
      }).catch(err=>{
        Toast(err.data.msg);
      });

    },

    hideCommonUserList(){
      this.isCommonUserListShow = false;
    }
  }
}
</script>

