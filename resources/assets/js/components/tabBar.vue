<template>
  <div id="tabBar">
      <ul class="flex flex-align-center">
          <li class="flex-1 flex flex-v flex-justify-center flex-align-center">
              <a href="/#/index">
                  <i class="iconfont index-icon comonIcon active">&#xe61f;</i>
                  <p class="active">首页</p>
              </a>
                
          </li>
          <li class="flex-1 flex flex-v flex-justify-center flex-align-center">
              <a href="javascript:;">
                <i class="iconfont message-icon comonIcon normal">&#xe6a7;</i>
                <p class="normal">消息</p>
              </a>
          </li>
          <li class="flex-1 flex flex-v flex-justify-center flex-align-center">
              <a href="/#/my">
                <i class="iconfont account-icon comonIcon normal">&#xe6a3;</i>
                <p class="normal">我的</p>
              </a>
          </li>

      </ul>
  </div>
</template>

<style lang="scss" scoped>
i {
  display: block;
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
  height: 4em;
  border-top: 1px solid #eee;
  width: 100%;

  ul {
    padding-top: 0.2em;
    box-sizing: border-box;
    li {
      .comonIcon {
        font-size: 2.5em;
      }

      .index-icon {
        // font-size: 3em;
      }

      p {
        text-align: center;
      }
    }
  }
}
</style>


<script>
export default {};
</script>

