<template>
  <div id="shop-order">
      <div class="top">
          <topBack style="background:#26a2ff;color:#fff;" :title="'店铺订单'"></topBack>

          <div class="data-input-wrap flex flex-align-center">
              <div class="start-time">
                  
              </div>
              <div>

              </div>
              <div class="end-time">

              </div>
          </div>
      </div>

      店铺订单
  </div>
</template>

<style lang="scss" scoped>
#shop-order {
  padding-top: 2em;

  .top {
    height: 8em;
    background: #26a2ff;

    .data-input-wrap{
        width:100%;
        height: 3em;
    }
  }
}
</style>

<script>
import topBack from "../../components/topBack";

export default {
  components: { topBack }
};
</script>

