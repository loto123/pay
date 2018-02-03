<template>
	<div id="billDetails">
		<topBack title="账单明细"></topBack>
		<div class="details-content">
			<div class="money-box">
				<span>转钻金额</span>
				<em v-bind:class="[(mode==1)?'':'active']">{{mode == 1?-amount:amount}}</em>
			</div>
			<ul class="billDetails-list">
				<li>
					<div class="title">类型</div>
					<div class="content">{{status(mode)}}</div>
				</li>
				<li>
					<div class="title">时间</div>
					<div class="content">{{changeTime(created_at)}}</div>
				</li>
				<li>
					<div class="title">单号</div>
					<div class="content">{{no}}</div>
				</li>
				<!-- <li>
					<div class="title">提钻账户</div>
					<div class="content">{{userName}}</div>
				</li> -->
				<li>
					<div class="title">备注</div>
					<div class="content">{{remark.length==0?"无":remark}}</div>
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
				remark:0,		//备注
				type:null,			//类型
				no:null,			//任务单号
				amount:null,		//入账金额
				mode:null,			//0:收入		1:支出
				userName:null		//提钻账户
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
				request.getInstance().getData("api/shop/transfer/records/detail/"+_id)
					.then((res) => {
                        this.remark=res.data.data.remark
						this.no=res.data.data.no
						this.created_at=res.data.data.created_at
						this.amount=res.data.data.amount
						this.type=res.data.data.type	
						this.mode=res.data.data.mode
						this.userName=res.data.data.user_name
                        Loading.getInstance().close();
					})
					.catch((err) => {
						Toast(err.data.msg);
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
				let res='';
				switch(type){
					case 0: res='提现'; break;
					case 1: res='转钻给店铺成员'; break;
					case 2: res='收入'; break;
				}
				return res;
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
		.active{
			color: #00cc00;
		}
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