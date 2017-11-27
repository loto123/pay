<template>
  <div id="login">
    <div v-on:click = "go">
        login{{count}}
    </div>
  </div>
</template>

<script>
export default {
  name: "login",

  computed: {
    count() {
      return this.$store.state.count;
    }
  },

  methods: {
    go() {
      console.log(this);
      this.$store.dispatch("increment",15);
    }

    // go: function() {
    //   count++;
    //   //   console.log("gogogog");
    // }
  }
};
</script>

