<template>
	<div id="bankManage">
		<topBack title="更换结算卡"></topBack>
		<div class="bankCard-container">
			<ul class="bankCard-list">
				<li  v-for="item in bankList" @click="changeSet(item.card_id)">
					<div class="bankCard-box" :style="{backgroundImage: 'url(' + item.card_logo + ')'}">
						<div class="card-info">
							<div class="bank-name">{{item.bank}}</div>
							<div class="card-type">{{item.card_type}}</div>
							<div class="card-number">{{item.card_num}}</div>
						</div>
						<div class="icon">
							<i class="iconfont">{{item.is_pay_card ?'&#xe62b;':''}}</i>
						</div>
					</div>
				</li>
			</ul>
		</div>
	</div>
</template>

<script>
	import request from '../../utils/userRequest';
	import topBack from "../../components/topBack";
	import { MessageBox, Toast } from "mint-ui";

	import Loading from '../../utils/loading'

	export default {
		components: { topBack },
		data() {
			return {
				bankList: []
				// card_id:null
			}
		},
		created() {
			this.bank();
		},
		methods: {
			//银行卡列表
			bank: function () {
				Loading.getInstance().open("加载中...");

				request.getInstance().getData('api/card/index')
					.then((res) => {
						this.bankList = res.data.data;
						Loading.getInstance().close();
					})
					.catch((err) => {
						Toast(err.data.msg);
						Loading.getInstance().close();
					})
			},
			//更换结算卡
			changeSet(card_id) {
				Loading.getInstance().open("加载中...");

				request.getInstance().postData('api/my/updatePayCard?'+'card_id='+card_id)
					.then((res) => {
						Toast('更换成功');
						this.bank();
						Loading.getInstance().close();
					})
					.catch((err) => {
						Toast(err.data.msg);
						Loading.getInstance().close();
					})
			}
		}
	};
</script>

<style lang="scss" scoped>
	#bankManage {
		padding-top: 2em;
		box-sizing: border-box;
	}

	.bankCard-container {
		width: 100;
		border-top: 1px solid #ccc;
		padding-top: 1em;
	}

	.bankCard-list {
		width: 90%;
		margin: auto;
		li {
			margin-bottom: 1em;
			position: relative;
			.del,
			.binding {
				position: absolute;
				right: 1em;
			}
			.del {
				bottom: 10px;
				background: #fff;
				border: none;
				outline: none;
				i {
					font-size: 2em;
					color: #777;
				}
			}
			.binding {
				top: 1em;
				color: #333;
				font-size: 0.8em;
			}
		}
	}

	.bankCard-box {
		background-size: 100% 100%;
		background-repeat: no-repeat;
		height: 7.5em;
		.card-info {
			margin-left: 18%;
			padding-top: 6%;
			color: #fff;
			.card-type,
			.bank-name {
				margin-bottom: 0.3em;
			}
			.card-type,
			.card-number {
				font-size: 0.9em;
			}
			.bank-name {
				font-size: 1em;
				margin-top: 0.1em;
			}
			.card-number {
				font-size: 1em;
			}
		}
	}
	.icon{
		position: absolute;
		right: 1em;
		top: 1em;
		color:#09BB07;
		i{
			font-size:2em;
		}
	}
</style>