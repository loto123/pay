<template>
  <div id="shop-order">
      <div class="top">
          <topBack style="background:#26a2ff;color:#fff;" :title="'店铺订单'"></topBack>

          <div class="data-input-wrap flex flex-align-center">
              <div class="start-time flex-4 flex flex-justify-center flex-align-center" @click = "openStartPicker">
                  {{startDate?startDate:'输入开始日期'}}
              </div>
              <div class="liner flex-2 flex flex-justify-center">
                  <div>
                  
                  </div>
              </div>
              <div class="end-time flex-4 flex flex-justify-center flex-align-center" @click = "openEndPicker">
                {{endDate?endDate:'输入结束日期'}}
              </div>
          </div>
      </div>

      <mt-datetime-picker
        ref="startPicker"
        type="date"
        year-format="{value} 年"
        month-format="{value} 月"
        date-format="{value} 日"
        v-model="pickerValue"
        v-on:confirm="setStartTime">
      </mt-datetime-picker>

       <mt-datetime-picker
        ref="endPicker"
        type="date"
        year-format="{value} 年"
        month-format="{value} 月"
        date-format="{value} 日"
        v-model="pickerValue"
        v-on:confirm="setEndTime">
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
import moment from "moment";

export default {
  components: { topBack },
  data() {
    return {
      pickerValue: null,
      startDate: null,
      endDate: null
    };
  },
  methods: {
    openStartPicker() {
      this.$refs.startPicker.open();
    },

    openEndPicker() {
      this.$refs.endPicker.open();
    },
    setStartTime(timer) {
      var _outDate = moment(timer).format("YYYY-MM-DD");
      this.startDate = _outDate;

      console.log(33);
    },
    setEndTime(timer) {
      var _outDate = moment(timer).format("YYYY-MM-DD");
      this.endDate = _outDate;
    }
  }
};
</script>

