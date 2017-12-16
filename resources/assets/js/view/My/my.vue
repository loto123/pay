<template>
	<div id="my">
		<section class="header-container">
			<div class="header">
				<div class="imgWrap">
					<img src="/images/avatar.jpg">
				</div>
				<h3>发起交易</h3>
				<div class="acc-number">账号:
					<span>18674231689</span>
				</div>
			</div>
		</section>
		<section>
			<ul class="my-list">
				<li @click="reffrrer">
					<mt-cell title="推荐人" is-link>
						<img slot="icon" src="/images/referrer.png" width="30" height="30">
						<span>张三李四:
							<em>132131321321</em>
						</span>
					</mt-cell>
				</li>
				<li>
					<mt-cell title="实名认证" is-link to="/my/realAuth">
						<img slot="icon" src="/images/realName.png" width="30" height="30">
						<span>未完善</span>
					</mt-cell>
				</li>
				<li>
					<mt-cell title="银行卡管理" is-link to="/my/bankCardManage">
						<img slot="icon" src="/images/bankCardManage.png" width="30" height="30">
						<span>
							<font>0</font>张</span>
					</mt-cell>
				</li>
				<li>
					<mt-cell title="查看结算卡" is-link to="/my/checkSettle">
						<img slot="icon" src="/images/bankCardManage.png" width="30" height="30">
					</mt-cell>
				</li>
				<li>
					<mt-cell title="更多设置" is-link to="/my/set">
						<img slot="icon" src="/images/moreSet.png" width="30" height="30">
					</mt-cell>
				</li>
			</ul>
		</section>
		<tabBar :status="'my'"></tabBar>
	</div>
</template>

<style lang="scss" scoped>
	.header-container {
		padding-top: 4em;
		padding-bottom: 1em;
		box-sizing: border-box;
		background: #26a2ff;
		color: #fff;
	}

	.header {
		text-align: center;
		.imgWrap {
			width: 100%;
			>img {
				width: 4.5em;
				height: 4.5em;
				display: block;
				margin: auto;
				border-radius: 50%;
			}
		}
		h3,
		.acc-number {
			font-size: 1em;
			text-align: center;
			margin-top: 0.5em;
		}
	}

	.my-list {
		border-bottom: 1px solid #d9d9d9;
		li {
			.mint-cell {
				background-image: none;
				background-size: 100% 1px;
				background-repeat: no-repeat;
				background-position: top;
				span {
					font-size: 0.9em;
				}
			}
		}
	}
</style>


<script>
	import axios from "axios";
	import { Toast } from 'mint-ui';
	import tabBar from "../../components/tabBar";
	import request from '../../utils/userRequest';
	export default {
		data () {
			return {

			}
		},
		created(){
      this.personal();
    },
		components: { tabBar },
		methods: {
			personal(){
				request.getInstance().getData("api/my/info")
					.then((res) => {
						console.log(res)
					})
					.catch((err) => {
						console.log(err);
					})
			}
		}
	};
</script>