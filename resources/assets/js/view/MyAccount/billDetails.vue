<template>
	<div id="billDetails">
		<topBack title="账单明细"></topBack>
		<div class="details-content">
			<div class="money-box">
				<span>入账金额</span>
				<em >{{amount}}</em>
			</div>
			<ul class="billDetails-list">
				<li>
					<div class="title">类型</div>
					<div class="content">{{(mode==0)?'收入':'支出'}}</div>
				</li>
				<li>
					<div class="title">时间</div>
					<div class="content">{{changeTime(created_at)}}</div>
				</li>
				<li>
					<div class="title">交易单号</div>
					<div class="content">{{no}}</div>
				</li>
				<li>
					<div class="title">账户余钱</div>
					<div class="content">{{balance}}</div>
				</li>
				<li>
					<div class="title">备注</div>
					<div class="content">{{status(type)}}</div>
				</li>
			</ul>
		</div>
	</div>
</template>

<script>
	import request from '../../utils/userRequest';
	import topBack from "../../components/topBack.vue";
	import Loading from '../../utils/loading'
	import { MessageBox, Toast } from "mint-ui";
	export default {
		data() {
			return {
				showAlert: false,
				created_at:null,	//时间
				remark:null,		//备注
				type:null,			//类型
				no:null,			//交易单号
				amount:null,		//入账金额
				mode:null,			//正负数  0:收入		1:支出
				balance:null		//账户余钱

			};
		},
		created(){
			this.init();
		},
		methods: {
			init(){
				Loading.getInstance().open("加载中...");
				var self = this;
      			var _id = this.$route.query.id;
				console.log(_id);
				request.getInstance().getData("api/account/records/detail/"+_id)
					.then((res) => {
                        this.remark=res.data.data.remark
						this.no=res.data.data.no
						this.balance=res.data.data.balance
						this.created_at=res.data.data.created_at
						this.amount=res.data.data.amount
						this.type=res.data.data.type	

                        Loading.getInstance().close();
					})
					.catch((err) => {
						console.error(err);
                        Loading.getInstance().close();
					})
			},
			changeTime(shijianchuo){
				function add0(m){return m<10?'0'+m:m }
				
				var time = new Date(shijianchuo*1000);
				var y = time.getFullYear();
				var m = time.getMonth()+1;
				var d = time.getDate();
				var h = time.getHours();
				var mm = time.getMinutes();
				var s = time.getSeconds();
				return y+'-'+add0(m)+'-'+add0(d)+' '+add0(h)+':'+add0(mm)+':'+add0(s);
			},
			status(type){
				let result='';
				switch(type){
					case 0: result='充值'; break;
					case 1: result='提现'; break;
					case 2: result='交易收入'; break;
					case 3: result='交易支出'; break;
					case 4: result='转账到店铺'; break;
					case 5: result='店铺转入'; break;
					case 6: result='交易手续费'; break;
					case 7: result='提现手续费'; break;
					case 8: result='大赢家茶水费'; break;
				}
				return result;
			}
		},
		components: {
			topBack
		}
	};
</script>

<style lang="scss" scoped>
	@import "../../../sass/oo_flex.scss";
	#billDetails {
		padding-top: 2em;
		background: #eee;
		height: 100vh;
		box-sizing: border-box;
	}

	.details-content {
		background: #fff;
		padding-bottom: 7em;
	}

	.money-box {
		height: 40px;
		line-height: 40px;
		border-bottom: 1px solid #ccc;
		display: flex;
		justify-content: space-between;
		padding: .5em .7em 0 .7em;
	}

	.billDetails-list {
		li {
			margin-top: 1em;
			display: flex;
			justify-content: space-between;
			padding: 0 0.7em;
			.title {
				color: #333;
			}
			.content {
				color: #555;
			}
			.remark{
				width: 20px;
			}
		}
	}
</style>