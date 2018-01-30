<template>
    <div id="FOF-page" class="flex flex-v flex-align-center">
        <div class="img-wrap">
            <img src="/images/404.jpg" alt="">
            <h3>页面未找到</h3>
        </div>
        <div class="index-button">
            <mt-button type="primary" size="large" @click="goIndex">回到首页</mt-button>
        </div>
    </div>
</template>

<style lang="scss" scoped>
#FOF-page{
    width: 100%;
    padding-top:8em;
    height: 100vh;
    background: #f5fdff;
    box-sizing: border-box;

    .img-wrap{
        width: 90%;

        >img{
            width: 100%;
        }

        h3{
            text-align: center;
            font-size:1.5em;
            color:#555;
        }
    }

    .index-button{
        margin-top:1em;
        width: 95%;
    }
}
</style>

<script>
export default {
  methods:{
      goIndex(){
          this.$router.push('/index');
      }
  }
}
</script>

