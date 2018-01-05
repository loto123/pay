<template>
	<div id="recharge" class="recharge-container">
		<topBack title="充值" style="background: #eee;">
			<div class="flex flex-reverse" style="width:100%;padding-right:1em;box-sizing:border-box;" @click="goIndex">
				<i class="iconfont" style="font-size:1.4em;">&#xe602;</i>
			</div>
		</topBack>
		<div class="recharge-box">
			<div class="title">充值金额</div>
			<div class="recharge-money flex flex-justify-center">
				<label>￥</label>
				<input type="text" placeholder="请输入金额" v-model="amount">
			</div>
			<div class="recharge-way">
				<div class="title">选择充值方式</div>
				<div class="list-wrap">
					<mt-radio align="right" title="" v-model="value" :options="options1">
					</mt-radio>
				</div>
			</div>
			<a href="javascript:;" class="recharge-btn" @click="rechargeBtn">
				<mt-button type="primary" size="large">充值</mt-button>
			</a>
		</div>
	</div>
</template>

<script>
	import request from '../../utils/userRequest';
	import topBack from "../../components/topBack.vue";
	import { MessageBox, Toast } from "mint-ui";

	export default {
		data() {
			return {
				amount: null,
				options1:[],
				way:null,
				value:null
			}
		},
		created() {
			this.selWay();
		},
		components: { topBack},
		props: ["showSwitch", "optionsList"],
		methods: {
			// hideTab() {
			// 	this.$emit("hideDropList", this.choiseValue);
			// },
			goIndex() {
				this.$router.push("/index");
			},
			rechargeBtn() {
				var self = this;
				var _data = {
					amount: this.amount,
					way:this.value
				}

				if (!this.amount) {
					Toast('请输入充值金额');
				}

				request.getInstance().postData('api/account/charge', _data)
					.then((res) => {
						location.href=res.data.data.redirect_url;
					})
					.catch((err) => {
						Toast(err.data.msg);
					})
			},
			// watch: {
			// 	"choiseValue": 'hideTab'
			// },
			selWay(){
				request.getInstance().getData('api/account/pay-methods/unknown/2')
					.then((res) => {
						this.setBankList(res);
					})
					.catch((err) => {
						Toast(err.data.msg);
					})
			},
			setBankList(res) {
				var _tempList = [];
				for (let i = 0; i < res.data.data.methods.length; i++) {
					var _t = {};
					_t.value = res.data.data.methods[i].id.toString();
					_t.label = res.data.data.methods[i].label;
					_tempList.push(_t);
				}
					console.log(_tempList);
				this.options1 = _tempList;
			}
		}
	};
</script>

<style lang="scss" scoped>
	@import "../../../sass/oo_flex.scss";
	.recharge-container {
		background: #eee;
		height: 100vh;
		padding-top: 2em;
		box-sizing: border-box;
	}

	.recharge-box {
		background: #fff;
		padding: 1em;
		margin: 0 0.5em;
		.tltle {
			font-size: 1em;
			color: #999;
		}
	}

	.recharge-money {
		border-bottom: 1px solid #ccc;
		vertical-align: middle;
		margin-top: 2em;
		font-size: 1.2em;
		padding: 0.2em 0;
		input {
			border: none;
			outline: none;
			width: 100%;
			font-size: 0.9em;
		}
	}

	.recharge-way {
		margin-top: 2em;
	}

	.recharge-btn {
		display: block;
		margin-top: 3em;
		margin-bottom: 1em;
	}
</style>