<template>
	<div id="withdraw" class="withdraw-container">
		<topBack title="出售" style="background: #eee;">
			<div class="flex flex-reverse" style="width:100%;padding-right:1em;box-sizing:border-box;" @click="openOption">
				<i class="iconfont" style="font-size:1.4em;">&#xe7fe;</i>
			</div>
		</topBack>
		<div class="withdraw-box">
			<div class="flex flex-v flex-align-start">
				<div class="tltle">选择要出售的宠物：</div>
				<div class="pet-list-box">

					<ul class="pet-list flex flex-justify-start flex-wrap-on" v-if="isShow && petsList.length!=0">
						<li class="flex flex-align-center flex-justify-center " v-for="item in petsList" v-bind:class="{active:item.isChecked}" @click ="setActive(item.id,item.is_egg)">
							<img :src="item.pic?item.pic:'/images/egg.jpg'">
						</li>
					</ul>
					
					<div class="notice flex flex-v flex-align-center " v-if="isShow && petsList.length==0">
						<h3>当前无宠物可售</h3>
						<p>（每天可免费领取3次宠物蛋）</p>
					</div>

					<div class="look-more" v-if="isShow && petsList.length!=0">
						<mt-button type="primary" size="large" style="height: 100%;" @click="lookMore">查看更多</mt-button>
					</div>

					<div class="look-more" v-if="isShow && petsList.length==0">
						<mt-button type="primary" size="large" style="height: 100%;" @click="showGetEggsPop">免费领取宠物蛋(剩余:{{getEggsTimes}}次)</mt-button>
					</div>
				</div>

				<!-- <div>
					<div>
						免费领取宠物蛋（剩余：3次）
					</div>
				</div> -->
			</div>
			<div class="price-list-box">
				<div class="tltle">出售价格</div>
				<ul class="price-list flex flex-wrap-on">
					<li v-for="item in priceList" @click="choiseSalePrice(item.price)" v-bind:class="{active:item.isChecked == true}" >￥{{item.price}}
					</li>
				</ul>

				<div class="high-price flex flex-align-center flex-justify-center" @click="choiseMaxQuoto" v-bind:class="{active:isMaxQuota}" v-if="balance!=0">¥{{myMaxQuota}}(最高价)</div>

			</div>
			<div class="usable-diamond">拥有钻石{{balance}}，出售消耗钻石<span>{{amount}}</span></div>
			<div class="withdraw-way">
				<div class="title">收款方式</div>
				<div class="list-wrap">
					<mt-radio align="right" v-model="value" :options="cardOptions" v-if="isShow">
					</mt-radio>
				</div>
			</div>
			<a href="javascript:;" class="withdraw-btn" @click="withdrawBtn">
				<mt-button type="primary" size="large">出售</mt-button>
			</a>
		</div>
		
		<!-- 更多宠物弹窗 -->
		<div class="popDetail flex flex-align-center flex-justify-center" v-if="isPopDetailShow">
			<div class="mask" @touchmove.stop.prevent></div>
			<div class="content flex flex-v flex-align-center">
				<div class="title flex flex-align-center flex-justify-center" @touchmove.stop.prevent>
					<span class="flex-1"></span>
					<h3 class="flex-8">选择要出售的宠物</h3>
					<span class="flex-1">
						<i class="iconfont" style="font-size: 1.2em;" @click="closePanel">&#xe604;</i>
					</span>
				</div>

				<div class="pets">
					<ul class="flex flex-wrap-on">
						<li class="flex flex-align-center flex-justify-center" v-for="item in petsList" v-bind:class="{active:item.isChecked}" @click ="setActive(item.id,item.is_egg)">
							<img :src="item.pic?item.pic:'/images/egg.jpg'" alt="">
						</li>
					</ul>
				</div>

				<div class="comfirm-button" >
					<mt-button type="primary" size="large" @click="closePanel">确定</mt-button>
				</div>

			</div>
		</div>
		
		<!-- 领取宠物蛋弹窗 -->
		<div class="popGetEggs flex flex-v flex-align-center" v-if="isPopGetEggsShow" @touchmove.stop.prevent>
			<div class="imgWrap">
				<img src="/images/egg.jpg" alt="">
			</div>
			<h3>
				{{broodInfo}}
			</h3>
			<div class="comfirm-button">
				<mt-button type="primary" size="large" @click="getEggs" v-if='!isPopBroodEggs' :disabled="isBroodClick">领取</mt-button>
				<mt-button type="primary" size="large" @click="broodEggs" v-else :disabled="isBroodClick">孵化</mt-button>
			</div>
			
		</div>
		
		<mt-actionsheet :actions="actions" v-model="sheetVisible"></mt-actionsheet>

		<!-- 提交后的付款信息 -->
		<div class="pay-info-detail flex flex-v flex-align-center" v-if="isPayInfoDetailShow">
			<div class="top-wrap"></div>
			<div class="bottom-wrap">
				<ul>
					<li class="flex flex-align-center">
						<span class="flex-1 ">出售价格</span>
						<span class="flex-1 flex flex-justify-end">{{amount}}钻</span>
					</li>
					<li class="flex flex-align-center">
						<span class="flex-1">手续费</span>
						<span class="flex-1 flex flex-justify-end">{{fee}}钻</span>
					</li>
					<li class="flex flex-align-center">
						<span class="flex-1">到账银行卡</span>
						<span class="flex-1 flex flex-justify-end">{{bankInfo}}</span>
					</li>
				</ul>
			</div>
			<div class="button-wrap">
				<mt-button type="primary" size="large" @click="closePayInfoDetail">完成</mt-button>
			</div>
		</div>

		<passWorld :setSwitch="showPasswordTag" v-on:hidePassword="hidePassword" v-on:callBack="callBack">
			<div class="withdraw-info flex flex-v flex-align-center">
				<div class="title">出售</div>
				<div class="price">￥{{amount}}</div>
				<div class="notice">额外扣除{{fee}}元/次 手续费</div>
			</div>
		</passWorld>
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
				showPasswordTag: false,         // 密码弹出开关
				dataList:[],                    // 支付方式数据数组
				petsList:[],                    // 宠物数组
				cardOptions: [],

				way: null,	                    // 提现方式
				value: null,
				has_pay_password: null,         // 是否设置支付密码
				balance:null,		            // 最高价
				fee_value: null,
				isFee: false,                   // 是否展示手续费
				isShow:false,
				isPayInfoDetailShow:false,      // 付款后的提示

				getEggsTimes:0,
				isPopDetailShow:false,          // 查看更多显示
				isPopGetEggsShow:false,         // 领取宠物蛋弹窗显示
				isPopBroodEggs:false,		    // 是否孵蛋
				broodInfo:null,					// 孵蛋提示信息
				amount:null,                    // 提交的价格
				petId:null,						// 宠物id
				myMaxQuota:0,                   // 玩家可以提现的最高价格
				isMaxQuota:false,               // 是否选中了最高价格
				priceList:[],	                // 价格列表
				sheetVisible:false,
				has_pay_card:0,                 // 是否绑定了银行卡
				actions:[],                     // 右上角动作列表
				isBroodClick:false,             // 孵化按钮防止连续点击
				fee_mode:0,                     // 手续费支付方式  0 为百分比  1为单笔固定
				bankInfo:null,                  // 提现成功后的银行卡信息提示
				fee:1
			}
		},
		mounted(){
			this.actions = [{
		        name: '出售状态',
		        method: this.goStatus
	        }];
		},

		created() {
			this.init();
		},
		computed: {

		},
		components: { topBack, passWorld },
		methods: {
			openOption() {
				this.sheetVisible = true;
			},
			hidePassword() {
				this.showPasswordTag = false;
			},
			init() {
				Loading.getInstance().open("加载中...");
				this.petsList = []; // 清空狗狗数组
				this.petId = null;
				this.amount = null;
				this.broodInfo = null;
				// this.isBroodClick = null;

				Promise.all([
					request.getInstance().getData("api/pet/sellable"),
					request.getInstance().getData("api/account"), 
					request.getInstance().getData('api/account/withdraw-methods')
					])
					.then((res) => {

						for(var i = 0; i <res[0].data.data.list.length; i++){
							var _temp = res[0].data.data.list[i];
							_temp.isChecked = false;
							this.petsList.push(_temp);
						}
						if(this.petsList.length == 0){
							request.getInstance().getData("api/pet/egg_acquire_times").then(res=>{
								this.getEggsTimes = res.data.data.times;
							}).catch(err=>{
								Toast(err.data.msg);
							});
						}

						this.balance=res[1].data.data.balance;

						this.has_pay_password = res[1].data.data.has_pay_password;
						this.has_pay_card = res[1].data.data.has_pay_card;

						// this.fee_mode= res[2].data.data.fee_mode;
						// this.fee_value = res[2].data.data.fee_value;
						this.dataList = res[2].data.data.methods;
						this.setBankList(res[2]);//获取提现方式列表

						this.check();
						Loading.getInstance().close();
					})
					.catch((err) => {
						console.error(err);
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
				this.cardOptions = _tempList;
				this.value = this.cardOptions[0].value;
				this.isShow = true;
			},

			withdrawBtn() {
				var self = this;

				var _isUnBindCard = this.withdraw();

				if(_isUnBindCard == true){
					return;
				}

				if(this.amount == null){
					Toast("请输入出售金额");
					return;
				}

				if(this.petId == null){
					Toast("请选择出售的宠物");
					return;
				}

				//成功内容
				var _data = {
					way: this.value
				}
				console.log(this.fee_mode);
				if(this.fee_mode == 0){          // 百分比模式
					this.fee = this.amount * ((this.fee_value)/100)
				}else if(this.fee_mode == 1){    // 指定金额模式
					this.fee = this.fee_value;
				}

				console.log(this.fee);
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
					password: password,
					amount:this.amount,
					pet_id:this.petId
				}

				if(this.amount == null){
					Toast("请选择出售的价格");
					return;
				}

				Loading.getInstance().open();
				Promise.all([request.getInstance().postData('api/my/pay_password', temp), request.getInstance().postData('api/account/withdraw', _data)])
					.then((res) => {
						Loading.getInstance().close();
						this.fee  = res[1].data.data.fee;
						this.bankInfo = res[1].data.data.receiver.bank_name+" "+res[1].data.data.receiver.card_tail_number;
						console.log(this.fee);
						Toast('出售成功');
						this.isPayInfoDetailShow = true;
						this.hidePassword();
					})
					.catch((err) => {
						Loading.getInstance().close();
						this.hidePassword();
						console.error(err);
						Toast(err.data.msg);
					})
			},
			check(){  
				this.priceList = [];

				for(var i = 0 ; i < this.dataList.length; i++){
					if(this.value == this.dataList[i].id){

						// 获取最高价
						this.myMaxQuota = this.dataList[i].my_max_quota;
						this.fee_mode = this.dataList[i].fee_mode;
						this.fee_value = this.dataList[i].fee_value;

						for(var k = 0; k < this.dataList[i].quota_list.length; k++){
							var _temp =  {};
							_temp.price = this.dataList[i].quota_list[k];
							_temp.isChecked = false;
							this.priceList.push(_temp);
						}
					}
				}
				
			},

			closePanel(){
				this.isPopDetailShow = false;
			},

			lookMore(){
				this.isPopDetailShow = true;
			},

			// 选择狗狗 或者 蛋
			setActive(id,isegg){
				if(isegg == true){
					this.isPopBroodEggs = true;
					this.isPopGetEggsShow  = true;
				}

				for( var i = 0; i < this.petsList.length; i++){
					this.petsList[i].isChecked = false;

					if(this.petsList[i].id == id){
						this.petsList[i].isChecked = true;
						this.petId = id;
					}
				}
			},

			// 选择出售狗狗的价格
			choiseSalePrice(price){
				this.isMaxQuota = false;
				for(let j = 0; j<this.priceList.length; j ++){
					this.priceList[j].isChecked = false;
					if(price == this.priceList[j].price){
						this.priceList[j].isChecked = true;
						this.amount = this.priceList[j].price;
					}
				}
			},

			showGetEggsPop(){
				this.isPopGetEggsShow = true;
			},
			
			withdraw(){
				// 未绑定银行卡
                if(this.has_pay_card==0){
                    MessageBox.confirm("您还没有绑定银行卡,是否前往绑定！", "温馨提示").then(
                        () => {
                            this.$router.push('/my');
                        },
                        () => {

                        }
                    );

                    return true;
                }else{

                	// 已绑定银行卡
                    this.$router.push('/myAccount/withdraw')
                }

            },

			// 免费领取宠物蛋 
			getEggs(){
				
				this.isBroodClick = true;

				request.getInstance().postData("api/pet/acquire_egg").then(res=>{
					this.isPopGetEggsShow = false;
					this.init();
					
				}).catch(err=>{
					Toast(err.data.msg);
					this.isPopGetEggsShow = false;
				});

				setTimeout(()=>{
					this.isBroodClick = false;
				},3500);
			},

			// 孵化宠物蛋
			broodEggs(){
				this.broodInfo =null;
				var _data = {
					egg_id:this.petId
				};

				// 禁止连续点击
				this.isBroodClick = true;
			
				request.getInstance().postData('api/pet/brood',_data).then(res=>{
						this.broodInfo = "正在孵化中";
						return Promise.resolve();
					}).then(res=>{
						var goLoop = ()=>{
								var timer = setTimeout(()=>{
								var _data = {
									pet_id :this.petId
								};
								
								request.getInstance().postData('api/pet/refresh_pet',_data).then(res=>{
									if(res.data.data.hatching == false){
										Toast("孵化成功");
										this.isPopBroodEggs =false;
										this.isPopGetEggsShow = false;
										this.isBroodClick = false;
										this.init();
									}else {
										goLoop();
										this.broodInfo = "正在孵化中";
									}
								}).catch(err=>{
									console.error(err);
								});
							},1000);
						}

						goLoop();
					})
					
					.catch(err=>{
						Toast(err.data.msg);
					});
			},

			goStatus(){
				this.$router.push("/myAccount/withdraw/status_list");
			},

			// 以最高价出售
			choiseMaxQuoto(){
				this.amount = this.myMaxQuota;
				this.isMaxQuota = true;
				for(var i = 0 ; i < this.priceList.length; i++){
					this.priceList[i].isChecked = false;
				}
			},

			// 关闭支付详情弹窗
			closePayInfoDetail(){
				this.isPayInfoDetailShow = false;
				this.init();
			}

		},
		watch:{
			value:function(){
				this.check();
			}
		}
	};
</script>

<style lang="scss" scoped>

	.pay-info-detail{
		padding-top:2em;
		position: fixed;
		width: 100%;
		height: 100vh;
		background: #fff;
		top:0;
		left: 0;

		.top-wrap{
			width:90%;
			height: 8em;
		}

		.bottom-wrap{
			border-top:1px solid #eee;
			width:90%;
			height: 8em;
			/*background: red;*/
			ul{
				li{
					width:100%;
					height: 2.5em;
				}
			}
		}

		.button-wrap{
			width:90%;
			margin-top:0.5em;
		}
	}


	/*密码提示*/
	.withdraw-info{
		width:100%;
		height: 5em;
		
		>div{
			margin-top: 0.5em;
		}
		.title{
			font-weight: bold;
		}
		.price{
			font-size: 1.3em;
		}
		.notice{
			color: #555;
		}
	}

	.withdraw-container {
		background: #eee;
		height: 100vh;
		padding-top: 2em;
		box-sizing: border-box;
	}
	
	/* 查看更多狗狗弹窗 */
	.popDetail{
		width: 100%;
		height: 100vh;
		position: fixed;
		top:0;
		left: 0;

		/*遮罩*/
		.mask{
			background: rgba(0,0,0,0.8);
			width: 100%;
			height: 100%;
			position: absolute;
			top:0;
			left: 0;
			z-index: 999;
		}

		.content{
			width: 95%;
			height: 24em;
			background: #fff;
			border-radius: 0.4em;
			z-index: 1000;
			.title{
				width: 100%;
				height: 3em;

				h3{
					text-align: center;
					font-size: 1.1em;
					font-weight: bold;
				}
			}

			.pets{
				width:100%;
				height: 16em;
				overflow-y:scroll;

				ul{
					li{
						box-sizing: border-box;
						width: 33%;
						margin-top:0.2em;

						>img{
							width:80%;
							border-radius:0.2em;
						}
					}

					.active{
						>img{
							border:2px solid #26a2ff;
						}
					}
				}
			}

			.comfirm-button{
				margin: 0 auto;
				margin-top:1em;
				width: 96%;
			}
		}
	}

	.popGetEggs{
		width: 100%;
		height: 100vh;
		position: fixed;
		top:0;
		left: 0;
		background: rgba(0,0,0,0.8);
		box-sizing: border-box;
		padding-top: 2em;
		
		.imgWrap{
			margin-top:5em;
		}

		.comfirm-button{
			margin: 0 auto;
			margin-top:1em;
			width: 96%;
		}

		h3{
			font-size:1.2em;
			color:#fff;
			text-align: center;
		}
	}

	.withdraw-box {
		background: #fff;
		padding-top: 1em;
		padding-bottom: 1em;
		padding-left: 0.8em;
		padding-right: 0.8em;
		box-sizing: border-box;
		.tltle {
			font-size: 1em;
			color: #999;
		}

		.pet-list-box{
			margin:0 auto;
			margin-top:0.5em;
			width: 98%;
			height: 8em;
			border:1px solid #eee;
			box-sizing: border-box;
			border-radius: 0.4em;
			
			.notice{
				height: 4em;
				padding-top:1em;
				box-sizing: border-box;

				h3{
					font-size: 1.2em;
					color:#555;
				}
				p{
					font-size:0.9em;
					color:#555;
				}
			}

			.pet-list{
				padding-top:0.5em;
				padding-bottom: 0.5em;
				box-sizing: border-box;
				width:100%;
				overflow: hidden;
				height: 5.6em;

				li{
					width: 4em;
					height: 4em;
					border-radius: 0.2em;
					box-sizing: border-box;
					border:1px solid #eee;
					margin-left: 1em;

					>img{
						display: block;
						width: 3.8em;
						height: 3.8em;
						border-radius:0.2em;
					}
				}
				
				/*是否被选中*/
				.active{
					border:1px solid #26a2ff;
				}
			}

			.look-more{
				height: 2em;
				width: 95%;
				margin: 0 auto;
			}
		}
	}

	.price-list-box {
		margin-top:0.8em;
		
		.tltle {
			color: #666;
		}

		.active{
			color: #00CC00;
			border: 1px solid #00CC00 !important;
		}

		.price-list {
			width: 100%;
			overflow: hidden;
			li {
				width: 28%;
				line-height: 2.5em;
				border: 1px solid #ccc;
				float: left;
				border-radius: 5px;
				margin-top: 1em;
				text-align: center;
				margin-left:5%;
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
		margin-top: 0.8em;
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