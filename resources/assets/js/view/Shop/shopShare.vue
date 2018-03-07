<template>
	<div id="shop-share" class="flex flex-justify-center flex-align-center">
		<topBack :title="!isUser?'邀请新会员':'加入公会'" style="background:#26a2ff;color:#fff;"></topBack>

		<div class="content-wrap-shop flex flex-v flex-align-center" v-if="!isUser">
			<div class="info flex flex-align-center">
				<img :src="logo" alt="">
				<h3>{{shopName}}</h3>
			</div>

			<div class="qr-code flex flex-align-center flex-justify-center">
				<img :src="QRCode" alt="">
			</div>

			<h3>
				将二维码分享给好友，扫一扫加入公会
			</h3>
		</div>

		<!--<div class="content-wrap-user" v-if="isUser">-->
		<!--<div class="info flex flex-v flex-align-center">-->
		<!--<img :src="logo" alt="">-->
		<!--<h1>{{shopName+'('+membersCount+'人)'}}</h1>-->
		<!--<h2>{{manager}} 创建于 {{timer}}</h2>-->
		<!--</div>-->

		<!--<div class="submit">-->
		<!--<mt-button type="primary" size="large">申请加入</mt-button>-->
		<!--</div>-->
		<!--</div>-->

	</div>
</template>

<style lang="scss" scoped>
	#shop-share {
		box-sizing: border-box;
		padding-top: 2em;
		height: 100vh;
		width: 100%;
		background: #26a2ff;

		.content-wrap-shop {
			margin-top: -3.5em;
			width: 90%;
			height: 22em;
			background: #fff;
			border-radius: 1em;

			.info {
				height: 5em;
				width: 100%;
				box-sizing: border-box;
				padding: 1em;

				>img {
					width: 4em;
					height: 4em;
					border-radius: 0.6em;
					display: block;
				}

				>h3 {
					margin-left: 1em;
					font-size: 1.2em;
					font-weight: bold;
				}
			}

			.qr-code {
				width: 13em;
				height: 13em;
				background: #eee;
				border-radius: 0.6em;

				>img {
					width: 90%;
					height: 90%;

				}
			}

			>h3 {
				margin-top: 1em;
				font-size: 0.9em;
				color: #555;
				text-align: center;
			}
		}

		.content-wrap-user {
			margin-top: -3.5em;
			width: 90%;
			height: 22em;
			background: #fff;
			border-radius: 1em;

			.info {
				height: auto;
				width: 100%;
				padding-top: 1em;
				box-sizing: border-box;

				>img {
					width: 4.5em;
					height: 4.5em;
					border-radius: 0.6em;
				}

				h1 {
					margin-top: 0.4em;
				}
				h2 {
					margin-top: 0.4em;
					color: #777;
				}
			}

			.submit {
				margin: 0 auto;
				width: 80%;
				margin-top: 5em;
			}
		}
	}
</style>

<script>
	import request from "../../utils/userRequest"
	import Loading from "../../utils/loading"
	import topBack from "../../components/topBack"
	import { Toast } from "mint-ui"

	export default {
		components: { topBack },
		data() {
			return {
				isUser: false,             // false ：商户分享界面  true ：用户加入界面
				shopId: null,
				QRCode: "",
				shopName: null,            //公会名称
				logo: null,                 // 公会头像
				membersCount: null,
				manager: null,
				timer: null,
				shareFriend_url:null
			}
		},
		created() {
			this.init().then(res => {
				if (res) {
					this.shareContent()
				}
			});
		},
		methods: {
			init() {
				this.shopId = this.$route.query.id;
				var data = {
					share_url: window.location.href.split('#')[0],
					list: ['onMenuShareTimeline', 'onMenuShareAppMessage']
				}
				Loading.getInstance().open();
				return Promise.all([request.getInstance().getData("api/shop/qrcode/" + this.shopId), request.getInstance().getData("api/shop/summary/" + this.shopId), request.getInstance().getData("api/proxy/share", data)])
					.then(res => {
						this.QRCode = res[0].data.data.url;
						this.logo = res[1].data.data.logo;
						this.shopName = res[1].data.data.name;
						this.membersCount = res[1].data.data.membersCount;
						this.shareFriend_url=res[1].data.data.share_url;

						var Data = res[2].data.data;
						var content = JSON.parse(Data.config);
						wx.config(content);
						return Promise.resolve(true);

						Loading.getInstance().close();
					}).catch(err => {
						Loading.getInstance().close();
						Toast("请求错误");
						console.error(err);
					});
			},
			shareContent() {
				let url = window.location.href.split('#')[0];
				let links = url + this.shareFriend_url;
				let title = '聚宝朋';
				let desc = '邀请你加入公会:'+this.shopName;
				let imgUrl = url + this.logo;
				wx.ready(() => {
					//分享给朋友
					wx.onMenuShareAppMessage({
						title: title, // 分享标题
						desc: desc, // 分享描述
						link: links, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
						imgUrl: imgUrl, // 分享图标
						success: function () {
							// 用户确认分享后执行的回调函数
							Toast('成功分享给朋友');
						},
						cancel: function () {
							// 用户取消分享后执行的回调函数
							Toast('分享失败，您取消了分享');
						}
					})
					//分享到朋友圈
					wx.onMenuShareTimeline({
						title: title, // 分享标题
						link: links, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
						imgUrl: imgUrl, // 分享图标
						success: function () {
							// 用户确认分享后执行的回调函数
							Toast('成功分享到朋友圈');
						},
						cancel: function () {
							// 用户取消分享后执行的回调函数
							Toast('分享失败，您取消了分享');
						}
					})
				})
			},
		}
	}
</script>