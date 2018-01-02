<template>
  <div id="my-users">
    <topBack
      :title="'我的用户'"
      style="background: #26a2ff; color:#fff;"
    >
    </topBack>

    <div class="users-count">
      <h3>12</h3>
      <h4>我的用户数（个）</h4>
    </div>

    <div class="shop-users flex flex-align-center">
      <span class="user-icon flex-2">
        <i class="iconfont">
          &#xe6a3;
        </i>
      </span>
      <h3 class="flex-9">店主用户</h3>
      <div class="flex-1">3</div>
      <span class="flex-1">
        <i class="iconfont">
          &#xe62e;
        </i>
      </span>
    </div>

    <ul class="shop-users-list">
      <li class="flex flex-align-center">
        <span class="flex-1"><img src="/images/avatar.jpg" alt=""></span>
        <div class="flex-1">Leaf</div>
        <div class="flex-1">18173610305</div>
      </li>

      <li class="flex flex-align-center">
        <span class="flex-1"><img src="/images/avatar.jpg" alt=""></span>
        <div class="flex-1">Leaf</div>
        <div class="flex-1">18173610305</div>
      </li>

      <li class="flex flex-align-center">
        <span class="flex-1"><img src="/images/avatar.jpg" alt=""></span>
        <div class="flex-1">Leaf</div>
        <div class="flex-1">18173610305</div>
      </li>

    </ul>

    <div class="common-users flex flex-align-center">
      <span class="user-icon flex-2">
        <i class="iconfont">
          &#xe6a3;
        </i>
      </span>
      <h3 class="flex-9">普通用户</h3>
      <div class="flex-1">3</div>
      <span class="flex-1">
         <i class="iconfont">
          &#xe62e;
        </i>
      </span>
    </div>

    <ul class="common-users-list">
      <li class="flex flex-align-center">
        <span class="flex-1"><img src="/images/avatar.jpg" alt=""></span>
        <div class="flex-1">Leaf</div>
        <div class="flex-1">18173610305</div>
      </li>
    </ul>

  </div>
</template>

<style lang="scss" scoped>
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

export default {
  components:{topBack},
  data(){
    return {
      shopUsers:[],                // 店主用户
      commomUsers:[]               // 普通用户
    }
  }
}
</script>

