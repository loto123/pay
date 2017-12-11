<template>
  <div id="shop-member">
      <div class="top">
          <top-back :title="'店铺成员(74)'">
          </top-back>
      </div>

      <div id="search-wrap" class="flex flex-align-center">
          <div class="flex flex-align-center flex-justify-around">
              <input type="text" placeholder="搜索" id="search-input" class="flex-7" @click="openSearchSwitch">
              <button type="button" class="flex-3" v-if="searchSwitch"> 取消 </button>
          </div>
      </div>
      <ul class="flex flex-wrap-on">
          <li class="flex flex-v flex-align-center">
              <i class="iconfont">
                  &#xe600;
              </i>
              <span>
                   
              </span>
          </li>
          <li class="flex flex-v flex-align-center">
              <i class="iconfont">
                  &#xe620;
              </i>

              <span>
              </span>
          </li>

          <li class="flex flex-v flex-align-center">
              <img src="/images/avatar.jpg" alt="" class="avatar">
              <h3>玩家姓名已</h3>
          </li>
          <li class="flex flex-v flex-align-center">
              <img src="/images/avatar.jpg" alt="" class="avatar">
              <h3>玩家姓名已</h3>
          </li>
          <li class="flex flex-v flex-align-center">
              <img src="/images/avatar.jpg" alt="" class="avatar">
              <h3>玩家姓名已</h3>
          </li>
          <li class="flex flex-v flex-align-center">
              <img src="/images/avatar.jpg" alt="" class="avatar">
              <h3>玩家姓名已</h3>
          </li>
          <li class="flex flex-v flex-align-center">
              <img src="/images/avatar.jpg" alt="" class="avatar">
              <h3>玩家姓名已</h3>
          </li>
      </ul>
  </div>
</template>

<style lang="scss" scoped>
#shop-member {
  padding-top: 2em;

  ul {
    width: 100%;
    li {
      width: 20%;
      height: 4em;
      box-sizing: border-box;
      > img {
        width: 2.6em;
        height: 2.6em;
        display: block;
      }
      h3 {
        font-size: 0.9em;
      }
    }
  }
}

#search-wrap {
  width: 100%;
  height: 3em;
  background: #eee;

  > div {
    width: 95%;
    height: 2em;
    background: #fff;
    margin: 0 auto;
    #search-input {
      border: none;
      outline: none;
      display: block;
      margin: 0 auto;
      height: 100%;
      border-radius: 0.2em;
      padding-left: 2em;
      padding-right: 2em;
      box-sizing: border-box;
      font-size: 1em;
      width: 80%;
      &::-webkit-input-placeholder {
        padding-left: 49%;
        color: #aaa;
      }
    }

    > button {
      border: none;
      outline: none;
      display: block;
      height: 100%;
      background: none;
      font-size: 1em;
      color: #00cc00;
    }
  }
}
</style>

<script>
import topBack from "../../components/topBack";

export default {
  data() {
    return {
      searchSwitch: false
    };
  },
  components: { topBack },
  methods: {
    openSearchSwitch() {
      this.searchSwitch = true;
    }
  }
};
</script>

