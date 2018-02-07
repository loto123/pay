<template>
	<div id="addBankCard">
		<topBack title="添加银行卡" style="background:#eee;"></topBack>
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
			<a class="mint-cell mint-field">
				<div class="mint-cell-left"></div>
				<div class="mint-cell-wrapper">
					<div class="mint-cell-title">
						<span class="mint-cell-text">所在地区</span>
					</div>
					<div class="mint-cell-value">
						<div class="mint-cell-value is-link" @click="choiceArea">
							<input placeholder="请选择省市" type="text" class="mint-field-core text-right" readonly="readonly" v-model="areaText">
							<div class="mint-field-clear" style="display: none;">
								<i class="mintui mintui-field-error"></i>
							</div>
						</div>
					</div>
					<i class="mint-cell-allow-right"></i>
					<mt-popup v-model="popupVisible" position="bottom" class="mint-popup-4" style="width:100%;">
						<div class="picker-toolbar">
							<span class="mint-datetime-action mint-datetime-cancel" @click="cancel">取消</span>
							<span class="mint-datetime-action mint-datetime-confirm" @click="selectaddress">确定</span>
						</div>
						<div class="page-picker-wrapper" v-if="pickerShow">
							<mt-picker :slots="addressSlots" @change="onAddressChange" :visible-item-count="5"></mt-picker>
						</div>
					</mt-popup>
				</div>
			</a>
			<div class="bank-info flex flex-v flex-justify-center">
				<div class="select-wrap flex flex-align-center" @click="showDropList">
					<div class="title">所属银行</div>
					<div class="sel-bank">
						{{dealShop?dealShop:'请选择银行卡所属银行'}}
					</div>
				</div>
				<mt-field label="银行卡号" placeholder="请填写银行卡号" type="number" v-model="card_num"></mt-field>
			</div>
			<div class="flex flex-v flex-justify-center">
				<section class="mobile-container">
					<div class="mobile-box flex flex-align-center">
						<span>手机号:</span>
						<em class="flex-1 number">{{mobile}}</em>
					</div>
				</section>
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

		<inputList :showSwitch="dropListSwitch" v-on:hideDropList="hideDropList" :optionsList="shopList" title="请选择银行">
		</inputList>
	</div>
</template>


<script>
	import request from '../../utils/userRequest';
	import topBack from "../../components/topBack";
	import inputList from "../../components/inputList";
	import { MessageBox, Toast, Picker, Popup } from "mint-ui";
	import Loading from '../../utils/loading'

	export default {
		data() {
			return {
				pickerShow: false,
				dropListSwitch: false,
				shopList: null,
				name: null,
				id_number: null,
				dealShop: null,
				mobile: null,
				card_num: null,
				bank_id: null,
				code: null,
				popupVisible: false,  //地址弹框

				computedTime: null,		//短信验证码倒计时
				addressSlots: null,
				address: null,
				addressSlots: [],
				areaPicker: '',
				areaText: '',	
				province: '',//省
				city: ''			//市

			}
		},
		components: { topBack, inputList },
		created() {
			this.personalInfo();

			this.initArea();

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
						this.mobile = res.data.data.mobile;
						Loading.getInstance().close();
					})
					.catch((err) => {
						Toast(err.data.msg);
					})
			},

			init() {
				Loading.getInstance().open();
				request
					.getInstance()
					.getData("api/card/getBanks")
					.then(res => {
						this.setBankList(res);
						Loading.getInstance().close();
					})
					.catch(err => {
						Toast(err.data.msg);
						Loading.getInstance().close();
					});
			},
			initArea() {
				var self = this;
				Loading.getInstance().open();
				request.getInstance().getData("api/card/getBankCardParams").then(res => {
					this.address = res.data.data;
					this.addressSlots = [
						{
							flex: 1,
							values: Object.keys(this.address),
							className: 'slot1',
							textAlign: 'center'
						},
						{
							divider: true,
							content: '-',
							className: 'slot2'
						}, {
							flex: 1,
							values: Object.values(this.address)[0],
							className: 'slot3',
							textAlign: 'center'
						}
					];
					this.pickerShow = true;
					Loading.getInstance().close();
				})
					.catch(err => {
						Toast(err.data.msg);
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
					province:this.province,
					city:this.city,
					code: this.code
				}
				if (!this.areaText) {
					Toast("请选择省市");
					return
				}else if (this.shopId == null) {
					Toast("请选择银行卡所属银行");
					return
				} else if (!this.card_num) {
					Toast("请填写银行卡号");
					return
				}else if (!this.code) {
					Toast("请输入验证码");
					return
				}

				Loading.getInstance().open();
				request.getInstance().postData("api/card/create", _data).then(res => {
					Toast('添加成功');
					this.$router.push('/my/bankCardManage');
					Loading.getInstance().close();
				}).catch(err => {
					Toast(err.data.msg);
					Loading.getInstance().close();
				});
			},
			//短信验证码
			sendYZM() {
				if(this.computedTime !=null){
					return;
				}
				var _data = {};
				_data.mobile = this.mobile;

				if (!this.mobile) {
					Toast("请填写银行卡预留手机号");
					return
				}
				this.computedTime = 60;
				var timer = setInterval(() => {
					this.computedTime--;
					if (this.computedTime == 0) {
						this.computedTime=null;
						clearInterval(timer);
					}
				}, 1000)
				
				Loading.getInstance().open();
				request.getInstance().postData("api/auth/sms", _data).then((res) => {
					Loading.getInstance().close();
				}).catch((err) => {
					Toast(err.data.msg);
					Loading.getInstance().close();
				})
			},
			onAddressChange(picker, values) {
				this.areaPicker = picker
				picker.setSlotValues(1, this.address[values[0]]); // 区/县数据就是一个数组
				this.addressProvince = values[0];
				this.addressCity = values[1];
			},
			choiceArea: function () {
				this.popupVisible = true;
				// 设置默认选中  
				if (this.province !== '' && this.city !== '') {
					this.areaPicker.setSlotValue(0, this.province)
					this.areaPicker.setSlotValue(1, this.city)
				}
			},
			cancel() {
				this.popupVisible = false;
				this.areaPicker.setSlotValue(0, this.province)
				this.areaPicker.setSlotValue(1, this.city)
			},
			selectaddress() {
				this.popupVisible = false
				this.province = this.addressProvince
				this.city = this.addressCity
				this.areaText = this.province + this.city
			},
		}
	};
</script>

<style lang="scss" scoped>
	#addBankCard {
		background: #eee;
		height: 100vh;
		padding-top: 2em;
		box-sizing: border-box;
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
			.mint-cell {
				background-image: none;
			}
		}
	}

	.affirm-add {
		display: block;
		width: 100%;
		margin: 2em auto;
	}

	.account-container {
		background: #fff;
		padding-left: 10px;
		.account-box {
			height: 3em;
			border-top: 1px solid #d9d9d9;
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

	.mobile-container {
		background: #fff;
		padding-left: 10px;
		.mobile-box {
			height: 3em;
			border-top: 1px solid #d9d9d9;
			border-bottom: 1px solid #d9d9d9;
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
		.title {
			width: 105px;
		}
		.sel-bank {
			color: #666;
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