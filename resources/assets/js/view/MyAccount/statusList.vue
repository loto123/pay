<template>
  <!-- 出售状态 -->
  <div id="status-list">
    <topBack title="出售状态" style="background:#fff;">
    </topBack>
    <div class="bill-box">
      <div class="bill-date flex flex-align-center flex-justify-between" style="display:none">
        <div class="left-content">
          <div class="cur-date">2017年11月</div>
          <div class="month-money flex">
            <div class="expend flex-1">支出¥
              <span>616.55</span>
            </div>
            <div class="income">收入¥
              <span>616.55</span>
            </div>
          </div>
        </div>
        <div>图标</div>
      </div>

      <div class="tab-fixed flex flex-align-start" v-if="showList.length != 0">

        <div class="flex flex-v flex-align-start flex-4">
          <div class="month">{{timeInfo == null ? "加载中..." : timeInfo}}</div>
          <div class="amount">出售获得：{{tabTotal}}</div>
        </div>

        <span class="flex flex-6 flex-justify-end flex-align-center">
                    <i class="iconfont" style="font-size: 2em;" @click="filterDate">&#xe663;</i>
                </span>
      </div>

      <div v-if="showList.length == 0" class="flex flex-v flex-align-center nodata">
        <i class="iconfont">
          &#xe655;
        </i>
        <div>暂无数据</div>
      </div>

      <ul class="bill-list" v-else v-infinite-scroll="loadMore" infinite-scroll-disabled="loading"
          infinite-scroll-distance="80">
        <li v-for="item in showList" class="flex flex-align-center" :class="{'time-panel-li':item.isTimePanel}">

          <!-- 时间面板 -->
          <div class="time-panel flex flex-align-center" v-if="item.isTimePanel == true" ref="timeTab">
            <div class="bill-content flex-8 flex flex-v">
              <!-- <h5></h5> -->
              <div>{{item.time}}</div>
              <div class="flex" style="margin-top:0.2em;">
                <div class="title" style="color:#999;">出售价格:</div>
                <div class="price" style="color:#999;">{{item.amount}}</div>
              </div>
            </div>

            <!-- <div class="bill-money flex-3">

            </div> -->
          </div>

          <!-- 内容 -->
          <div class="content flex flex-align-center" v-else>
            <div class="imgWrap flex-2">
              <img :src="item.pet_pic" alt="">
            </div>

            <div class="bill-content flex-8">
              <h5>{{item.state}}</h5>
              <div class="time">{{item.created_at}}</div>
            </div>

            <div class="bill-money flex-3">
              <div class="title">出售价格:</div>
              <div class="price">{{item.price}}</div>
            </div>
          </div>

        </li>
      </ul>
      <p v-if="loading" class="page-infinite-loading flex flex-align-center flex-justify-center">
        <mt-spinner type="fading-circle"></mt-spinner>
        <span style="margin-left: 0.5em;color:#999;">加载中...</span>
      </p>
    </div>

    <mt-datetime-picker
      v-model="dateModel"
      class="profit-date"
      type="date"
      ref="picker"
      year-format="{value} 年"
      month-format="{value} 月"
      :startDate="startDate"
      :endDate="endDate"
      @confirm="choiseDate">
    </mt-datetime-picker>

  </div>
</template>

<script>
  import request from '../../utils/userRequest';
  import topBack from "../../components/topBack.vue";
  import Loading from '../../utils/loading'
  import {Toast} from "mint-ui";

  export default {
    data() {
      return {
        showAlert: false,
        type: null,      //类型
        created_at: null,        //结束时间
        size: null,  //数目

        billList: [],
        originList: [],
        showList: [],

        timeInfo: null,
        tabTotal: "",
        headList: [],

        wrapperHeight: null,
        loading: false,
        allLoaded: false,
        canLoading: true,

        //日期部分
        dateModel: null,
        dateChoise: null,    // 选择的日期
        startDate: new Date(2017,1,1),
        endDate: new Date()
      };
    },
    created() {
      this.init();
    },

    mounted() {
      // this.wrapperHeight = document.documentElement.clientHeight - this.$refs.wrapper.getBoundingClientRect().top;
      window.addEventListener('scroll', this.handleScroll);
    },

    methods: {
      // 日期部分：
      filterDate() {
        this.$refs.picker.open();
        this.$refs.picker.$children[0].$children[0].$children[2].$el.style.display = "none";
      },

      choiseDate(res) {
        // this.dateChoise = null;
        this.originList = [];
        var _year = res.getFullYear();
        var _month = res.getMonth() + 1;
        if (_month < 10) {
          _month = "0" + _month.toString();
        }
        var _date = _year + '-' + _month;
        this.dateChoise = _date;

        var data = {
          limit: 20,
          version: 2,
          month:this.dateChoise
        }

        request.getInstance().getData("api/pet/sold_record", data)
          .then((res) => {

            // 获取事件分组
            this.originList = res.data.data.grouping;

            this.billList = [].concat(this.originList);

            this.tabTotal = res.data.data.sold_amount;

            this.showList = this.buildDataList();

            if(this.showList.length>0){
              this.timeInfo = this.showList[0].time;
              this.tabTotal = this.showList[0].amount;
            }
            Loading.getInstance().close();

          })
          .catch((err) => {
            Toast(err.data.msg);
            Loading.getInstance().close();
          })

      },
      show() {
        this.showAlert = true;
      },
      cancel() {
        this.showAlert = false;
      },

      init() {
        var data = {
          type: this.type,
          created_at: this.created_at,
          limit: 20,
          version: 2
        }

        Loading.getInstance().open();

        request.getInstance().getData("api/pet/sold_record", data)
          .then((res) => {

            // 获取事件分组
            this.originList = res.data.data.grouping;

            this.billList = [].concat(this.originList);

            this.tabTotal = res.data.data.sold_amount;

            this.showList = this.buildDataList();

            this.timeInfo = this.showList[0].time;
            this.tabTotal = this.showList[0].amount;

            Loading.getInstance().close();

          })
          .catch((err) => {
            Toast(err.data.msg);
            Loading.getInstance().close();
          })
      },

      // 建立数据列表
      buildDataList() {
        var _dataList = [];

        for (var i = 0; i < this.billList.length; i++) {

          var _timePanelInfo = {};
          _timePanelInfo.time = this.billList[i].month;
          _timePanelInfo.amount = this.billList[i].sold_amount;
          _timePanelInfo.isTimePanel = true;

          if (!this.billList[i].list[0].isTimePanel) {
            this.billList[i].list.unshift(_timePanelInfo);
          }

          _dataList = _dataList.concat(this.billList[i].list);
        }

        return _dataList;
      },

      // 滚动
      handleScroll() {

        var scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
        if (!this.$refs.timeTab) {
          return;
        }

        for (var i = 0; i < this.$refs.timeTab.length; i++) {
          if (this.$refs.timeTab[i].getBoundingClientRect().top <= 30 && this.$refs.timeTab[i].getBoundingClientRect().top >= -10) {
            var _list = this.$refs.timeTab[i].innerText.split("出售价格:");

            this.timeInfo = _list[0];
            this.tabTotal = _list[1];
            return;

          }

        }
      },

      loadMore() {
        this.loading = false;
        if (this.showList.length == 0 || !this.canLoading) {
          return;
        }

        var _url = "api/pet/sold_record";
        var _data = {};

        this.loading = true;

        this.canLoading = false;

        setTimeout(() => {

          var _data = {
            limit: 20,
            offset: [].concat(this.showList).pop().id,
            version: 2
          }

          if (this.dateChoise != null) {
            _data.date = this.dateChoise;
          }

          request.getInstance().getData(_url, _data).then(res => {

            if (res.data.data.grouping.length == 0) {
              this.canLoading = false;
              this.loading = false;
              return;
            }

            // 检查新加载的第一位是否和最后一位相同
            if (this.originList[this.originList.length - 1].list[0].time == res.data.data.grouping[0].month) {

              this.originList[this.originList.length - 1].list = this.originList[this.originList.length - 1].list.concat(res.data.data.grouping[0].list);

              for (var i = 1; i < res.data.data.grouping.length; i++) {
                this.originList = this.originList.concat(res.data.data.grouping[i]);
              }

            } else {

              for (var i = 0; i < res.data.data.grouping.length; i++) {

                this.originList = this.originList.concat(res.data.data.grouping[i]);

              }

            }

            this.canLoading = true;
            this.loading = false;

            this.billList = [].concat(this.originList);

            this.showList = this.buildDataList();

          }).catch(err => {
            Loading.getInstance().close;
            Toast(err.data.msg);

          });
        }, 1500);
      }
    },
    components: {
      topBack
    }
  };
</script>

<style lang="scss" scoped>
  #status-list {
    padding-top: 2em;
    box-sizing: border-box;
    .header-right {
      width: 100%;
      padding-right: 1em;
      height: 2em;
      box-sizing: border-box;
    }
  }

  .bill-box {
    font-size: 0.9em;
    .bill-date {
      padding: 0 1em;
      color: #666;
    }
    .month-money {
      color: #999;
      .income {
        margin-left: 1em;
      }
    }
  }

  .tab-fixed {
    position: fixed;
    top: 2em;
    left: 0em;
    z-index: 1001;
    width: 100%;
    height: 3em;
    background: #eee;
    box-sizing: border-box;
    padding-left: 1em;
    padding-right: 1em;

    > div {
      color: #555;
      margin-top: 0.3em;

      .month {
        margin-top: 0.3em;
      }

      .amount {
        margin-top: 0.2em;
      }
    }

    > span {
      height: 100%;
    }
  }

  .bill-list {
    /*padding-top:2em;*/
    li {
      padding: 0 1em;
      border-top: 1px solid #ccc;
      width: 100%;
      height: 4em;
      box-sizing: border-box;

      .content {
        width: 100%;
        height: 4em;

        .imgWrap {
          > img {
            width: 3em;
          }
        }
      }

      .bill-content {
        padding-left: 0.7em;
        padding-right: 0.7em;
        box-sizing: border-box;

        .time {
          color: #999;
          font-size: 0.8em;
          margin-top: 0.5em;
        }

      }
      .bill-money {
        font-size: 1em;
        .price {
          margin-top: 0.5em;
        }
      }
      .active {
        color: #00cc00;
      }
      &:last-child {
        border-bottom: 1px solid #ccc;
      }
    }

    .time-panel-li {
      background: #eee;
      height: 3em;

      .time-panel {
        width: 100%;
        height: 100%;
      }
    }
  }

  .slide-enter-active,
  .slide-leave-active {
    transition: all 1s ease;
  }

  .slide-enter,
  .slide-leave-to {
    transform: translateY(100vh);
  }

  .sel-type-box {
    h2 {
      height: 2.8em;
      line-height: 2.8em;
      text-align: center;
      color: #333;
      border: 1px solid #f1f1f1;
      font-weight: 700;
    }
    .type-list {
      padding: 4%;
      overflow: hidden;
      li {
        width: 32%;
        float: left;
        text-align: center;
        height: 60px;
        line-height: 60px;
      }
      a {
        display: block;
        width: 100%;
        height: 100%;
      }
      .active {
        a {
          background: #50b56a;
          color: #fff;
        }
      }
    }
  }

  .sel-type {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.2);
    z-index: 1000;
    transition: all 0.3s ease-in-out;
  }

  .sel-type-box {
    position: absolute;
    z-index: 1002;
    width: 100%;
    background: #fff;
    bottom: 0;
    border-radius: 4px;
  }

  .cancel-btn {
    margin-top: 1.5em;
  }

  .nodata {
    margin-top: 10%;
    i, div {
      color: #ddd;
    }
    i {
      font-size: 3.5em;
    }
    div {
      font-size: 2em;
      margin-top: 0.3em;
    }
  }
</style>