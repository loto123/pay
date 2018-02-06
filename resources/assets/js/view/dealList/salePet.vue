<template>
	<div id="dealList">
		<topBack title="消息中心" style="background:#26a2ff;color:#fff;" :backUrl="'\/index\/'">
		</topBack>
		<div class="tab-menu flex flex-align-center flex-justify-center">
			<div class="flex flex-align-center flex-justify-center active">在售宠物</div>
			<div class="flex flex-align-center flex-justify-center" @click="goMyPet">我的宠物</div>
		</div>
		<div class="pet-box">
			<ul>
				<li class="flex flex-align-center flex-justify-between" v-for="item in salePetList">
					<div class="flex-2 pet-image">
						<img :src="item.pet_image">
					</div>
					<div class="flex-5 pet-info">
						<div>宠物编号:{{item.pet_id}}</div>
						<div>持有人:{{item.holder_name}}</div>
					</div>
					<div class="flex-3 price">
						售价<span>{{(item.price==0)?"面议":item.price}}</span>
					</div>
				</li>
			</ul>
		</div>
	</div>
</template>


<script>
	import request from '../../utils/userRequest';
	import topBack from "../../components/topBack.vue";
	import { MessageBox,Toast } from "mint-ui";
	import Loading from '../../utils/loading'
	export default {
		data() {
			return {
				salePetList:[]		//在售宠物列表
			};
		},
		created(){
			this.init();
		},
		components: { topBack },
		
		methods: {
			goMyPet(){
				this.$router.push('/myPet');
			},
			init(){
				Loading.getInstance().open("加载中...");

				request.getInstance().getData('api/pet/all_on_sale')
					.then((res) => {
						this.salePetList=res.data.data.data;
						Loading.getInstance().close();
					})
					.catch((err) => {
						Toast(err.data.msg);
						Loading.getInstance().close();
					})
			}
		}
	};
</script>

<style lang="scss" scoped>
	@import "../../../sass/oo_flex.scss";
	#dealList {
		width: 100%;
		padding-top: 2em;
		box-sizing: border-box;
		background: #f4f4f4;
		min-height: 100vh;
	}

	.tab-menu {
		width: 100%;
		height: 3em;
		background: #fff;
		>div {
			width: 50%;
			height: 100%;
			box-sizing: border-box;
		}
		.active {
			border-bottom: 0.2em solid #26a2ff;
			color: #26a2ff;
		}
	}
	.pet-box{
		ul{
			li{
				margin-top:1em;
				background:#fff;
				padding:1em;
				.pet-image{
					img{
						display: block;
						width:100%;
					}
				}
				.pet-info{
					padding-left:0.5em;
				}
				.price{
					span{
						margin-left:0.5em;
						color:#f00;
					}
				}
			}
		}
	}
</style>