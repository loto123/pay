<template>
  <div id="bill">
    <topBack title="账单明细" style="background:#26a2ff;color:#fff;">
      <div class="flex flex-reverse flex-align-center header-right">
        <div style="width:50%;text-align:center">
          <i class="iconfont" style="font-size:1.4em;" @click="filterDate">
            &#xe663;
          </i>
        </div>
      </div>
    </topBack>
    <!-- 固定 -->
    <div class="tab-fixed flex flex-v flex-align-start" v-if="recordList.length != 0" v-show="isShowPanel">
      <div class="month">{{timeInfo==null?"加载中...":timeInfo}}</div>
      <div>
        <div class="amount">
          <span>收入钻石:{{tabIncome}}<i class="diamond">&#xe6f9;</i></span>
          <span>支出钻石:{{tabDisburse}}<i class="diamond">&#xe6f9;</i></span>
        </div>
      </div>
    </div>

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
      <div v-if="recordList.length == 0" class="flex flex-v flex-align-center nodata">
        <i class="iconfont">
          &#xe655;
        </i>
        <div>暂无数据</div>
      </div>
      <!-- 插入 -->
      <ul class="bill-list" v-else v-infinite-scroll="loadMore" infinite-scroll-disabled="loading" infinite-scroll-distance="80">
        <li v-for="item in recordList" :class="{'time-tab':item.isTimePanel}">
          <a href="javascript:;" class="flex" v-if="item.isTimePanel == false" @click="details(item.id)">
            <div class="flex-7">
              <div class="bill-content">
                <h5>{{status(item.type)}}</h5>
                <div class="time">{{changeTime(item.created_at)}}</div>
              </div>
            </div>
            <div class="flex-3 recordList-content">
              <div>
                <div class="bill-money" v-bind:class="[item.mode == 0?'green-color':'']">
                  {{item.mode == 1?-item.amount:'+'+item.amount}}
                  <i class="diamond">&#xe6f9;</i>
                </div>
              </div>
            </div>
          </a>
          <div v-if="item.isTimePanel == true" class="time-tab" ref="timeTab">
            <div class="month">{{item.time}}</div>
            <div>
              <div class="amount">
                <span>收入钻石:{{item.in}}<i class="diamond">&#xe6f9;</i></span>
                <span>支出钻石:￥{{item.out}}<i class="diamond">&#xe6f9;</i></span>
              </div>
            </div>
          </div>
        </li>
      </ul>
      <p v-if="loading" class="page-infinite-loading flex flex-align-center flex-justify-center">
        <mt-spinner type="fading-circle"></mt-spinner>
        <span style="margin-left: 0.5em;color:#999;">加载中...</span>
      </p>

      <mt-datetime-picker v-model="dateModel" class="profit-date" type="date" ref="picker" year-format="{value} 年" month-format="{value} 月"
        :startDate="startDate" :endDate="endDate" @confirm="choiseDate">
      </mt-datetime-picker>
    </div>
  </div>
</template>

<script>
  import request from '../../utils/userRequest';
  import topBack from "../../components/topBack.vue";
  import Loading from '../../utils/loading'
  import { Toast } from "mint-ui";
  import moment from "moment"

  export default {
    data() {
      return {
        shopId:null,                  //店铺id
        type: null,                  //类型
        created_at: null,            //结束时间
        size: null,                  //数目
        recordList: [],

        headList: [],               // timeTab数组
        timeInfo: null,
        tabTotal: "",
        tabStatus: [true, false],
        tabIncome: "",               //收入
        tabDisburse: "",             //支出
        wrapperHeight: null,
        loading: false,
        allLoaded: false,
        canLoading: true,

        dateModel: null,
        dateChoise: null,           // 选择的日期
        startDate: new Date(2017, 1),
        endDate: new Date(),

        isShowPanel: true
      };
    },
    created() {
      this.init();
    },

    mounted() {
      window.addEventListener('scroll', this.handleScroll);
    },

    methods: {
      details(id) {
        this.$router.push({ path: "/shop/record/record_details?id=" + id + "&types=1" });
      },
      init() {
        this.shopId = this.$route.query.id;
        this.canLoading = true;
        Loading.getInstance().open();
        this.dateChoise = null;
        var _data = {
          limit: 20,
          offset: 0
        }
        Loading.getInstance().open();
        request.getInstance().getData("api/shop/transfer/records/" + this.shopId,_data)
        .then((res) => {
          var _dataList = res.data.data.data;
          if (_dataList.length == 0) {
            this.recordList = [];
            Loading.getInstance().close();
            return;
          }
          for (var i = 0; i < _dataList.length; i++) {
            _dataList[i].isTimePanel = false;
          }
          this.recordList = _dataList;
          this.buildTimePanel();
          Loading.getInstance().close();
        })
        .catch((err) => {
          Toast(err.data.msg);
          Loading.getInstance().close();
        })
      },
      // 建立时间面板
      buildTimePanel() {
        var _head = 0;
        var _tempIndex = -1;
        var getTheDate = (timecode) => {
          if (!timecode) {
            return null;
          }

          var _index = this.changeTime(timecode).indexOf("-");
          if (_index == -1) {
            return null
          }
          var _t = this.changeTime(timecode).split("-");
          var data = _t[0] + "年" + _t[1] + "月";
          return data;
        }

        var key = 0;

        // 初始化头部
        if (this.recordList.length != 0) {
          if (this.recordList[0].isTimePanel == false) {
            key = 0;
            var _head = getTheDate(this.recordList[key].created_at);
          } else if (this.recordList[1].isTimePanel == false) {
            key = 1;
            var _head = getTheDate(this.recordList[key].created_at);
          }
        }

        var _initialData = {
          time: _head,
          index: key,
          in: "加载中...",
          out: "加载中..."
        }
        if (this.headList.length == 0) {
          this.headList.push(_initialData);
        }
        if (this.isShowPanel == false) {
          return;
        } else {
          // 插入时间标签
          for (var i = 0; i < this.recordList.length; i++) {
            if (this.recordList[i].isTimePanel == true) {
              _head = getTheDate(this.recordList[i + 1].created_at);
              continue;
            }
            try {
              var label = getTheDate(this.recordList[i].created_at);
              //  当头部与当前的创建时间不一致时
              if (_head != getTheDate(this.recordList[i].created_at)) {
                // 更新头部
                _head = getTheDate(this.recordList[i].created_at);

                var data = {
                  time: _head,
                  index: i,
                  in: "加载中...",
                  out: "加载中..."
                }
                this.headList.push(data);
              }
            } catch (e) {
              console.error(e);
            }
          }
        }
        var count = 0;

        // recordList 插值
        for (let k = 0; k < this.headList.length; k++) {
          var _index = this.headList[k].index + count;

          if (this.recordList[_index].isTimePanel == true) {
            continue;
          }
          this.headList[k].index = _index;
          this.recordList.splice(_index, 0, { isTimePanel: true, time: this.headList[k].time, in: this.headList[k].in, out: this.headList[k].out });
          count++;
        }

        for (let m = 0; m < this.recordList.length; m++) {
          if (this.recordList[m].isTimePanel == true && this.recordList[m].in == "加载中..." && this.recordList[m].out == "加载中...") {

            var _year = this.recordList[m].time.split("年")[0];
            var _month = this.recordList[m].time.split("年")[1].split("月")[0];
            var _timer = _year + "-" + _month;
            var _data = {
              month: _timer
            }
            // 获取当月的总额度
            request.getInstance().getData("api/shop/transfer/records/month/"+this.shopId, _data)
              .then(res => {
                this.recordList[m].in = res.data.data.in;
                this.recordList[m].out = res.data.data.out;

                for (var j = 0; j < this.headList.length; j++) {
                  if (this.headList[j].time == this.recordList[m].time) {
                    this.headList[j].in = res.data.data.in;
                    this.headList[j].out = res.data.data.out;
                  }
                }

                this.timeInfo = this.recordList[0].time;
                this.tabIncome = this.recordList[0].in; //收入
                this.tabDisburse = this.recordList[0].out;//支出

              }).catch();
          }
        }
        count = 0;
      },

      // 滚动
      handleScroll() {
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;

        if (!this.$refs.timeTab) {
          return;
        }

        for (var i = 0; i < this.$refs.timeTab.length; i++) {
          if (this.$refs.timeTab[i].getBoundingClientRect().top <= "70" && this.$refs.timeTab[i].getBoundingClientRect().top > 0) {

            this.timeInfo = this.headList[i].time;
            this.tabIncome = this.headList[i].in;
            this.tabDisburse = this.headList[i].out;
          }
        }
      },

      // 上滑加载更多
      loadMore() {
        this.loading = false;
        if (this.recordList.length == 0 || !this.canLoading) {
          return;
        }
        var _data = {
          limit: 20,
          offset: [].concat(this.recordList).pop().id
        }
        this.loading = true;
        this.canLoading = true;

        setTimeout(() => {
          if (this.dateChoise != null) {
            _data.date = this.dateChoise;
          }
          //明细
          request.getInstance().getData("api/shop/transfer/records/" + this.shopId, _data).then(res => {
            if (res.data.data.data.length == 0) {
              this.canLoading = false;
              this.loading = false;
              return;
            }
            for (var i = 0; i < res.data.data.data.length; i++) {
              res.data.data.data[i].isTimePanel = false;
              this.recordList.push(res.data.data.data[i]);
            }
            this.canLoading = true;
            this.loading = false;
            this.buildTimePanel();
          }).catch(err => {
            Loading.getInstance().close;
            Toast(err.data.msg);
          });
        }, 1500)
      },
      filterDate() {
        this.$refs.picker.open();
        this.$refs.picker.$children[0].$children[0].$children[2].$el.style.display = "none";
      },
      choiseDate(res) {
        this.headList = [];
        var _year = res.getFullYear();
        var _month = res.getMonth() + 1;
        if (_month < 10) {
          _month = "0" + _month.toString();
        }
        var _date = _year + '-' + _month;
        this.dateChoise = _date;
        //明细
        var _data = {
          limit: 20,
          offset: 0,
          start: this.dateChoise,
        }
        request.getInstance().getData("api/shop/transfer/records/" + this.shopId, _data)
          .then((res) => {
            var _dataList = res.data.data.data;
            if (_dataList.length == 0) {
              Loading.getInstance().close();
              this.recordList = [];
              return;
            }
            for (var i = 0; i < _dataList.length; i++) {
              _dataList[i].isTimePanel = false;
            }
            this.recordList = _dataList;
            this.buildTimePanel();
            Loading.getInstance().close();
          })
          .catch((err) => {
            Toast(err.data.msg);
            Loading.getInstance().close();
          })
      },
      changeTime(shijianchuo) {
        function add0(m) { return m < 10 ? '0' + m : m }
        var time = new Date(shijianchuo * 1000);
        var y = time.getFullYear();
        var m = time.getMonth() + 1;
        var d = time.getDate();
        var h = time.getHours();
        var mm = time.getMinutes();
        var s = time.getSeconds();
        return y + '-' + add0(m) + '-' + add0(d) + ' ' + add0(h) + ':' + add0(mm) + ':' + add0(s);
      },
      status(type) {
        let result = '';
        switch (type) {
          case 0: result = '转至个人账户'; break;
          case 1: result = '转给公会成员'; break;
          case 2: result = '个人账户转入'; break;
          case 3: result = '任务打赏'; break;
          case 4: result = '任务分成'; break;
        }
        return result;
      }
    },
    components: {
      topBack
    }
  };
</script>

<style lang="scss" scoped>
  @import "../../../sass/oo_flex.scss";
  .green-color {
    color: #00cc00;
  }

  .diamond {
    margin-left: 3px;
  }

  #bill {
    padding-top: 2em;
    box-sizing: border-box;
    .header-right {
      width: 100%;
      padding-right: 0.5em;
      height: 2em;
      box-sizing: border-box;
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

    >div {
      color: #555;
      margin-top: 0.3em;
    }
  }

  .bill-box {
    font-size: 0.9em;
    /* height: 3.4em; */
    /* line-height: 1.7em; */
    /* background: #eee; */
    /* padding-top: 0.5em; */
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

  .bill-list {
    li {
      padding: 0 1em;
      border-top: 1px solid #ccc;
      a {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 55px;
        line-height: 20px;
      }
      .bill-content {
        .time {
          color: #999;
          font-size: 0.8em;
        }
      }
      .bill-money {
        font-size: 1em;
      }
      .active {
        color: #00cc00;
      }
      &:last-child {
        border-bottom: 1px solid #ccc;
      }
      .recordList-content {
        text-align: right;
      }
    }
  }

  .time-tab {
    width: 100%; // height:3em;
    background: #eee;
    padding: 0;
    box-sizing: border-box;
    padding-left: 1em;
    padding-right: 1em;

    >div {
      color: #555;
      margin-top: 0.3em;
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

  .nodata {
    margin-top: 10%;
    i,
    div {
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