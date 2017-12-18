<template>
	<div id="my">
		<topBack title="关于"></topBack>
		<section class="header-container">
			<div class="header">
				<div class="imgWrap">
					<img src="/images/logo.png">
				</div>
				<div class="versions">游戏宝 1.0.1</div>
			</div>
		</section>
		
	</div>
</template>

<style lang="scss" scoped>
	.header-container {
		padding-top: 2em;
		padding-bottom: 1em;
		box-sizing: border-box;
		background: #eee;
		height: 100vh;
	}

	.header {
		text-align: center;
		border-top:1px solid #ccc;
		padding-top: 2em;
		.imgWrap {
			>img {
				width: 4em;
				height: 4em;
				display: block;
				margin: auto;
				border-radius: 50%;
				padding:1em;
				background:#fff;
			}
		}
		.versions {
			font-size: 1em;
			text-align: center;
			margin-top: 0.5em;
		}
	}
</style>


<script>
	import topBack from "../../components/topBack";
	export default {
		data () {
			return {}
		},
		components: { topBack },
	}
</script>