<template>
  <div id="notice">

    <div class="img-wrap flex flex-justify-center ">
      <img src="images/notFound.png" alt="">
    </div>

    <h3>{{noticeInfo?noticeInfo:"页面无法显示"}}</h3>
    
    <div class="button-wrap flex flex-justify-center">
      <mt-button type="primary" size="large" @click="goIndex">返回首页</mt-button>
    </div>

  </div>
</template>

<script>
  export default{
    data(){
      return{
        noticeInfo:null
      }
    },

    created(){
      this.init();
    },

    methods:{
      init(){
        if(this.$route.query.notice){
          this.noticeInfo = this.$route.query.notice;
        }
      },

      goIndex(){
        if(window.history.length <= 2){
          this.$router.push('/index');
        }else {
          this.$router.go(-2);
        }
        this.$router.push("/index");
      }
    }
  }

   
</script>

<style scoped lang="scss">
#notice{
  min-height: 100vh;
  width: 100%;
  background: #eee;
  
  >.img-wrap{
    width: 100%;
    height: auto;
    padding-top:2em;
  }

  h3{
    margin-top:2em;
    text-align: center;
    font-size: 1.4em;
    color:#888;
  }
  
  .button-wrap{
    margin:0 auto;
    margin-top:2em;
    width: 90%;
  }
}
</style>