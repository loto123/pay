<template>
	<div id="addBankCard">
		<topBack title="添加银行卡"></topBack>
		<div class="addBankCard-box">
			<h2>请绑定持卡人本人的银行卡</h2>
			<div class="flex flex-v flex-justify-center">
				<section class="account-container">
					<div class="account-box flex flex-align-center">
						<span>姓名:</span>
						<em class="flex-1 number">{{name}}</em>
					</div>
					<div class="account-box flex flex-align-center">
						<span>身份证号:</span>
						<em class="flex-1 number">{{id_number}}</em>
					</div>
				</section>
			</div>
			<div class="bank-info flex flex-v flex-justify-center">
				
				<div class="select-wrap flex flex-align-center" @click="showDropList">
					<div class="title">所属银行</div>
					<div class="sel-bank">
						{{dealShop?dealShop:'请选择银行卡所属银行'}}
					</div>
					
				</div>
				<mt-field label="银行卡号" placeholder="请填写银行卡号" type="number" v-model="card_num"></mt-field>
			</div>
			<div class="bank-info flex flex-v flex-justify-center">
				<mt-field label="预留手机号" placeholder="请填写银行卡预留手机号" type="number" maxlength="11" v-model="mobile"></mt-field>
			</div>
			<section class="input-wrap-box">
				<div class="input-wrap flex flex-align-center">
					<span>验证码:</span>
					<input type="text" placeholder="请输入验证码" class="flex-1" v-model="code">
					<mt-button type="default" class="flex-1" @click="sendYZM">发送验证码{{computedTime?"("+computedTime+")":""}}</mt-button>
				</div>
			</section>
		</div>
		<a href="javascript:;" class="btn affirm-add" @click="affirmAdd()">
			<mt-button type="primary" size="large">确认</mt-button>
		</a>

		<inputList :showSwitch="dropListSwitch" v-on:hideDropList="hideDropList" :optionsList="shopList">
		</inputList>
	</div>
</template>


<script>
	import request from '../../utils/userRequest';
	import topBack from "../../components/topBack";
	import inputList from "../../components/inputList";
	import { MessageBox,Toast } from "mint-ui";

	import Loading from '../../utils/loading'

	export default {
		data() {
			return {
				dropListSwitch: false,
				shopList: null,
				name: null,
				id_number: null,
				dealShop: null,

				card_num: null,
				bank_id: null,
				mobile: null,
				code: null,

				computedTime:null 		//短信验证码倒计时
			}
		},
		components: { topBack, inputList },
		created() {
			this.personalInfo();
			this.init();
		},
		methods: {
			//个人信息
			personalInfo() {
				Loading.getInstance().open("加载中...");

				request.getInstance().getData("api/my/info")
					.then((res) => {
						this.name = res.data.data.name;
						this.id_number = res.data.data.id_number;
						Loading.getInstance().close();
					})
					.catch((err) => {
						console.error(err);
					})
			},

			init() {
				Loading.getInstance().open();
				request
					.getInstance()
					.getData("api/card/getBanks")
					.then(res => {
						console.log(res);
						this.setBankList(res);
						Loading.getInstance().close();
					})
					.catch(err => {
						console.error(err);
						Loading.getInstance().close();
					});
			},

			setBankList(res) {
				var _tempList = [];
				for (let i = 0; i < res.data.data.length; i++) {
					var _t = {};
					_t.value = res.data.data[i].id;
					_t.label = res.data.data[i].name;
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
				return "";
			},

			showDropList() {
				this.dropListSwitch = true;
			},
			hideDropList(data) {
				this.dropListSwitch = false;
				this.dealShop = this.getShopName(data);

				this.shopId = data;
			},
			affirmAdd() {
				var self = this;
				var _data = {
					bank_id: this.shopId,
					card_num: this.card_num,
					mobile: this.mobile,
					code: this.code
				}

				if(this.shopId == null){
					Toast("请选择银行卡所属银行");
					return 
				}else if(!this.card_num){
					Toast("请填写银行卡号");
					return 
				}else if(!this.mobile){
					Toast("请填写银行卡预留手机号");
					return 
				}else if(!this.code){
					Toast("请输入验证码");
					return 
				} 

				Loading.getInstance().open();
				request.getInstance().postData("api/card/create", _data).then(res => {
					Toast('添加成功');
					this.$router.push('/my/bankCardManage');	
					Loading.getInstance().close();
				}).catch(err => {
					console.error(err);
					Loading.getInstance().close();
				});
			},
			//短信验证码
			sendYZM() {
				var _temp = {};
				_temp.mobile = this.mobile;

				if(!this.mobile){
					Toast("请填写银行卡预留手机号");
					return 
				}
				request.getInstance().postData("api/auth/sms", _temp).then((res) => {
					this.computedTime = 60;
					this.timer = setInterval(() => {
						this.computedTime--;
						console.log(this.computedTime);
						if (this.computedTime == 0) {
							clearInterval(this.timer)
						}
					}, 1000)
				}).catch((err) => {
					console.log(err);
				})
			}
		}
	};
</script>

<style lang="scss" scoped>
	#addBankCard {
		background: #eee;
		height: 100vh;
		padding-top: 2em;
	}

	.addBankCard-box {
		border-top: 1px solid #ccc;
		h2 {
			color: #999;
			height: 2em;
			line-height: 2em;
			padding-left: 10px;
			padding-top: 0.7em;
		}
		.bank-info {
			margin-top: 1em;
		}
	}

	.affirm-add {
		display: block;
		margin-top: 1em;
		width: 96%;
		margin: auto;
		margin-top: 2em;
	}

	.account-container {
		background: #fff;
		.account-box {
			height: 3em;
			border-top: 1px solid #d9d9d9;
			padding-left: 10px;
			span {
				display: inline-block;
				width: 105px;
			}
			.number {
				color: #666;
				font-size: inherit;
			}
		}
	}
	.select-wrap {
		height: 2.5em;
		padding-left: 10px;
		box-sizing: border-box;
		margin-top: 0.5em;
		background: #fff;
		.title{
			width:105px;
		}
		.sel-bank{
			color:#666;
		}
	}
	.input-wrap-box {
		background: #fff;
		padding-left: 10px;
	}

	.input-wrap {
		width: 100%;
		height: 3em;
		span {
			display: inline-block;
			width: 105px;
		}
		.mint-button {
			font-size: 0.9em;
		}
		.mint-button--default {
			background: #fff;
		}
		input {
			border: none;
			outline: none;
			text-rendering: auto;
			color: initial;
			letter-spacing: normal;
			word-spacing: normal;
			text-transform: none;
			text-indent: 0px;
			text-shadow: none;
			display: inline-block;
			text-align: start;
			height: 2em;
			box-sizing: border-box;
			width: 20%;
			font-size: inherit;
		}
	}
</style>