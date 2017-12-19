<template>
  <div id="app">
    <router-view></router-view>
  </div>
</template>

<style lang="scss">
.mint-indicator-mask{
  z-index:1999 !important;
  // display:none;
}

.mint-indicator-wrapper{
  z-index:1998 !important;
  
}
</style>


<script>
export default {
  name: 'app'
}
</script>

