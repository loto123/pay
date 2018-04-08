<template>
  <div id="authAgent">
    <top-back style="background:#26a2ff;color:#fff;" :title="'授权代理'">
    </top-back>
    <div class="header-container">
      <div class="flex flex-align-center flex-justify-around search">
        <input type="text" placeholder="搜索" id="searchInput" class="flex-7 search-input">
        <button class="query-btn">查询</button>
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
    background: #fff;
    .search{
      width: 95%;
      height: 2em;
      background: #fff;
      margin: 3em auto;
      .search-input{
        border: none;
        outline: none;
        display: block;
        margin: 0 auto;
        height: 100%;
        border-radius: 0.2em;
        padding-left: 2em;
        padding-right: 2em;
        -webkit-box-sizing: border-box;
        box-sizing: border-box;
        font-size: 1em;
        width: 80%;
      }
      .query-btn{
        
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