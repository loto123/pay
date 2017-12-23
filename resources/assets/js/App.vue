<template>
  <div id="app">
    <router-view></router-view>
  </div>
</template>

<style lang="scss">
.mint-indicator-mask{
  z-index:1998 !important;
  // background-color:rgba(255,255,255,1);
  // opacity: 1;
}

.mint-indicator-wrapper{
  z-index:1999 !important;
}
</style>


<script>
export default {
  name: 'app'
}
</script>

