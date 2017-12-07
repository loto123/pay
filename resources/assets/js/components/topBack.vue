<template>
    <div id="top-component" class="flex flex-align-center flex-justify-between">
        <a href="javascript:;" v-on:click="goBack">返回</a>
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
  a {
    display: block;
    width: 33.33%;
  }
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
  props: ["title", "backUrl"],
  methods: {
    goBack() {
      if (!this.$props.backUrl) {
        this.$router.go(-1);
      } else {
        this.$router.push(this.$props.backUrl);
      }
    }
  }
};
</script>

