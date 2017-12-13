<template>
    <div id="top-component" class="flex flex-align-center flex-justify-between">
        <div v-on:click="goBack">返回</div>
        <h3>{{title}}</h3>
        <div>
          <slot></slot>
        </div>
        
    </div>
</template>

<style lang="scss" scoped>
#top-component {
  height: 2em;
  width: 100%;
  padding-left: 1em;
  box-sizing: border-box;
  position: fixed;
  top: 0em;
  left: 0em;
  z-index: 1000;
  // a {
  //   display: block;
  //   width: 33.33%;
  // }
  h3 {
    text-align: center;
    width: 33.33%;
  }
  div {
    width: 33.33%;
  }
}
</style>


<script>
export default {
  name: "topBack",
  props: ["title", "backUrl", "userAction"],
  methods: {
    goBack() {
      if (!this.$props.userAction) {
        if (!this.$props.backUrl) {
          this.$router.go(-1);
        } else {
          this.$router.push(this.$props.backUrl);
        }
      }
      else{
        this.$emit(this.$props.userAction);
      }
      
    }
  }
};
</script>

