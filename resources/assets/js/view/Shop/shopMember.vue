<template>
  <div id="shop-member">
      <div class="top">
          <top-back :title="'店铺成员(74)'">
          </top-back>
      </div>

      <div id="search-wrap" class="flex flex-align-center">
          <div class="flex flex-align-center flex-justify-around">
              <input type="text" placeholder="搜索" id="search-input" class="flex-7" @keyup="openSearchSwitch">
              <button type="button" class="flex-3" v-if="searchSwitch"> 取消 </button>
          </div>
      </div>
      <ul class="flex flex-wrap-on">

          <li class="add-member flex flex-v flex-align-center flex-justify-center">
                <div class="img-wrap flex flex-align-center flex-justify-center">
                  <i class="iconfont">
                    &#xe600;
                </i>
              </div>
              <span>
                   
              </span>
          </li>

          <li class="minus-member flex flex-v flex-align-center flex-justify-center">
              <div class="img-wrap flex flex-align-center flex-justify-center">
                  <i class="iconfont" style="margin-top:-0.2em;">
                    &#xe620;
                </i>
              </div>
              <span>
              </span>
          </li>

          <li class="flex flex-v flex-align-center flex-justify-start">
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
      height: 4.5em;
      box-sizing: border-box;

      .img-wrap {
        width: 3em;
        height: 3em;
        border: 1px solid #aaa;
        border-radius: 0.4em;

        > i {
          font-size: 2em;
          color: #aaa;
        }
      }

      > img {
        width: 2.6em;
        height: 2.6em;
        display: block;
      }

      h3 {
        font-size: 0.8em;
        margin-top:0.2em;
      }

      .avatar{
          box-sizing: border-box;
          margin-top:0.6em;
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

