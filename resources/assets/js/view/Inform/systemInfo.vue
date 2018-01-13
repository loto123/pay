<template>
  <div id="Inform">
    <div id="top">
      <topBack title="消息中心" :backUrl="'\/index\/'">
        <div class="clear-inform flex flex-reverse flex-align-center" @click="del(3)">
          清空消息
        </div>
      </topBack>
    </div>
    <div class="tab-menu flex flex-align-center flex-justify-center">
      <div class="flex flex-align-center flex-justify-center" @click="goShareBenefit">分润通知</div>
      <div class="flex flex-align-center flex-justify-center" @click="goUser">用户注册</div>
      <div class="flex flex-align-center flex-justify-center active">系统通知</div>
    </div>
    <div class="systemInfo-box">
      <ul>
        <li  v-for="item in systemList" @click="goDetails(item.notice_id)">
          <div class="info-header flex flex-align-end flex-justify-between">
            <div class="title">{{item.title}}</div>
            <div class="date">{{item.created_at}}</div>
          </div>
          <div class="content">{{item.content}}</div>
        </li>
      </ul>
    </div>
  </div>
</template>


<script>
  import request from '../../utils/userRequest';
  import topBack from "../../components/topBack.vue";
  import { MessageBox,Toast } from "mint-ui";
  import Loading from '../../utils/loading'

  export default {
    data() {
      return {
        systemList:[]
      };
    },
    created(){
			this.systemInfo();
		},
    components: { topBack },
    methods: {
      goShareBenefit() {
        this.$router.push("/inform");
      },
      goUser() {
        this.$router.push("/userRegister");
      },
      goDetails(e) { //详情
        this.$router.push("/systemInfo/system_Details"+"?notice_id="+e);
      },
      systemInfo() { //列表
        var self=this;
        Loading.getInstance().open("加载中...");

				request.getInstance().getData('api/notice/index?type=3')
					.then((res) => {
            self.systemList=res.data.data.list;
            Loading.getInstance().close();
					})
					.catch((err) => {
            Toast(err.data.msg);
            Loading.getInstance().close();
					})
      },
      //清空消息
			del(type) {
				MessageBox.confirm("是否确认清空全部消息?", "温馨提示").then(
				() => {
					request.getInstance().postData("api/notice/delete?type=" + type)
					.then((res) => {
					Toast({
						message: "清空成功",
						duration: 800
					});
					this.systemInfo();
					})
					.catch((err) => {
					  Toast(err.data.msg);
					})
				},
				() => {
					//取消操作
				}
				);
			}
    }
  };
</script>

<style lang="scss" scoped>
  @import "../../../sass/oo_flex.scss";
  #top {
    width: 100%;
    background: #26a2ff;
    color: #fff;
    padding-top: 2em;
    box-sizing: border-box;
    .clear-inform {
      box-sizing: border-box;
      width: 100%;
      height: 100%;
      padding-right: 0.8em;
    }
  }

  .tab-menu {
    width: 100%;
    height: 3em;

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

  .systemInfo-box {
    margin-top: 1em;
    li {
      border: 1px solid #ddd;
      padding: 0.5em 1em;
      .info-header {
        margin-bottom: 0.7em;
        .date {
          color: #999;
          font-size: 0.8em;
        }
        .title {
          color: #333;
          font-size: 1em;
        }
      }
      .content {
        color: #999;
        font-size: 0.8em;
        width: 100%;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
      }
    }
  }
</style>