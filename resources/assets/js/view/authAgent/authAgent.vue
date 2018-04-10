<template>
  <div id="authAgent">
    <top-back style="background:#26a2ff;color:#fff;" :title="'授权代理'">
    </top-back>
    <div class="header-container">
      <div class="flex flex-align-center flex-justify-around search">
        <input type="text" placeholder="输入你想授权的用户手机号" id="searchInput" class="flex-7 search-input">
        <button class="query-btn">查询</button>
      </div>
      <div class="search-content">
        <div>
          <img src="/images/avatar.jpg">
        </div>
        <h2 class="name">Leaf</h2>
        <div class="account">账号:<span>321321321</span></div>
        <button class="confirm-btn">确认授权</button>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  #authAgent {
    padding-top: 2em;
    background: #F0F1F5;
    min-height: 100vh;
    box-sizing: border-box;
  }
  .header-container {
    /* background: #fff; */
    .search{
      width: 95%;
      height: 3em;
      background: #fff;
      margin: 3em auto;
      .search-input{
        border: none;
        outline: none;
        display: block;
        margin: 0 auto;
        height: 100%;
        border-radius: 1em;
        padding-left: 1em;
        -webkit-box-sizing: border-box;
        box-sizing: border-box;
        font-size: 1em;
      }
      .query-btn{
        outline: none;
        background: #26a2ff;
        color: #fff;
        border: none;
        height: 100%;
        width: 5.5em;
        border-top-right-radius: 3px;
        border-bottom-right-radius: 3px;
        font-size: 1em;
      }
    }
  }
</style>

<script>
  import topBack from '../../components/topBack'
  import Loading from "../../utils/loading"
  import request from "../../utils/userRequest"
  import { MessageBox, Toast } from 'mint-ui'

  export default {
    components: { topBack },
    data() {
      return {
       
      }
    },
    created() {
      // this.init();
    },
    methods: {
    }
  }
</script>