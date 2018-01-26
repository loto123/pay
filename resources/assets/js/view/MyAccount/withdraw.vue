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
					<ul class="pet-list flex flex-justify-around">
						<li class="flex flex-align-center flex-justify-center active">
							<img src="/images/avatar.jpg">
						</li>
						<li class="flex flex-align-center flex-justify-center">
							<img src="/images/avatar.jpg">
						</li>
						<li class="flex flex-align-center flex-justify-center">
							<img src="/images/avatar.jpg">
						</li>
					</ul>

					<div class="look-more">
						<mt-button type="primary" size="large" style="height: 100%;" @click="lookMore">查看更多</mt-button>
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
					<li v-for="(item,index) in priceList">￥{{item}}
					</li>
				</ul>
				<div class="high-price flex flex-align-center flex-justify-center">¥{{my_max_quota}}(最高价)</div>
			</div>
			<div class="usable-diamond">拥有钻石{{balance}}，出售消耗钻石<span>100.00</span></div>
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
						<li class="flex flex-align-center flex-justify-center active">
							<img src="/images/avatar.jpg" alt="">
						</li>
						<li class="flex flex-align-center flex-justify-center">
							<img src="/images/avatar.jpg" alt="">
						</li>
						<li class="flex flex-align-center flex-justify-center">
							<img src="/images/avatar.jpg" alt="">
						</li>
						<li class="flex flex-align-center flex-justify-center">
							<img src="/images/avatar.jpg" alt="">
						</li>
						<li class="flex flex-align-center flex-justify-center">
							<img src="/images/avatar.jpg" alt="">
						</li>
						<li class="flex flex-align-center flex-justify-center">
							<img src="/images/avatar.jpg" alt="">
						</li>
						<li class="flex flex-align-center flex-justify-center">
							<img src="/images/avatar.jpg" alt="">
						</li>
						<li class="flex flex-align-center flex-justify-center">
							<img src="/images/avatar.jpg" alt="">
						</li>
						<li class="flex flex-align-center flex-justify-center">
							<img src="/images/avatar.jpg" alt="">
						</li>
						<li class="flex flex-align-center flex-justify-center">
							<img src="/images/avatar.jpg" alt="">
						</li>
						<li class="flex flex-align-center flex-justify-center">
							<img src="/images/avatar.jpg" alt="">
						</li>
					</ul>
				</div>

				<div class="comfirm-button">
					<mt-button type="primary" size="large">确定</mt-button>
				</div>

			</div>
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
				dataList:[],

				cardOptions: [],
				way: null,	//提现方式
				value: null,
				has_pay_password: null,//是否设置支付密码
				balance:null,		//最高价
				my_max_quota:null,
				fee_value: null,
				isFee: false,//是否展示手续费
				isShow:false,

				isPopDetailShow:false,  // 查看更多显示

				priceList:[]	//价格列表
			}
		},
		created() {
			this.init();
		},
		computed: {

		},
		components: { topBack, passWorld },
		methods: {
			openOption() {
				console.log("打开了选项");
				// this.$router.push('/index');
			},
			hidePassword() {
				this.showPasswordTag = false;
			},
			init() {
				Loading.getInstance().open("加载中...");
				// /account/withdraw-methods
				Promise.all([
					request.getInstance().getData("api/pet/sellable"),
					request.getInstance().getData("api/account"), 
					request.getInstance().getData('api/account/withdraw-methods')
					])
					.then((res) => {
						this.balance=res[1].data.data.balance;
						this.has_pay_password = res[1].data.data.has_pay_password;

						this.dataList = res[2].data.data.methods
						this.setBankList(res[2]);//获取提现方式列表
						Loading.getInstance().close();
					})
					.catch((err) => {
						console.error(err);
						Toast(err.data.msg);
					})
			},
			setBankList(res) {
				console.log(res);
				var _tempList = [];
				for (let i = 0; i < res.data.data.methods.length; i++) {
					var _t = {};
					_t.value = res.data.data.methods[i].id.toString();
					_t.label = res.data.data.methods[i].label;
					_tempList.push(_t);
				}
				this.cardOptions = _tempList;
				this.value = this.cardOptions[0].value;
				console.log(this.cardOptions);
				this.isShow = true;
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
			check(){  
				for(var i = 0 ; i < this.dataList.length; i++){
					if(this.value == this.dataList[i].id){
						this.priceList = this.dataList[i].quota_list;
					}
				}
			},

			closePanel(){
				this.isPopDetailShow = false;
			},

			lookMore(){
				this.isPopDetailShow = true;
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
	.withdraw-container {
		background: #eee;
		height: 100vh;
		padding-top: 2em;
		box-sizing: border-box;
	}
	
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
			.pet-list{
				padding-top:0.5em;
				padding-bottom: 0.5em;
				box-sizing: border-box;
				li{
					width: 4em;
					height: 4em;
					border-radius: 0.2em;
					box-sizing: border-box;
					border:1px solid #eee;

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