<template>
	<div id="bankManage">
		<topBack title="银行卡管理" style="background:#fff;" :backUrl="'\/my\/'"></topBack>
		<div class="bankCard-container">
			<ul class="bankCard-list">
				<li v-for="item in bankList">
					<div class="bankCard-box" :style="{backgroundImage: 'url(' + item.card_logo + ')'}">
						<div class="card-info">
							<div class="bank-name">{{item.bank}}</div>
							<div class="card-type">{{item.card_type}}</div>
							<div class="card-number">{{item.card_num}}</div>
						</div>
					</div>
					<div class="del" @click="del(item.card_id)">
						<img src="/images/delete.png">
					</div>
					<div class="binding">{{item.is_pay_card? '结算卡' : '' }}</div>
				</li>
			</ul>
			<div class="add-bankCard" @click="showPassword">
				<a href="javascript:;">添加新银行卡</a>
			</div>
		</div>
		<passWorld :setSwitch="showPasswordTag" v-on:hidePassword="hidePassword" v-on:callBack="callBack"></passWorld>
	</div>
</template>

<script>
	import axios from "axios";
	import request from '../../utils/userRequest';
	import topBack from "../../components/topBack";
	import passWorld from "../../components/password"
	import { MessageBox, Toast } from "mint-ui";

	import Loading from '../../utils/loading'

	export default {
		components: { topBack, passWorld },
		data() {
			return {
				bankList: [],
				showPasswordTag: false,       // 密码弹出开关
				isdel: false
			}
		},
		created() {
			this.bank();
		},
		methods: {
			showPassword() {
				request.getInstance().getData('api/my/info')
					.then((res) => {
						if (res.data.data.has_pay_password == 0) {
							//调转到设置支付密码
							this.$router.push('/my/setting_password');
						} else {
							this.showPasswordTag = true;   //密码层弹出

						}
					})
					.catch((err) => {
						Toast(err.data.msg);
					})
			},
			//支付密码验证
			payPasswordVal() {

			},
			hidePassword() {
				this.showPasswordTag = false;
			},
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
					})
			},
			//删除银行卡
			del(card_id) {
				MessageBox.confirm("是否删除该银行卡?", "温馨提示").then(
					() => {
						request.getInstance().postData("api/card/delete?card_id=" + card_id)
							.then((res) => {
								Toast({
									message: "删除成功",
									duration: 800
								});
								this.bank();
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
			//支付密码验证
			callBack(password) {
				var temp = {};
				temp.password = password;

				request.getInstance().postData('api/my/pay_password', temp)
					.then((res) => {
						if (res.data.code == 1) {
							this.$router.push('/my/bankCardManage/addBankCard');
						}
					})
					.catch((err) => {
						Toast(err.data.msg);
					})
			}
		}
	};
</script>

<style lang="scss" scoped>
	#bankManage {
		padding-top: 2em;
		box-sizing: border-box;
		box-sizing: border-box;
		background: #fff;
	}

	.bankCard-container {
		width: 100;
		border-top: 1px solid #ccc;
		padding-top: 1em;
	}

	.bankCard-list {
		width: 100%;
		margin: auto;
		li {
			margin-bottom: 1em;
			position: relative;
			width: 92%;
			margin: auto;
			.del,
			.binding {
				position: absolute;
				right: 1em;
			}
			.del {
				bottom: 10px;
				border: none;
				outline: none;
				img{
					display: block;
					width:1.7em;
				}
			}
			.binding {
				top: 1em;
				font-size: 0.8em;
				color: #fff;
			}
		}
	}

	.bankCard-box {
		background-size: 100% 100%;
		background-repeat: no-repeat;
		height: 7.5em;
		.card-image {
			>img {
				width: 100%;
			}
		}
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

	.add-bankCard {
		width: 90%;
		height: 4em;
		line-height: 4em;
		border: 1px dashed #ccc;
		text-align: center;
		margin: auto;
		margin-top: 1em;
		a {
			display: block;
			width: 100%;
			color: #999;
		}
	}
</style>