<template>
	<div id="purchase" class="purchase-container">
		<topBack title="购买" style="background: #eee;">
			<div class="flex flex-reverse" style="width:100%;padding-right:1em;box-sizing:border-box;" @click="goIndex">
				<i class="iconfont" style="font-size:1.4em;">&#xe602;</i>
			</div>
		</topBack>
		<div class="purchase-box">
			<div class="price-list-box">
				<div class="tltle">选择要购买的宠物价格</div>
				<ul class="price-list flex flex-wrap-on flex-justify-between">
					<li class="active">￥100</li>
					<li>￥100</li>
					<li>￥100</li>
					<li>￥100</li>
					<li>￥100</li>
					<li>￥100</li>
					<li>￥9000</li>
				</ul>
			</div>

			<div class="pet-list-box">
				<div class="header flex flex-align-center">
					<div class="title">符合购买价格的宠物</div>
					<div class="query-btn">
						<mt-button type="primary" size="small">查询</mt-button>
					</div>
				</div>
				<ul class="pet-list">
					<li>231</li>
				</ul>	
				
			</div>


			<div class="purchase-way">
				<div class="title">选择购买方式</div>
				<div class="list-wrap">
					<mt-radio align="right" title="" v-model="value" :options="options1">
					</mt-radio>
				</div>
			</div>
			<a href="javascript:;" class="purchase-btn" @click="purchaseBtn">
				<mt-button type="primary" size="large">购买</mt-button>
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
				options1: [],
				way: null,
				value: null
			}
		},
		created() {
			this.init();
		},
		components: { topBack },
		props: ["showSwitch", "optionsList"],
		methods: {
			goIndex() {
				this.$router.push("/index");
			},
			purchaseBtn() {
				var self = this;
				var _data = {
					way: this.value
				}
				request.getInstance().postData('api/account/charge', _data)
					.then((res) => {
						location.href = res.data.data.redirect_url;
					})
					.catch((err) => {
						Toast(err.data.msg);
					})
			},
			init() {
				Promise.all([request.getInstance().getData('api/account/pay-methods/unknown/2'),request.getInstance().getData('api/account/deposit_quota')])
					.then((res) => {
						this.setPurchaseList(res[0]);
						console.log(res[1]);
					})
					.catch((err) => {
						Toast(err.data.msg);
					})
			},
			setPurchaseList(res) {
				var _tempList = [];
				for (let i = 0; i < res.data.data.methods.length; i++) {
					var _t = {};
					_t.value = res.data.data.methods[i].id.toString();
					_t.label = res.data.data.methods[i].label;
					_tempList.push(_t);
				}
				this.options1 = _tempList;
			}
		}
	};
</script>

<style lang="scss" scoped>
	@import "../../../sass/oo_flex.scss";
	.purchase-container {
		background: #eee;
		height: 100vh;
		padding-top: 2em;
		box-sizing: border-box;
	}

	.purchase-box {
		background: #fff;
		padding: 1em;
		margin: 0 0.5em;
	}

	.price-list-box {
		.tltle {
			color: #666;
		}
		.price-list {
			width: 100%;
			overflow: hidden;
			li {
				width: 28%;
				height: 2.5em;
				line-height: 2.5em;
				border: 1px solid #ccc;
				float: left;
				border-radius: 5px;
				margin-top: 1em;
				text-align: center;
				&:nth-child(3n+1) {
					margin-left: 0;
				}
			}
			.active{
				color: #00CC00;
				border: 1px solid #00CC00;
			}
		}
	}
	.pet-list-box{
		margin-top:1em;
		.header{
			margin-bottom:0.3em;
			.title{
				margin-right:0.5em;
				color:#666;
			}
		}
		.pet-list{
			height: 5em;
			border: 1px solid #ccc;
			width: 100%;
		}
	}
	.purchase-way {
		margin-top: 2em;
	}

	.purchase-btn {
		display: block;
		margin-top: 3em;
		margin-bottom: 1em;
	}
</style>