<template>
	<div id="Inform">
		<div id="top" :backUrl="'\/index\/'">
			<topBack title="消息中心">
				<div class="clear-inform flex flex-reverse flex-align-center" @click="del(1)">
					清空消息
				</div>
			</topBack>
		</div>
		<div class="tab-menu flex flex-align-center flex-justify-center">
			<div class="flex flex-align-center flex-justify-center active">分润通知</div>
			<div class="flex flex-align-center flex-justify-center" @click="goUser">用户注册</div>
			<div class="flex flex-align-center flex-justify-center" @click="goSystemInfo">系统通知</div>
		</div>
		<div class="shareBenefit-box">
			<ul v-for="item in moneyList">
				<li class="flex flex-align-center flex-justify-between" @click="goDetails(item.notice_id)">
					<div class="left-content">
						<div class="personal-info flex">
							<div class="flex-1 flex">
								<div class="personal-img">从
									<img src="/images/avatar.jpg">
									<span>{{item.title}}</span>获得</div>
							</div>
						</div>
						<div class="date">{{item.created_at}}</div>
					</div>
					<div class="shareBenefit-money active">{{item.content}}</div>
				</li>
			</ul>
		</div>
	</div>
</template>


<script>
	import axios from "axios";
	import request from '../../utils/userRequest';
	import topBack from "../../components/topBack.vue";
	import { MessageBox,Toast } from "mint-ui";

	export default {
		data() {
			return {
				moneyList:[]
			};
		},
		created(){
			this.moneyInfo();
		},
		components: { topBack },
		methods: {
			goUser() {
				this.$router.push("/userRegister")
			},
			goSystemInfo() {
				this.$router.push('/systemInfo')
			},
			goDetails(e) {
				this.$router.push("/inform/money_details"+"?notice_id="+e);
			},
			moneyInfo() {
				var self=this;

				request.getInstance().getData('api/notice/index')
					.then((res) => {
						self.moneyList=res.data.data[1];
					})
					.catch((err) => {
						console.error(err);
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
					this.moneyInfo();
					})
					.catch((err) => {
					console.log(err);
					})
				},
				() => {
					//取消操作
					console.log("已经取消");
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

	.shareBenefit-box {
		margin-top: 1em;
		li {
			border: 1px solid #ddd; // line-height:2em;
			padding: 0.5em 1em;
			.left-content {
				.date {
					color: #999;
					font-size: 0.8em;
				}
			}
			.shareBenefit-money {
				font-size: 1.4em;
				font-weight: normal;
			}
			.active {
				color: green;
			}
		}
	}

	.personal-info {
		.personal-img {
			margin-bottom: 0.5em;
			img {
				display: inline-block;
				border-radius: 50%;
				width: 30px;
				height: 30px;
				vertical-align: middle;
				margin: 0 0.5em;
			}
			span {
				display: inline-block;
				margin-right: 0.7em;
			}
		}
	}
</style>