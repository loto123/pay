<template>
  <div id="bill">
    <topBack title="账单明细" style="background:#26a2ff;color:#fff;">
      <div class="flex flex-reverse flex-align-center header-right">
        <div style="width:50%;text-align:right;height: 100%;line-height: 2em;" @click="show">
          <a href="javascript:;" style="color:#fff;">筛选</a>
        </div>
        <div style="width:50%;text-align:center">
          <i class="iconfont" style="font-size:1.4em;" @click="filterDate">
            &#xe663;
          </i>
        </div>
      </div>
    </topBack>
    <div class="change-tab flex">
      <div class="flex-1 flex flex-justify-center flex-align-center" @click="changeTab(0)" :class="{active:tabStatus[0]}">宠物买卖</div>
      <div class="flex-1 flex flex-justify-center flex-align-center" @click="changeTab(1)" :class="{active:tabStatus[1]}">钻石流转</div>
    </div>
    <!-- 固定 -->
    <div class="tab-fixed flex flex-v flex-align-start" v-if="recordList.length != 0" v-show="isShowPanel">
      <div class="month">{{timeInfo==null?"加载中...":timeInfo}}</div>
      <div v-if="tabStatus[0]==true">
        <div class="amount">
          <span>收入:￥{{tabDisburse}}</span>
          <span>支出:￥{{tabIncome}}</span>
        </div>
      </div>
      <div v-if="tabStatus[1]==true">
        <div class="amount">
          <span>收入:￥{{tabIncome}}
            <i class="diamond">&#xe6f9;</i>
          </span>
          <span>支出:￥{{tabDisburse}}
            <i class="diamond">&#xe6f9;</i>
          </span>
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
              <div class="bill-content" v-if="tabStatus[0]">
                <h5>{{status(item.type)}}</h5>
                <div class="time">{{changeTime(item.created_at)}}</div>
              </div>
              <div class="bill-content" v-if="tabStatus[1]">
                <h5>{{status2(item.type)}}</h5>
                <div class="time">{{changeTime(item.created_at)}}</div>
              </div>
            </div>
            <div class="flex-3 recordList-content">
              <div v-if="tabStatus[0]">
                <div class="bill-money" v-bind:class="[item.mode == 0?'':'green-color']">{{item.mode == 0?'-'+item.amount:'+'+item.amount}}</div>
              </div>
              <div v-if="tabStatus[1]">
                <div class="bill-money" v-bind:class="[item.mode == 0?'green-color':'']">{{item.mode == 0?'+'+item.amount:'-'+item.amount}}
                  <i class="diamond">&#xe6f9;</i>
                </div>
              </div>
              <div v-if="tabStatus[0]">
                <div class="fee" v-if="item.type==1">手续费:{{tabStatus[0]?item.fee:''}}</div>
              </div>
            </div>
          </a>
          <div v-if="item.isTimePanel == true" class="time-tab" ref="timeTab">
            <div class="month">{{item.time}}</div>
            <div v-if="tabStatus[0]==true">
              <div class="amount">
                <span>收入:￥{{item.out}}</span>
                <span>支出:￥{{item.in}}</span>
              </div>
            </div>
            <div v-if="tabStatus[1]==true">
              <div class="amount">
                <span>收入:￥{{item.in}}
                  <i class="diamond">&#xe6f9;</i>
                </span>
                <span>支出:￥{{item.out}}
                  <i class="diamond">&#xe6f9;</i>
                </span>
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

    <transition name="slide">
      <div class="sel-type" v-if="showAlert">
        <div class="sel-type-box">
          <h2>选择任务类型</h2>
          <ul class="type-list">
            <li @click="selAll">
              <a href="javascript:;">全部</a>
            </li>
            <li v-for="item in items" v-if="tabStatus[0]==true&&item.isBuy==true" @click="selContent(item.type)">
              <a href="javascript:;">{{item.title}}</a>
            </li>
            <li v-for="item in items" v-if="tabStatus[1]==true&&item.isBuy==false" @click="selContent(item.type)">
              <a href="javascript:;">{{item.title}}</a>
            </li>
          </ul>
          <div class="cancel-btn">
            <a href="javascript:;" @click="cancel">
              <mt-button type="default" size="large">取消</mt-button>
            </a>
          </div>
        </div>
      </div>
    </transition>
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
        showAlert: false,
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
        items: [
          { type: 0, title: '购买', isBuy: true },
          { type: 1, title: '出售', isBuy: true },
          { type: [2,3,6,9], title: '任务', isBuy: false },
          { type: [4, 5], title: '公会转账', isBuy: false },
          { type: 8, title: '任务打赏', isBuy: false },
          // { type: 10, title: '分润', isBuy: false },
          { type: [0, 1], title: '宠物买卖', isBuy: false }
        ],
        isShowPanel: true,
        newArray: []
      };
    },
    created() {
      this.init();
    },

    mounted() {
      window.addEventListener('scroll', this.handleScroll);
    },

    methods: {
      show() {
        this.showAlert = true;
      },
      cancel() {
        this.showAlert = false;
      },
      details(id) {
        if (this.tabStatus[0] == true) {
          // 分润状态
          this.$router.push({ path: "/myAccount/bill/bill_details/?id=" + id + "&types=1" });
        } else if (this.tabStatus[1] == true) {
          // 提现状态
          this.$router.push({ path: "/myAccount/bill/bill_details/?id=" + id + "&types=2" });
        }
      },
      init() {
        this.canLoading = true;
        Loading.getInstance().open();
        this.dateChoise = null;
        var _data1 = {
          limit: 20,
          offset: 0,
          type: [0, 1]
        }
        Loading.getInstance().open();

        // 宠物买卖
        if (this.tabStatus[0] == true) {
          request.getInstance().getData("api/account/records", _data1)
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
        } else if (this.tabStatus[1] == true) {    // 公会
          var _data2 = {
            limit: 20,
            offset: 0,
            type: [0,1,2, 3, 4, 5, 6, 8, 9, 10]
          }

          request.getInstance().getData("api/account/records", _data2)
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
        }

      },

      changeTab(tabindex) {

        if (this.loading == true) {
          return
        }
        this.tabStatus = [false, false];
        this.tabStatus[tabindex] = true;
        this.headList = [];
        this.init();
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

            if (this.tabStatus[0] == true) {

              var _data3 = {
                month: _timer,
                type: [0, 1]
              }
              // 获取当月的总额度 宠物买卖
              request.getInstance().getData("api/account/records/month", _data3)
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
            } else {
              // 获取当月的总额度 公会
              var _data4 = {
                month: _timer,
                type: [0,1,2, 3, 4, 5, 6, 8, 9, 10]
              }
              request.getInstance().getData("api/account/records/month", _data4)
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
        var _data = {};
        //宠物买卖
        if (this.tabStatus[0] == true) {
          _data = {
            limit: 20,
            offset: [].concat(this.recordList).pop().id,
            type: this.newArray.length > 0 ? this.newArray : [0, 1]
          }
          //公会
        } else if (this.tabStatus[1]) {
          _data = {
            limit: 20,
            offset: [].concat(this.recordList).pop().id,
            type: this.newArray.length > 0 ? this.newArray : [0,1,2, 3, 4, 5, 6, 7, 8, 9, 10]
          }
        }
        this.loading = true;
        this.canLoading = true;

        setTimeout(() => {
          if (this.dateChoise != null) {
            _data.date = this.dateChoise;
          }
          //明细
          request.getInstance().getData('api/account/records', _data).then(res => {
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
        }, 1500);
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
        if (this.tabStatus[0] == true) {
          var _data = {
            limit: 20,
            offset: 0,
            start: this.dateChoise,
            type: [0, 1]
          }
          request.getInstance().getData("api/account/records", _data)
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
        } else if (this.tabStatus[1] == true) {
          var _data = {
            limit: 20,
            offset: 0,
            start: this.dateChoise,
            type: [0,1,2, 3, 4, 5, 6, 8, 9, 10]
          }
          request.getInstance().getData("api/account/records", _data)
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
        }
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
          case 0: result = '购买'; break;
          case 1: result = '出售'; break;
          case 2: result = '任务拿钻'; break;
          case 3: result = '任务交钻'; break;
          case 4: result = '转账到公会'; break;
          case 5: result = '公会转入'; break;
          case 6: result = '任务手续费'; break;
          case 8: result = '任务打赏'; break;
          case 9: result = '拿钻撤销'; break;
          case 10: result = '分润转入'; break;
        }
        return result;
      },
      status2(type) {
        let result = '';
        switch (type) {
          case 0: result = '购买宠物'; break;
          case 1: result = '出售宠物'; break;
          case 2: result = '任务拿钻'; break;
          case 3: result = '任务交钻'; break;
          case 4: result = '转账到公会'; break;
          case 5: result = '公会转入'; break;
          case 6: result = '任务手续费'; break;
          case 8: result = '任务打赏'; break;
          case 9: result = '拿钻撤销'; break;
          case 10: result = '分润转入'; break;
        }
        return result;
      },
      selContent(type2) {
        this.newArray = [];
        this.newArray = [].concat(type2);
        var data = {
          type: type2
        }
        request.getInstance().getData("api/account/records?", data)
          .then((res) => {
            var _dataList = res.data.data.data;
            if (_dataList.length == 0) {
              this.recordList = [];
              Loading.getInstance().close();
              this.showAlert = false;
              return;
            }
            for (var i = 0; i < _dataList.length; i++) {
              _dataList[i].isTimePanel = false;
            }
            this.showAlert = false;
            this.recordList = _dataList;
            this.isShowPanel = false;
            this.buildTimePanel();
            Loading.getInstance().close();
          })
          .catch((err) => {
            Toast(err.data.msg);
            this.showAlert = false;
            Loading.getInstance().close();
          })
      },
      selAll() {
        this.headList = [];
        this._dataList = [];
        this.init();
        this.newArray.length = [];
        this.showAlert = false;
        this.isShowPanel = true;
        // this.canLoading = true;
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
    padding-top: 5em;
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
    top: 5em;
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
      .fee {
        color: #aaa;
        font-size: 0.8em;
      }
      .active {
        color: #00cc00;
      }
      &:last-child {
        border-bottom: 1px solid #ccc;
      }
      .recordList-content{
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
    z-index: 1002;
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

  .change-tab {
    width: 100%;
    height: 3em;
    background: #26a2ff;
    position: fixed;
    top: 2em;
    border: 1px solid #fff;
    box-sizing: border-box;

    >div {
      color: #fff;
    }

    .active {
      background: #fff;
      color: #26a2ff;
    }
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