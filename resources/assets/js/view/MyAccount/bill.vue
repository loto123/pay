<template>
    <div id="bill">
        <topBack title="账单明细" style="background:#26a2ff;color:#fff;">
            <div class="flex flex-reverse flex-align-center header-right">
               <div style="width:50%;text-align:right;height: 100%;line-height: 2em;" @click="show">
                    <a href="javascript:;" style="color:#fff;">筛选</a>
               </div>
               <div style="width:50%;text-align:center">
                    <i class="iconfont" style="font-size:1.4em;" @click="filterDate">
                        &#xe704;
                    </i>
                </div>
            </div>
        </topBack>
        <div class="change-tab flex">
            <div class="flex-1 flex flex-justify-center flex-align-center" @click="changeTab(0)" :class="{active:tabStatus[0]}">宠物买卖</div>
            <div class="flex-1 flex flex-justify-center flex-align-center" @click="changeTab(1)" :class="{active:tabStatus[1]}">公会</div>
        </div>
        <!-- 固定 -->
        <div class="tab-fixed flex flex-v flex-align-start" v-if="recordList.length != 0">
            <div class="month">{{timeInfo==null?"加载中...":timeInfo}}</div>
            <div class="amount">{{tabStatus[0]==true?'收益：':'提现：'}}{{tabTotal}}</div>
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
                        <div class="bill-content" v-if="tabStatus[0]">
                            <h5>{{status(item.type)}}</h5>
                            <div class="time">{{changeTime(item.created_at)}}</div>
                        </div>
                        <div class="bill-content" v-if="tabStatus[1]">
                            <h5>{{status(item.type)}}</h5>
                            <div class="time">{{changeTime(item.created_at)}}</div>
                        </div>
                        <div class="bill-money">{{tabStatus[0]?item.amount:item.amount}}</div>
                    </a>

                    <div v-if="item.isTimePanel == true" class="time-tab" ref="timeTab">
                        <div class="month">{{item.time}}</div>
                        <div class="amount">{{tabStatus[0]==true?'收益：':'提现：'}}{{item.total}}</div>
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

            <!-- <ul class="bill-list" v-else>
                <li v-for="item in billList" @click="details(item.id)">
                    <a href="javascript:;" class="flex">
                        <div class="bill-content">
                            <h5>{{status(item.type)}}</h5>
                            <div class="time">{{changeTime(item.created_at)}}</div>
                        </div>
                        <div class="bill-money" v-bind:class="[item.mode == 1?'':'active']">{{item.mode == 1?-item.amount:item.amount}}</div>
                    </a>
                </li>
            </ul> -->
        </div>

        <transition name="slide">
            <div class="sel-type" v-if="showAlert">
                <div class="sel-type-box">
                    <h2>选择任务类型</h2>
                    <ul class="type-list">
                        <li @click="selAll">
                            <a href="javascript:;">全部</a>
                        </li>
                        <li v-for="item in items" @click="selContent(item.type)">
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
                type: null,              //类型
                created_at: null,        //结束时间
                size: null,              //数目
                recordList: [],

                headList: [],            // timeTab数组
                timeInfo: null,
                tabTotal: "",
                tabStatus: [true, false],

                wrapperHeight: null,
                loading: false,
                allLoaded: false,
                canLoading: true,

                dateModel: null,
                dateChoise: null,    // 选择的日期
                startDate: new Date("2017,1,1"),
                endDate: new Date(),

                items: [
                    { type: 0, title: '购买' },
                    { type: 1, title: '出售' },
                    { type: [2, 3], title: '任务' },
                    { type: [4, 5], title: '公会转账' },
                    { type: 6, title: '任务手续费' },
                    { type: 7, title: '出售手续费' },
                    { type: 8, title: '任务加速' }
                ]
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
            show() {
                this.showAlert = true;
            },
            cancel() {
                this.showAlert = false;
            },
            details(id) {
                if (this.tabStatus[0] == true) {
                    // 分润状态
                    this.$router.push({ path: "/myAccount/bill/bill_details/?id=" + id + "&type=profit" });
                } else if (this.tabStatus[1] == true) {
                    // 提现状态
                    this.$router.push({ path: "/myAccount/bill/bill_details/?id=" + id + "&type=withDraw" });
                }
            },
            // init() {
            //     var data = {
            //         type: this.type,
            //         created_at: this.created_at,
            //         size: this.size
            //     }
            //     Loading.getInstance().open("加载中...");

            //     request.getInstance().getData("api/account/records")
            //         .then((res) => {
            //             this.billList = res.data.data.data
            //             Loading.getInstance().close();
            //         })
            //         .catch((err) => {
            //             Toast(err.data.msg);
            //             Loading.getInstance().close();
            //         })
            // },

            init() {
                Loading.getInstance().open();
                this.dateChoise = null;
                var _data = {
                    limit: 15,
                    offset: 0,
                    type:[0,1]
                }
                Loading.getInstance().open();
                if (this.tabStatus[0] == true) {
                    request.getInstance().getData("api/account/records", _data)
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
                } else if (this.tabStatus[1] == true) {
                    var _data = {
                        limit: 15,
                        offset: 0,
                        type:[2,3]
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
            changeTab(tabindex){
                this.tabStatus = [false,false];
                this.tabStatus[tabindex] = true;
                this.headList = [];
                this.init();
            },
            // 建立时间面板
            buildTimePanel() {
                var _head = 0;

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
                // 设置头部
                if (this.recordList.length != 0) {
                    if (this.recordList[0].isTimePanel == false) {
                        key = 0;
                        var _head = getTheDate(this.recordList[key].created_at);
                    } else if (this.recordList[1].isTimePanel == false) {
                        key = 1;
                        var _head = getTheDate(this.recordList[key].created_at);
                    }
                }

                if (this.headList.length == 0) {
                    // 分润列表
                    if (this.tabStatus[0] == true) {
                        // 日期筛选 
                        if (this.dateChoise != null) {
                            var _data = {
                                month: this.dateChoise,
                                type:[0,1]
                            }

                            // 获取当月的总额度
                            request.getInstance().getData("api/account/records/month", _data)
                                .then(res => {
                                    var _initialData = {
                                        time: _head,
                                        index: key,
                                        total: res.data.data.total
                                    }
                                    // this.headList.push(_initialData);
                                    // this.timeInfo = this.headList[0].time;
                                    // this.tabTotal = this.headList[0].total;
                                }).catch();
                        } else {
                            // 获取当月的总额度
                            request.getInstance().getData("api/account/records/month", _data)
                                .then(res => {
                                    var _initialData = {
                                        time: _head,
                                        index: key,
                                        total: res.data.data.total
                                    }
                                    // this.headList.push(_initialData);
                                    // this.timeInfo = this.headList[0].time;
                                    // this.tabTotal = this.headList[0].total;
                                }).catch();
                        }
                    } else if (this.tabStatus[1] == true) {
                        // 提现列表
                        // 日期筛选 
                        if (this.dateChoise != null) {
                            var _data = {
                                month: this.dateChoise,
                                type:[2,3,4,]
                            }
                            // 获取当月的总额度
                            request.getInstance().getData("api/account/records/month", _data)
                                .then(res => {
                                    var _initialData = {
                                        time: _head,
                                        index: key,
                                        total: res.data.data.total
                                    }
                                    // this.headList.push(_initialData);
                                    // this.timeInfo = this.headList[0].time;
                                    // this.tabTotal = this.headList[0].total;
                                }).catch();
                        } else {
                            // 获取当月的总额度
                            request.getInstance().getData("api/account/records/month", _data)
                                .then(res => {
                                    // var _initialData = {
                                    //     time:_head,
                                    //     index:key,
                                    //     total:res.data.data.total
                                    // }
                                    // this.headList.push(_initialData);
                                    // this.timeInfo = this.headList[0].time;
                                    // this.tabTotal = this.headList[0].total;
                                }).catch();
                        }

                    }
                }

                var _initialData = {
                    time: _head,
                    index: key,
                    total: "加载中..."
                }
                if (this.headList.length == 0) {
                    this.headList.push(_initialData);
                }

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
                                total: "加载中..."
                            }

                            this.headList.push(data);

                        }
                    } catch (e) {
                        console.log(e);
                    }

                }

                var count = 0;

                // recordList 插值
                for (let k = 0; k < this.headList.length; k++) {
                    var _index = this.headList[k].index + count;

                    if (this.recordList[_index].isTimePanel == true) {
                        continue;
                    }
                    this.recordList.splice(_index, 0, { isTimePanel: true, time: this.headList[k].time, total: this.headList[k].total });
                    count++;
                }

                for (let m = 0; m < this.recordList.length; m++) {
                    if (this.recordList[m].isTimePanel == true && this.recordList[m].total == "加载中...") {

                        var _year = this.recordList[m].time.split("年")[0];
                        var _month = this.recordList[m].time.split("年")[1].split("月")[0];
                        var _timer = _year + "-" + _month;
                        var _data = {
                            month: _timer
                        }

                        if (this.tabStatus[0] == true) {
                            // 获取当月的总额度(分润)
                            request.getInstance().getData("api/account/records/month", _data)
                                .then(res => {
                                    this.recordList[m].total = res.data.data.total;
                                    this.timeInfo = this.recordList[0].time;
                                    this.tabTotal = this.recordList[0].total;
                                }).catch();
                        } else {
                            // 获取当月的总额度(分润)
                            request.getInstance().getData("api/account/records/month", _data)
                                .then(res => {
                                    this.recordList[m].total = res.data.data.total;
                                    this.timeInfo = this.recordList[0].time;
                                    this.tabTotal = this.recordList[0].total;
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
                        if (i > 1) {
                            this.timeInfo = this.headList[i - 1].time;
                            this.tabTotal = this.headList[i - 1].total;
                        }
                    }

                }
            },

            // 上滑加载更多
            loadMore() {
                this.loading = false;
                if (this.recordList.length == 0 || !this.canLoading) {
                    return;
                }

                this.loading = true;

                this.canLoading = false;

                setTimeout(() => {

                    var _data = {
                        limit: 5,
                        offset: [].concat(this.recordList).pop().id,
                    }

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
                // this.dateChoise = null;
                this.headList = [];
                var _year = res.getFullYear();
                var _month = res.getMonth() + 1;
                if (_month < 10) {
                    _month = "0" + _month.toString();
                }
                var _date = _year + '-' + _month;
                this.dateChoise = _date;
                var _data = {
                    limit: 15,
                    offset: 0,
                    start: this.dateChoise
                }
                //明细
                if (this.tabStatus[0] == true) {
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
                    case 2: result = '任务'; break;
                    case 3: result = '任务'; break;
                    case 4: result = '公会转账'; break;
                    case 5: result = '公会转账'; break;
                    case 6: result = '任务手续费'; break;
                    case 7: result = '出售手续费'; break;
                    case 8: result = '任务加速'; break;
                }
                return result;
            },

            selContent(type) {
                Loading.getInstance().open("加载中...");
                request.getInstance().getData("api/account/records?type=" + type)
                    .then((res) => {
                        this.recordList = res.data.data.data;
                        console.log(this.recordList);
                        this.showAlert = false;
                        Loading.getInstance().close();
                    })
                    .catch((err) => {
                        Toast(err.data.msg);
                        Loading.getInstance().close();
                    })
            },
            selAll() {
                this.init();
                this.showAlert = false;
            }
        },
        components: {
            topBack
        }
    };
</script>

<style lang="scss" scoped>
    @import "../../../sass/oo_flex.scss";
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
    .tab-fixed{
        position: fixed;
        top:5em;
        left: 0em;
        z-index:1001;
        width:100%;
        height:3em;
        background:#eee;
        box-sizing:border-box;
        padding-left: 1em;
        padding-right:1em;

        >div{
            color: #555;
            margin-top:0.3em;
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
                font-size: 1.2em;
            }
            .active {
                color: #00cc00;
            }
            &:last-child {
                border-bottom: 1px solid #ccc;
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
    .change-tab{
        width:100%;
        height: 3em;
        background:#26a2ff;
        position: fixed;
        top:2em;
        border:1px solid #fff;
        box-sizing: border-box;

        >div{
            color:#fff;
        }

        .active{
            background:#fff;
            color:#26a2ff;
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