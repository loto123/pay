<template>
    <div id="top" class="flex flex-align-center flex-justify-between">
        <a href="javascript:;" v-on:click="goBack">返回</a>
        <h3>{{title}}</h3>
        <div></div>
    </div>
</template>

<style lang="scss" scoped>
#top {
  height: 2em;
  width: 100%;
  padding-left: 1em;
  box-sizing: border-box;
  //   border-bottom: 1px solid #eee;
  a{
      display: block;
      width:33.33%
  }
  h3 {
    text-align: center;
    width:33.33%
  }
  div{
    width:33.33%
      
  }
}
</style>


<script>
export default {
  name: "topBack",
  props: ["title"],
  methods: {
    goBack() {
      this.$router.go(-1);
    }
  }
};
</script>

