<template>
  <div id="tabBar">
      <ul class="flex flex-align-center">
          <li class="flex-1 flex flex-v flex-justify-center flex-align-center">
              <a href="/#/index">
                  <i class="iconfont index-icon comonIcon" v-bind:class="[status == 'index'?'active':'normal']">&#xe61f;</i>
                  <p v-bind:class="[status == 'index'?'active':'normal']">首页</p>
              </a>
                
          </li>
          <li class="flex-1 flex flex-v flex-justify-center flex-align-center">
           <div class="make-deal flex flex-v flex-align-center">
              <a href="/#/makeDeal">
                <i class="iconfont message-icon comonIcon normal flex flex-align-center flex-justify-center">&#xe600;</i>
                <p class="normal">发起交易</p>
              </a>
           </div>
          </li>
          <li class="flex-1 flex flex-v flex-justify-center flex-align-center">
              <a href="/#/my">
                <i class="iconfont account-icon comonIcon" v-bind:class="[status == 'my'?'active':'normal']">&#xe6a3;</i>
                <p v-bind:class="[status == 'my'?'active':'normal']">我的</p>
              </a>
          </li>

      </ul>
  </div>
</template>

<style lang="scss" scoped>
i {
  /*display: block;*/
}

.active {
  color: #26a2ff;
}

.normal {
  color: #666;
}

#tabBar {
  position: absolute;
  bottom: 0em;
  left: 0em;
  height: 3em;
  border-top: 1px solid #eee;
  width: 100%;
  background: #f5f7fb;

  ul {
    padding-top: 0.2em;
    box-sizing: border-box;
    li {
      .comonIcon {
        font-size: 1.7em;
      }

      p {
        margin-top: 0.2em;
        text-align: center;
        font-size: 0.8em;
      }

      .make-deal {
        width: 4.5em;
        height: 4.5em;
        background: #f5f7fb;
        border-radius: 50%;
        margin-top: -3.2em;

        i {
          width:1.9em;
          height: 1.9em;
          margin-top: 0.4em;
          /*padding: 0.45em;*/
          background: #26a2ff;
          border-radius: 50%;
          color: #fff;
        }

        p{
          margin-top:0.5em;
        }
      }
    }
  }
}
</style>

<script>
export default {
  props:["status"]
};
</script>

