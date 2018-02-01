<template>
    <div id="top-component" class="flex flex-align-center flex-justify-between">
        <div v-on:click="goBack" class="flex-2">返回</div>
        <h3 class="flex-3">{{title}}</h3>
        <div class="flex-2">
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

  h3 {
    text-align: center;
  }

}
</style>


<script>
export default {
  name: "topBack",
  props: ["title", "backUrl", "userAction"],
  // mounted(){
 
  // },
  methods: {
    goBack() {
      if (!this.$props.userAction) {
        if (!this.$props.backUrl) {
          if(window.history.length <= 2){
            this.$router.push('/index');
          }else {
            this.$router.go(-1);
          }
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

