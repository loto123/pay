<template>
  <div id="dealList">
    <topBack title="交易行" style="background:#26a2ff;color:#fff;" :backUrl="'\/index\/'" :showBack="isCanBack">
    </topBack>
    <div class="tab-menu flex flex-align-center flex-justify-center">
      <div class="flex flex-align-center flex-justify-center" @click="goSalePet">在售宠物</div>
      <div class="flex flex-align-center flex-justify-center active">我的宠物</div>
    </div>
    <div v-if="petList.length == 0" class="flex flex-v flex-align-center nodata">
      <i class="iconfont">
        &#xe655;
      </i>
      <div>暂无数据</div>
    </div>
    <div class="pet-box" ref='wrapper'>
      <ul v-infinite-scroll="loadMore" infinite-scroll-disabled="loading" infinite-scroll-distance="80">
        <li class="flex flex-align-center flex-justify-between" v-for="item in petList">
          <div class="flex-2 pet-image">
            <img :src="item.pet_image">
          </div>
          <div class="flex-4 pet-info">
            <div class="pet-number">宠物编号:{{item.pet_id}}</div>
            <div class="pet-owner">持有人:{{setString(item.holder_name,5)}}</div>
          </div>
          <div class="flex-4 price">
            售价:
            <span>{{item.price=="面议"?"面议":(item.price+"元")}}</span>
          </div>
        </li>
      </ul>
      <p v-if="loading" class="page-infinite-loading flex flex-align-center flex-justify-center">
        <mt-spinner type="fading-circle"></mt-spinner>
        <span style="margin-left: 0.5em;color:#999;">加载中...</span>
      </p>
    </div>
  </div>
</template>


<script>
  import request from '../../utils/userRequest';
  import topBack from "../../components/topBack.vue";
  import { Toast } from "mint-ui";
  import Loading from '../../utils/loading'
  import utils from "../../utils/utils"

  export default {
    data() {
      return {
        petList: [],		//在售宠物列表

        wrapperHeight: null,
        loading: false,
        allLoaded: false,
        canLoading: true,
        isCanBack: true
      };
    },
    created() {
      this.init();
    },
    mounted() {
      this.wrapperHeight = document.documentElement.clientHeight - this.$refs.wrapper.getBoundingClientRect().top;
    },
    components: { topBack },
    methods: {
      goSalePet() {
        let apps=this.$route.query.curApp;
        if(!apps){
          this.$router.push('/salePet');
        }else{
          this.$router.push('/salePet?curApp='+apps);
        }
      },
      init() {
        let apps = this.$route.query.curApp;
        if (!apps) {
          this.isCanBack = true;
        } else {
          this.isCanBack = false;
        }
        var _data = {
          limit: 10,
          offset: 0
        }
        Loading.getInstance().open("加载中...");

        request.getInstance().getData('api/pet/my_pets', _data)
          .then((res) => {
            this.petList = res.data.data.data;
            Loading.getInstance().close();
          })
          .catch((err) => {
            Toast(err.data.msg);
            Loading.getInstance().close();
          })
      },
      loadMore() {
        this.loading = false;
        if (this.petList.length == 0 || !this.canLoading) {
          return;
        }
        this.loading = true;
        this.canLoading = false;
        setTimeout(() => {
          var _data = {
            limit: 10,
            offset: [].concat(this.petList).pop().time
          }
          request.getInstance().getData('api/pet/my_pets', _data).then(res => {
            if (res.data.data.data.length == 0) {
              this.canLoading = false;
              this.loading = false;
              return;
            }
            for (var i = 0; i < res.data.data.data.length; i++) {
              this.petList.push(res.data.data.data[i]);
            }
            this.canLoading = true;
            this.loading = false;
          }).catch(err => {

          });
        }, 1500);
      },
      setString(str, len) {
        return utils.SetString(str, len);
      }
    }
  };
</script>

<style lang="scss" scoped>
  @import "../../../sass/oo_flex.scss";
  #dealList {
    width: 100%;
    padding-top: 2em;
    box-sizing: border-box;
    background: #f4f4f4;
    min-height: 100vh;
  }

  .tab-menu {
    width: 100%;
    height: 3em;
    background: #fff;
    >div {
      width: 50%;
      height: 100%;
      box-sizing: border-box;
    }
    .active {
      border-bottom: 0.2em solid #26a2ff;
      color: #26a2ff;
    }
  }

  .pet-box {
    ul {
      background: #fff;
      border: 1px solid #eee;
      li {
        margin-top: 1em;
        padding: 1em 0;
        margin: 0 0.7em;
        border-bottom: 1px solid #eee;
        .pet-image {
          img {
            display: block;
            width: 100%;
          }
        }
        .pet-info {
          padding-left: 0.5em;
          color: #323232;
          font-size: 0.9em;
          overflow: hidden;
          white-space: nowrap;
          text-overflow: ellipsis;
          .pet-number {
            margin-bottom: 1em;
          }
        }
        .price {
          font-size: 0.9em;
          span {
            margin-left: 0.2em;
            color: #26A3FF;
            font-weight: 700;
          }
        }
      }
    }
  }

  .nodata {
    margin-top: 20vh;
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