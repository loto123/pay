<template>
  <div id="app">
    <router-view></router-view>
  </div>
</template>

<style lang="scss">
.mint-indicator-mask{
  z-index:1998 !important;
  
}

.mint-indicator-wrapper{
  z-index:1999 !important;
}

#drop-list-component{
    .mint-radio-label{
      font-size: 0.9em;
    }
}

.mint-toast{
    z-index: 9999;
}
</style>

<script>
export default {
  name: 'app'
}
</script>

