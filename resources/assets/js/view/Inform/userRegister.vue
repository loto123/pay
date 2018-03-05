<template>
	<div id="inform">
		<topBack title="消息中心" style="background:#26a2ff;color:#fff;" :backUrl="'\/index\/'">
			<div class="clear-inform flex flex-reverse flex-align-center" @click="del(2)">
				清空消息
			</div>
		</topBack>
		<div class="tab-menu flex flex-align-center flex-justify-center">
			<div class="flex flex-align-center flex-justify-center" @click="goshareBenefit">分润通知</div>
			<div class="flex flex-align-center flex-justify-center active">用户注册</div>
			<div class="flex flex-align-center flex-justify-center" @click="goSystemInfo">系统通知</div>
		</div>
		<div class="userRegister-box" ref='wrapper'>
			<ul v-infinite-scroll="loadMore" infinite-scroll-disabled="loading" infinite-scroll-distance="80">
				<li v-for="item in registerList">
					<div class="info-header flex flex-align-end flex-justify-between">
						<div class="title">{{item.title}}</div>
						<div class="date">{{item.created_at}}</div>
					</div>
					<div class="status">{{item.content}}</div>
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
	import { MessageBox, Toast } from "mint-ui";
	import Loading from '../../utils/loading'

	export default {
		data() {
			return {
				registerList: [],

				wrapperHeight: null,
                loading: false,
                allLoaded: false,
                canLoading: true,
			};
		},
		created() {
			this.registerInfo();
		},
		components: { topBack },
		mounted(){
            this.wrapperHeight = document.documentElement.clientHeight - this.$refs.wrapper.getBoundingClientRect().top;
        },
		methods: {
			goshareBenefit() {
				this.$router.push("/inform");
			},
			goSystemInfo() {
				this.$router.push("/systemInfo");
			},
			registerInfo() {
				var self = this;
				var _data = {
					type: 2,
					limit: 20,
					offset: 0
				}
				Loading.getInstance().open("加载中...");

				request.getInstance().getData('api/notice/index', _data)
					.then((res) => {
						self.registerList = res.data.data.list;
						Loading.getInstance().close();
					})
					.catch((err) => {
						Toast(err.data.msg);
						Loading.getInstance().close();
					})
			},
			//清空消息
			del(type) {
				if (this.registerList.length==0) {
                    Toast('当前没有消息')
                    return
                }
				MessageBox.confirm("是否确认清空全部消息?", "温馨提示").then(
					() => {
						request.getInstance().postData("api/notice/delete?type=" + type)
							.then((res) => {
								Toast({
									message: "清空成功",
									duration: 800
								});
								this.registerInfo();
							})
							.catch((err) => {
								Toast(err.data.msg);
							})
					},
					() => {
						//取消操作
					}
				);
			},
			loadMore() {
				this.loading = false;
				if (this.registerList.length == 0 || !this.canLoading) {
					return;
				}
				this.loading = true;
				this.canLoading = false;
				setTimeout(() => {

					var _data = {
						type: 2,
						limit: 20,
						offset: [].concat(this.registerList).pop().notice_id
					}

					request.getInstance().getData('api/notice/index', _data).then(res => {

						if (res.data.data.list.length == 0) {
							this.canLoading = false;
							this.loading = false;
							return;
						}

						for (var i = 0; i < res.data.data.list.length; i++) {
							this.registerList.push(res.data.data.list[i]);
						}

						this.canLoading = true;
						this.loading = false;
					}).catch(err => {

					});
				}, 1500);

			}
		}
	};
</script>

<style lang="scss" scoped>
	@import "../../../sass/oo_flex.scss";
	#inform {
		width: 100%;
		padding-top: 2em;
		box-sizing: border-box;
		background: #f4f4f4;
		height: 100vh;
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
		background: #fff;
		>div {
			width: 33.3%;
			height: 100%;
			box-sizing: border-box;
		}
		.active {
			border-bottom: 0.2em solid #26a2ff;
			color: #26a2ff;
		}
	}

	.userRegister-box {
		li {
			border-bottom: 1px solid #ddd;
			padding: 0.5em 1em;
			.info-header {
				margin-bottom: 0.7em;
				.title {
					color: #333;
					font-size: 1em;
				}
				.date {
					color: #999;
					font-size: 0.8em;
				}
			}
			.status {
				color: #999;
				font-size: 0.8em;
			}
			&:first-child{
				border-top: 1px solid #ddd;
			}
		}
	}
</style>