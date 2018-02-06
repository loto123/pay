<template>
	<div id="dealList">
		<topBack title="消息中心" style="background:#26a2ff;color:#fff;" :backUrl="'\/index\/'">
		</topBack>
		<div class="tab-menu flex flex-align-center flex-justify-center">
			<div class="flex flex-align-center flex-justify-center active">在售宠物</div>
			<div class="flex flex-align-center flex-justify-center" @click="goMyPet">我的宠物</div>
		</div>
		<div class="pet-box" ref='wrapper'>
			<ul v-infinite-scroll="loadMore" infinite-scroll-disabled="loading" infinite-scroll-distance="80">
				<li class="flex flex-align-center flex-justify-between" v-for="item in salePetList">
					<div class="flex-2 pet-image">
						<img :src="item.pet_image">
					</div>
					<div class="flex-5 pet-info">
						<div class="pet-number">宠物编号:{{item.pet_id}}</div>
						<div class="pet-owner">持有人:{{item.holder_name}}</div>
					</div>
					<div class="flex-3 price">
						售价:<span>{{(item.price==0)?"面议":item.price+'元'}}</span>
					</div>
				</li>
			</ul>
			<p v-if="loading" class="page-infinite-loading flex flex-align-center flex-justify-center">
				<mt-spinner type="fading-circle"></mt-spinner>
				<span style="margin-left: 0.5em;color:#999;">加载中...</span>
			</p>
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
				salePetList:[],		//在售宠物列表

				wrapperHeight: null,
                loading: false,
                allLoaded: false,
                canLoading: true,
			};
		},
		created(){
			this.init();
		},
		mounted(){
            this.wrapperHeight = document.documentElement.clientHeight - this.$refs.wrapper.getBoundingClientRect().top;
        },
		components: { topBack },
		
		methods: {
			goMyPet(){
				this.$router.push('/myPet');
			},
			init(){
				var _data = {
						type: 1,
						limit: 20,
						offset: 0
					}
				Loading.getInstance().open("加载中...");

				request.getInstance().getData('api/pet/all_on_sale',_data)
					.then((res) => {
						this.salePetList=res.data.data.data;
						Loading.getInstance().close();
					})
					.catch((err) => {
						Toast(err.data.msg);
						Loading.getInstance().close();
					})
			},
			loadMore() {
				this.loading = false;
				if (this.salePetList.length == 0 || !this.canLoading) {
					return;
				}
				this.loading = true;
				this.canLoading = false;
				setTimeout(() => {

					var _data = {
						type: 1,
						limit: 20,
						offset: [].concat(this.salePetList).pop().id
					}

					request.getInstance().getData('api/pet/all_on_sale', _data).then(res => {

						if (res.data.data.list.length == 0) {
							this.canLoading = false;
							this.loading = false;
							return;
						}

						for (var i = 0; i < res.data.data.list.length; i++) {
							this.salePetList.push(res.data.data.list[i]);
						}

						this.canLoading = true;
						this.loading = false;
					}).catch(err => {

					});
				}, 1500);

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
			background:#fff;
			border: 1px solid #eee;
			li{
				margin-top:1em;
				padding:1em 0;
				margin: 0 0.7em;
				border-bottom: 1px solid #eee;
				.pet-image{
					img{
						display: block;
						width:100%;
					}
				}
				.pet-info{
					padding-left:0.5em;
					color: #323232;
					font-size: 0.9em;
					.pet-number{
						margin-bottom: 1em;
					}
				}
				.price{
					span{
						margin-left:0.2em;
						color:#26A3FF;
						font-weight:700;
					}
				}
			}
		}
	}
</style>