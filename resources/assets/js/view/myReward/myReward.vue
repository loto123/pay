<template>
  <!-- 发起任务 -->
  <div id="myReward">
    <topBack title="我的赏金" style="background:#26a2ff;color:#fff;"></topBack>
    <div class="select-wrap-box flex">
      <div class="title flex-3">选择打赏来源公会:</div>
      <div class="select-wrap flex flex-align-center" @click="showDropList">
        {{dealShop?dealShop:'全部'}}
      </div>
    </div>
    <div class="deal-wrap" ref='wrapper'>
      <ul v-infinite-scroll="loadMore" infinite-scroll-disabled="loading" infinite-scroll-distance="80">
        <li class="reward-list" v-for="item in shopContent">
          <div class="topContent">来自公会:{{item.shop_name}}</div>
          <div class="infoContent-box flex">
            <div class="left-content">
              <div class="avatar-wrap">
                <img :src="item.user_avatar">
              </div>
            </div>
            <div class="right-content flex flex-7 flex-align-center">
              <div class="reward-content flex flex-v flex-justify-center">
                <div class="title">
                  <span>{{item.user_name}}</span>打赏了你</div>
                <div class="date">{{changeTime(item.created_at)}}</div>
              </div>
              <div class="reward-oney">
                <div class="m-text">{{item.amount}}
                  <i class="diamond" style="float: right;margin-top: 0.1em; margin-left: 0.2em;">&#xe6f9;</i>
                </div>
              </div>
            </div>
          </div>
        </li>
      </ul>
      <p v-if="loading" class="page-infinite-loading flex flex-align-center flex-justify-center">
        <mt-spinner type="fading-circle"></mt-spinner>
        <span style="margin-left: 0.5em;color:#999;">加载中...</span>
      </p>
    </div>
    <inputList :showSwitch="dropListSwitch" v-on:hideDropList="hideDropList" :optionsList="shopList" v-if="isShow" title="选择打赏来源公会">
    </inputList>
  </div>
</template>

<style scoped lang="scss">
  #myReward {
    padding-top: 2em;
    background: #eee;
    width: 100%;
    height: 100vh;
    box-sizing: border-box;
  }

  .select-wrap-box {
    margin-top: 0.5em;
    .title{
      height: 2.5em;
      line-height:2.5em;
      width: 40%;
      background: #fff;
      padding-left:0.5em;
    }
    .select-wrap {
      width: 60%;
      margin: 0 auto;
      height: 2.5em;
      line-height:2.5em;
      padding-left: 0.5em;
      box-sizing: border-box;
      background: #fff;
    }
  }

  .deal-wrap {
    width: 100%;
    ul {
      width: 100%;
      border-top: 1px solid #ccc;
      border-bottom: 1px solid #ccc;
      .reward-list {
        background: #fff;
        width: 100%;
        box-sizing: border-box;
        padding: 0.5em;
        .topContent {
          font-size: 1em;
          height: 2em;
          line-height: 2em;
        }
      }
    }
    .page-infinite-loading {
      height: 2.5em;
      text-align: center;
    }
    .wrap {
      padding-bottom: 7em;
    }
  }

  .infoContent-box {
    .right-content {
      border-bottom: 1px solid #ddd;
      padding: 0.5em 0;
      .reward-money {
        height: 100%;
        .m-text {
          font-size: 1.1em;
          margin-top: 0.6em;
          color: #00cc00;
        }
      }
      .reward-content {
        height: 100%;
        width: 70%;
        .title {
          margin-bottom: 0.7em;
          color:#666;
          span {
            margin-right: 0.5em;
            color:#333;
          }
        }
        .date {
          color: #999;
        }
      }
    }
    .left-content {
      padding: 0.5em 0;
      margin-right: 1em;
    }
  }

  .avatar-wrap {
    box-sizing: border-box;
    img {
      width: 3em;
      height: 3em;
      border-radius: 50%;
    }
  }
</style>

<script>
  import topBack from "../../components/topBack";
  import inputList from "../../components/inputList";
  import Loading from "../../utils/loading";
  import request from "../../utils/userRequest";
  import utils from "../../utils/utils"
  import { Toast } from 'mint-ui'

  export default {
    name: "makeDeal",
    created() {
      this.init();
    },
    data() {
      return {
        dropListSwitch: false,       // 下拉框开关
        choiseMemberSwitch: false,    // 选择提醒玩家开关
        dealShop: null,
        shopList: [],
        shopId: null,
        isShow: false,
        shopContent: [],

        wrapperHeight: null,
        loading: false,
        allLoaded: false,
        canLoading: true
      };
    },
    mounted() {
      this.wrapperHeight = document.documentElement.clientHeight - this.$refs.wrapper.getBoundingClientRect().top;
    },
    methods: {
      init() {
        Loading.getInstance().open();
        // 拿到所有的店铺
        request.getInstance().getData("api/shop/lists/mine")
          .then(res => {
            this.setShopList(res);
            this.isShow = true;
            Loading.getInstance().close();
          })
          .catch(err => {
            Loading.getInstance().close();
          });
      },
      allShop() {
        var _data = {
          limit: 10,
          offset: 0,
          shop_id: this.shopId
        }
        Loading.getInstance().open();
        // 拿到所有的公会列表
        request.getInstance().getData("api/shop/tips",_data)
          .then(res => {
            this.shopContent = res.data.data.data;
            Loading.getInstance().close();
          })
          .catch(err => {
            Loading.getInstance().close();
          });
      },
      loadMore() {
        this.loading = false;
        if (this.shopContent.length == 0 || !this.canLoading) {
          return;
        }
        this.loading = true;
        this.canLoading = false;
        setTimeout(() => {
          var _data = {
            limit: 10,
            shop_id: this.shopId,
            offset: [].concat(this.shopContent).pop().id
          }
          request.getInstance().getData('api/shop/tips', _data).then(res => {
            if (res.data.data.data.length == 0) {
              this.canLoading = false;
              this.loading = false;
              return;
            }
            for (var i = 0; i < res.data.data.data.length; i++) {
              this.shopContent.push(res.data.data.data[i]);
            }
            this.canLoading = true;
            this.loading = false;
          }).catch(err => {

          });
        }, 1500);
      },
      setShopList(res) {
        var _tempList = [
          {
            label:"全部",
            value:"0"
          }
        ];
        for (let i = 0; i < res.data.data.data.length; i++) {
          var _t = {};
          _t.value = res.data.data.data[i].id.toString();
          _t.label = utils.SetString(res.data.data.data[i].name, 10);
          _tempList.push(_t);
        }
        this.shopList = _tempList;
      },

      getShopName(id) {
        for (let i = 0; i < this.shopList.length; i++) {
          if (this.shopList[i].value == id) {
            return this.shopList[i].label;
          }
        }
        // return "没有这个公会";
      },
      getDefaultPrice(id) {
        for (let i = 0; i < this.shopList.length; i++) {
          if (this.shopList[i].value == id) {
            return this.shopList[i].price;
          }
        }
      },
      showDropList() {
        if (this.shopList.length == 0) {
          Toast("当前无可选的公会,请先加入公会或创建公会");
          return;
        }
        this.dropListSwitch = true;
      },
      hideDropList(data) {
        this.dropListSwitch = false;
        this.dealShop = this.getShopName(data);
        this.shopId = data;
        this.allShop();
        this.canLoading = true;
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
      }
    },
    components: { topBack, inputList }
  };
</script>