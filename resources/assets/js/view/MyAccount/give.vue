<template>
	<div id="give" class="give-container">
		<topBack title="转钻到公会">
			<div class="flex flex-reverse" style="width:100%;padding-right:1em;box-sizing:border-box;" @click="goIndex">
				<i class="iconfont" style="font-size:1.4em;">&#xe602;</i>
			</div>
		</topBack>
		<div class="give-box">
			<div class="title">转移钻石</div>
			<div class="give-money flex flex-justify-center">
				<input type="number" placeholder="请输入钻石" v-model="amount">
			</div>
			<div class="all-money flex">
				<div class="money">可转钻石 ¥<span>{{balance}}</span>,</div>
				<a href="javascript:;" class="all-giveAcc" @click="allGive">全部转移</a>
			</div>
			<div class="select-wrap flex flex-align-center" @click="showDropList">

				{{dealShop?dealShop:'请选择您要转钻的公会'}}

			</div>
			<a href="javascript:;" class="transAcc-btn" @click="giveBtn">
				<mt-button type="primary" size="large">转移</mt-button>
			</a>
		</div>
		<inputList :showSwitch="dropListSwitch" v-on:hideDropList="hideDropList" :optionsList="shopList" title="请选择您要转钻的公会"></inputList>
		<passWorld :setSwitch="showPasswordTag" v-on:hidePassword="hidePassword" v-on:callBack="callBack"></passWorld>
	</div>
</template>

<script>
	import request from '../../utils/userRequest';
	import topBack from "../../components/topBack.vue";
	import inputList from "../../components/inputList";
	import passWorld from "../../components/password"

	import Loading from "../../utils/loading";
	import {Toast} from 'mint-ui'

	export default {
		created() {
			this.init();
		},
		data() {
			return {
				dropListSwitch: false,       //下拉框开关
				showPasswordTag:false,
				dealShop: null,
				shopList: null,
				balance:null,  	            //可转账

				shopId: null,	            //公会ID
				amount:null, 	            //提现money
				has_pay_password:null,	    //是否设置支付密码
				shop_id:null
			};
		},
		components: { topBack, inputList,passWorld},
		methods: {
			goIndex() {
				this.$router.push("/index");
			},
			allGive(){ //全部转账
				this.amount=this.balance;
			},
			init() {
				Loading.getInstance().open();
				Promise.all([request.getInstance().getData("api/account"),request.getInstance().getData("api/shop/lists/mine")])
					.then(res => {
						this.setShopList(res[1]);
						this.balance=res[0].data.data.balance;
						this.has_pay_password=res[0].data.data.has_pay_password;
						Loading.getInstance().close();
					})
					.catch(err => {
						Toast(err.data.msg);
						Loading.getInstance().close();
					});
			},

			setShopList(res) {
				var _tempList = [];
				for (let i = 0; i < res.data.data.data.length; i++) {
					var _t = {};
					_t.value = res.data.data.data[i].id.toString();
					_t.label = res.data.data.data[i].name;
					_tempList.push(_t);
				}

				this.shopList = _tempList;
			},

			getShopName(id) {
				for (let i = 0; i < this.shopList.length; i++) {
					if (this.shopList[i].value == id) {
						return this.shopList[i].label;
					}
				}
				return "没有这个公会";
			},

			showDropList() {
				this.dropListSwitch = true;
			},
			hideDropList(data) {
				this.dropListSwitch = false;
				this.dealShop = this.getShopName(data);

				this.shopId = data;
			},
			hidePassword() {
				this.showPasswordTag = false;
			},
			giveBtn(){
				var self = this;
				var _data = {
					amount: this.amount
				}

				if (this.amount<=0) {
					Toast('请输入转账钻石数量');
					return
				}else if(this.amount>this.balance){
					Toast('余额不足');
					return
				}else if (!this.shopId) {
					Toast('请选择公会');
					return
				}
				
				if (this.has_pay_password==0) {
					this.$router.push('/my/setting_password');//跳转到设置支付密码
				}else{
					this.showPasswordTag = true;   //密码层弹出
				}
			},
			//支付密码验证
			callBack(password){
				var temp = {};
				temp.password=password;
				var _data = {
					amount: this.amount,
					shop_id :this.shopId,
					password:password
				}

				Promise.all([request.getInstance().postData('api/my/pay_password',temp),request.getInstance().postData('api/account/transfer', _data)])
				.then((res) => {
					Toast('转账成功');
					this.$router.push('/myAccount');
				})
				.catch((err) => {
					Toast(err.data.msg);
				})
			}

		}
	};
</script>

<style lang="scss" scoped>
	@import "../../../sass/oo_flex.scss";
	.give-container {
		background: #eee;
		height: 100vh;
		padding-top: 2em;
		box-sizing: border-box;
	}

	.give-box {
		background: #fff;
		padding: 1em;
		margin: 0 0.5em;
		.tltle {
			font-size: 1em;
			color: #999;
		}
	}

	.give-money {
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

	.all-money {
		margin-top: 1em;
		.money {
			color: #666;
		}
		.all-giveAcc {
			color: #199ed8;
			margin-left: 0.4em;
		}
	}

	.select-wrap {
		width: 100%;
		margin: 0 auto;
		height: 2.5em;
		padding-left: 1em;
		box-sizing: border-box;
		margin-top: 2em;
		background: #fff;
		border: 1px solid #ccc;
	}

	.transAcc-btn {
		display: block;
		margin-top: 3em;
		margin-bottom: 1em;
	}
</style>