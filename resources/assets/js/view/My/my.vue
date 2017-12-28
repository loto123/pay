<template>
	<div id="my">
		<section class="header-container">
			<div class="header">
				<div class="imgWrap">
					<img :src="thumb">
				</div>
				<h3>{{name}}</h3>
				<div class="acc-number">账号:
					<span>{{mobile}}</span>
				</div>
			</div>
		</section>
		<section>
			<ul class="my-list">
				<li @click="referrer">
					<mt-cell title="推荐人" is-link>
						<img slot="icon" src="/images/referrer.png" width="30" height="30">
						<span>{{parent_name}} <em>{{parent_mobile}}</em></span>
					</mt-cell>
				</li>
				<li @click="realAuth(mobile)">
					<mt-cell title="实名认证" is-link>
						<img slot="icon" src="/images/realName.png" width="30" height="30">
						<span>{{identify_status ? "已认证" : "去认证"}}</span>
					</mt-cell>
				</li>
				<li @click="bankCardManage">
					<mt-cell title="银行卡管理" is-link>
						<img slot="icon" src="/images/bankCardManage.png" width="30" height="30">
						<span>
							<font>{{card_count}}</font>张</span>
					</mt-cell>
				</li>
				<li @click="checkSettle">
					<mt-cell title="查看结算卡" is-link>
						<img slot="icon" src="/images/bankCardManage.png" width="30" height="30">
					</mt-cell>
				</li>
				<li @click="set(mobile)">
					<mt-cell title="更多设置" is-link>
						<img slot="icon" src="/images/moreSet.png" width="30" height="30">
					</mt-cell>
				</li>
			</ul>
		</section>
		<tabBar :status="'my'"></tabBar>
	</div>
</template>

<style lang="scss" scoped>
	.header-container {
		padding-top: 4em;
		padding-bottom: 1em;
		box-sizing: border-box;
		background: #26a2ff;
		color: #fff;
	}

	.header {
		text-align: center;
		.imgWrap {
			width: 100%;
			>img {
				width: 4.5em;
				height: 4.5em;
				display: block;
				margin: auto;
				border-radius: 50%;
			}
		}
		h3,
		.acc-number {
			font-size: 1em;
			text-align: center;
			margin-top: 0.5em;
		}
	}

	.my-list {
		border-bottom: 1px solid #d9d9d9;
		li {
			.mint-cell {
				background-image: none;
				background-size: 100% 1px;
				background-repeat: no-repeat;
				background-position: top;
				span {
					font-size: 0.9em;
				}
			}
		}
	}
</style>


<script>
	import tabBar from "../../components/tabBar";
	import request from '../../utils/userRequest';
	import { Toast,MessageBox } from 'mint-ui';
	import Loading from '../../utils/loading'

	export default {
		data () {
			return {
				name:null,		//名字
				mobile:null,	//手机号
				thumb:null,		//图像

				
				parent_name:null,		//推荐人名字
				parent_mobile:null,		//推荐人手机号
				card_count:null,		//银行卡
				identify_status:null	//认证状态
				
				
			}
		},
		created(){
			this.personalInfo();
    	},
		components: { tabBar },
		methods: {
			//个人信息
			personalInfo(){
				Loading.getInstance().open("加载中...");
				Promise.all([request.getInstance().getData("api/my/info"),request.getInstance().getData("api/my/index")])
				.then((res)=>{
					this.name=res[0].data.data.name;
					this.mobile=res[0].data.data.mobile;
					this.thumb=res[0].data.data.thumb;

					this.parent_name=res[1].data.data.parent_name;
					this.parent_mobile=res[1].data.data.parent_mobile;
					this.card_count=res[1].data.data.card_count;
					this.identify_status=res[1].data.data.identify_status;

					Loading.getInstance().close();
				})
				.catch((err)=>{
					console.error(err);
				})
			},
			realAuth(e){
				if(this.identify_status==1){
					Toast('你已完成实名认证 ');
				}else{
					this.$router.push("/my/realAuth"+"?mobile="+e);
				}
				
			},
			//银行卡管理
			bankCardManage(){
				if(this.identify_status==0){
					MessageBox.confirm("你还没有进行实名认证，请先前往认证", "温馨提示").then(
						() => {
							this.$router.push("/my/realAuth"+"?mobile="+this.mobile);
						},
						() => {
							Toast("已经取消");
						}
					);
				}else{
					this.$router.push('/my/bankCardManage');
				}
			},
			//查看结算卡
			checkSettle(){
				if(this.card_count>0){
					this.$router.push('/my/checkSettle');
				}else{
					Toast('请添加银行卡');
				}
			},
			set(e){
				this.$router.push("/my/set"+"?mobile="+e);
			},
			referrer(){
				this.$router.push('/my/referrer');
			}
		}
	};
</script>