<template>
  <div id="shop-order">
      <div class="top">
          <topBack style="background:#26a2ff;color:#fff;" :title="'店铺订单'"></topBack>

          <div class="data-input-wrap flex flex-align-center">
              <div class="start-time flex-4" @click = "openPicker">
                  {{trueDate}}
              </div>
              <div class="liner flex-2 flex flex-justify-center">
                  <div>

                  </div>
                  
              </div>
              <div class="end-time flex-4">

              </div>
          </div>
      </div>

      <mt-datetime-picker
        ref="picker"
        type="date"
        year-format="{value} 年"
        month-format="{value} 月"
        date-format="{value} 日"
        v-model="pickerValue"
        v-on:confirm="setTime">
      </mt-datetime-picker>

      店铺订单
  </div>
</template>

<style lang="scss" scoped>
#shop-order {
  padding-top: 2em;
  min-height: 100vh;
  background: #eee;
  .top {
    height: 8em;
    background: #26a2ff;

    .data-input-wrap {
      width: 100%;
      height: 2.8em;
      padding-left: 1em;
      padding-right: 1em;
      box-sizing: border-box;
      position: relative;
      top: 3em;

      > div {
        height: 100%;
      }
      .start-time {
        height: 100%;
        background: #fff;
        border-radius: 0.4em;
      }

      .end-time {
        height: 100%;
        background: #fff;
        border-radius: 0.4em;
      }

      .liner {
        > div {
          border-bottom: 1px solid #fff;
          height: 0.2em;
          margin-top: 1em;
          width: 40%;
        }
      }
    }
  }
}
</style>

<script>
import topBack from "../../components/topBack";
import moment from 'moment'

console.log(moment);

export default {
  components: { topBack },
  data(){
      return {
          pickerValue:null,
          trueDate:null
      }
  },
  methods:{
      openPicker(){
          this.$refs.picker.open();
      },
      setTime(){
          console.log(33);
      }
  }
};
</script>

