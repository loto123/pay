<template>
	<div id="withdraw" class="withdraw-container">
		<topBack title="出售" style="background: #eee;">
			<div class="flex flex-reverse" style="width:100%;padding-right:1em;box-sizing:border-box;" @click="goIndex">
				<i class="iconfont" style="font-size:1.4em;">&#xe602;</i>
			</div>
		</topBack>
		<div class="withdraw-box">
			<div class="price-list-box">
				<div class="tltle">出售价格</div>
				<ul class="price-list flex flex-wrap-on flex-justify-between">
					<li class="active">￥100</li>
					<li>￥100</li>
					<li>￥100</li>
					<li>￥100</li>
					<li>￥100</li>
					<li>￥100</li>
					<li>￥9000</li>
				</ul>
				<div class="high-price flex flex-align-center flex-justify-center">¥8000(最高价)</div>
			</div>
			<div class="usable-diamond">拥有钻石 8000.00，出售消耗钻石<span>100.00</span></div>
			<div class="withdraw-way">
				<div class="title">收款方式</div>
				<div class="list-wrap">
					<mt-radio align="right" title="" v-model="value" :options="options1">
					</mt-radio>
				</div>
			</div>
			<a href="javascript:;" class="withdraw-btn" @click="withdrawBtn">
				<mt-button type="primary" size="large">出售</mt-button>
			</a>
		</div>
		<passWorld :setSwitch="showPasswordTag" v-on:hidePassword="hidePassword" v-on:callBack="callBack"></passWorld>
	</div>
</template>

<script>
	import request from '../../utils/userRequest';
	import topBack from '../../components/topBack.vue'
	import passWorld from "../../components/password"
	import Loading from '../../utils/loading'
	import { MessageBox, Toast } from "mint-ui";

	export default {
		data() {
			return {
				showPasswordTag: false,       // 密码弹出开关

				options1: [],
				way: null,	//提现方式
				value: null,
				has_pay_password: null,//是否设置支付密码
				fee_mode: null,			//提现状态  1代表固定手续费   0代表百分比
				fee_value: null,
				isFee: false,//是否展示手续费
			}
		},
		created() {
			this.init();
		},
		computed: {

		},
		components: { topBack, passWorld },
		methods: {
			goIndex() {
				this.$router.push('/index');
			},
			hidePassword() {
				this.showPasswordTag = false;
			},
			init() {
				Loading.getInstance().open("加载中...");

				Promise.all([request.getInstance().getData("api/account"), request.getInstance().getData('api/account/withdraw-methods')])
					.then((res) => {
						this.has_pay_password = res[0].data.data.has_pay_password;
						this.setBankList(res[1]);//获取提现方式列表
						Loading.getInstance().close();
					})
					.catch((err) => {
						Toast(err.data.msg);
					})
			},
			withdrawBtn() {
				var self = this;
				//成功内容
				var _data = {
					way: this.value
				}

				if (!this.value) {
					Toast('请选择支付方式');
					return
				}
				if (this.has_pay_password == 0) {
					this.$router.push('/my/setting_password');//跳转到设置支付密码
				} else {
					this.showPasswordTag = true;   //密码层弹出
				}
			},
			//支付密码验证
			callBack(password) {
				var temp = {};
				temp.password = password;
				var _data = {
					way: this.value,
					password: password
				}
				Promise.all([request.getInstance().postData('api/my/pay_password', temp), request.getInstance().postData('api/account/withdraw', _data)])
					.then((res) => {
						Toast('提现成功');
						this.$router.push('/myAccount');

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
					this.fee_mode = res.data.data.methods[i].fee_mode;
					this.fee_value = res.data.data.methods[i].fee_value;
					console.log(this.fee_value);
					_tempList.push(_t);
				}
				this.options1 = _tempList;
			}
		}
	};
</script>

<style lang="scss" scoped>
	@import "../../../sass/oo_flex.scss";
	.withdraw-container {
		background: #eee;
		height: 100vh;
		padding-top: 2em;
		box-sizing: border-box;
	}

	.withdraw-box {
		background: #fff;
		padding: 1em;
		margin: 0 0.5em;
		.tltle {
			font-size: 1em;
			color: #999;
		}
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
		.high-price{
			height: 3em;
			width: 100%;
			border:1px solid #ddd;
			border-radius: 5px;
			margin-top:0.8em;
		}
	}
	.usable-diamond{
		width: 100%;
		margin-top:0.8em;
		span{
			color:blue;
		}	
	}
	.withdraw-way {
		margin-top: 2em;
		.title {
			color: #999;
			margin-bottom: 0.5em;
		}
	}

	.withdraw-btn {
		display: block;
		margin-top: 3em;
		margin-bottom: 1em;
	}
</style>