<template>
  <div id="shop-order">
      <div class="top">
          <topBack style="background:#26a2ff;color:#fff;" :title="'公会订单'"></topBack>

          <div class="data-input-wrap flex flex-align-center">
              <div class="start-time flex-4 flex flex-justify-center flex-align-center" @click = "openStartPicker">
                <i class="iconfont" style="padding-right:0.5em;color:#666;">&#xe704;</i>
                <span>{{startDate?startDate:'输入开始日期'}}</span>
              </div>
              <div class="liner flex-2 flex flex-justify-center">
                  <div>
                  
                  </div>
              </div>
              <div class="end-time flex-4 flex flex-justify-center flex-align-center" @click = "openEndPicker">
                <i class="iconfont" style="padding-right:0.5em;color:#666;">&#xe704;</i>
                <span>{{endDate?endDate:'输入结束日期'}}</span>
              </div>
          </div>

          <h3>
            累计交易 11 笔，总收益23 元
          </h3>

          <h4>
            日期区间收益（元）
          </h4>
          
      </div>

      <div class="content">
        <div class="tab-menu flex flex-align-center">
          <div class="flex-1 flex flex-align-center flex-justify-center " :class="[tabStatus==1?'active':null]" @click="changeTab(1)">进账</div>
          <div class="flex-1 flex flex-align-center flex-justify-center" :class="[tabStatus==2?'active':null]" @click="changeTab(2)">出账</div>
          <div class="flex-1 flex flex-align-center flex-justify-center" :class="[tabStatus==3?'active':null]" @click="changeTab(3)">收益</div>
        </div>

        <ul class="flex flex-v flex-align-center">
          <li class="flex">
            <div class="left flex flex-v flex-8">
              <div class="flex flex-align-center">
                售出：金豆 X 1000
              </div>
              <div class="flex flex-align-center">订单时间: 2015-11-23 12：22</div>
            </div>
            <div class="right flex flex-align-center flex-justify-center flex-2">
              + <span style="font-weight:bold;color:#00cc00;font-size:1.1em;">1000</span> 元
            </div>
          </li>

          <li class="flex">
            <div class="left flex flex-v flex-8">
              <div class="flex flex-align-center">
                售出：金豆 X 1000
              </div>
              <div class="flex flex-align-center">订单时间: 2015-11-23 12：22</div>
            </div>
            <div class="right flex flex-align-center flex-justify-center flex-2">
              + <span style="font-weight:bold;color:#00cc00;font-size:1.1em;">1000</span> 元
            </div>
          </li>

          <li class="flex">
            <div class="left flex flex-v flex-8">
              <div class="flex flex-align-center">
                售出：金豆 X 1000
              </div>
              <div class="flex flex-align-center">订单时间: 2015-11-23 12：22</div>
            </div>
            <div class="right flex flex-align-center flex-justify-center flex-2">
              + <span style="font-weight:bold;color:#00cc00;font-size:1.1em;">1000</span> 元
            </div>
          </li>
        </ul>
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

  </div>
</template>

<style lang="scss" scoped>
#shop-order {
  padding-top: 2em;
  box-sizing: border-box;
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
      top: 0.8em;

      > div {
        height: 100%;
      }
      .start-time {
        height: 100%;
        background: #fff;
        border-radius: 0.4em;
        span {
          color: #666;
          font-size: 0.9em;
        }
      }

      .end-time {
        height: 100%;
        background: #fff;
        border-radius: 0.4em;

        span {
          color: #666;
          font-size: 0.9em;
        }
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

    h3 {
      margin-top: 2em;
      text-align: center;
      font-size: 1em;
      color: #fff;
    }
    h4 {
      text-align: center;
      color: #fff;
      font-size: 0.9em;
      margin-top: 0.6em;
    }
  }

  .content {
    .tab-menu {
      width: 100%;
      height: 3em;
      background: #fff;

      > div {
        box-sizing: border-box;
        height: 100%;
      }

      .active {
        border-bottom: 2px solid #26a2ff;
      }
    }

    ul{
      height: auto;
      width:100%;
      margin-top:0.5em;

      li{
        height: 3.5em;
        width:100%;
        background:#fff;
        margin-top:0.5em;

        .left{
          >div{
            padding-left:1em;
            height: 50%;
            box-sizing:border-box;
            font-size:0.9em;
            color:#666;
          }
        }

        .right{
            padding-right:1em;
            box-sizing:border-box;
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
      pickerValue: null, // 选择的日期
      startDate: null, // 开始日期
      endDate: null, // 结束日期
      tabStatus: 1
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
    },
    setEndTime(timer) {
      var _outDate = moment(timer).format("YYYY-MM-DD");
      this.endDate = _outDate;
    },
    changeTab(value){
      this.tabStatus = value; 
    }
  }
};
</script>

