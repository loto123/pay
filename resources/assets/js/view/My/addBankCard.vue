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
			<div class="page-picker-wrapper">
				<mt-picker :slots="addressSlots" @change="onAddressChange" :visible-item-count="5"></mt-picker>
			</div>
			<p class="page-picker-desc" style="padding:1em;">地址: {{ addressProvince }} {{ addressCity }}</p>
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
	import { MessageBox, Toast, Picker } from "mint-ui";

	import Loading from '../../utils/loading'

	const address = {
    '北京': ['北京'],
    '广东': ['广州', '深圳', '珠海', '汕头', '韶关', '佛山', '江门', '湛江', '茂名', '肇庆', '惠州', '梅州', '汕尾', '河源', '阳江', '清远', '东莞', '中山', '潮州', '揭阳', '云浮'],
    '上海': ['上海'],
    '天津': ['	'],
    '重庆': ['重庆'],
    '辽宁': ['沈阳', '大连', '鞍山', '抚顺', '本溪', '丹东', '锦州', '营口', '阜新', '辽阳', '盘锦', '铁岭', '朝阳', '葫芦岛'],
    '江苏': ['南京', '苏州', '无锡', '常州', '镇江', '南通', '泰州', '扬州', '盐城', '连云港', '徐州', '淮安', '宿迁'],
    '湖北': ['武汉', '黄石', '十堰', '荆州', '宜昌', '襄樊', '鄂州', '荆门', '孝感', '黄冈', '咸宁', '随州', '恩施土家族苗族自治州', '仙桃', '天门', '潜江', '神农架林区'],
    '四川': ['成都', '自贡', '攀枝花', '泸州', '德阳', '绵阳', '广元', '遂宁', '内江', '乐山', '南充', '眉山', '宜宾', '广安', '达州', '雅安', '巴中', '资阳', '阿坝藏族羌族自治州', '甘孜藏族自治州', '凉山彝族自治州'],
    '陕西': ['西安', '铜川', '宝鸡', '咸阳', '渭南', '延安', '汉中', '榆林', '安康', '商洛'],
    '河北': ['石家庄', '唐山', '秦皇岛', '邯郸', '邢台', '保定', '张家口', '承德', '沧州', '廊坊', '衡水'],
    '山西': ['太原', '大同', '阳泉', '长治', '晋城', '朔州', '晋中', '运城', '忻州', '临汾', '吕梁'],
    '河南': ['郑州', '开封', '洛阳', '平顶山', '安阳', '鹤壁', '新乡', '焦作', '濮阳', '许昌', '漯河', '三门峡', '南阳', '商丘', '信阳', '周口', '驻马店'],
    '吉林': ['长春', '吉林', '四平', '辽源', '通化', '白山', '松原', '白城', '延边朝鲜族自治州'],
    '黑龙江': ['哈尔滨', '齐齐哈尔', '鹤岗', '双鸭山', '鸡西', '大庆', '伊春', '牡丹江', '佳木斯', '七台河', '黑河', '绥化', '大兴安岭地区'],
    '内蒙古': ['呼和浩特', '包头', '乌海', '赤峰', '通辽', '鄂尔多斯', '呼伦贝尔', '巴彦淖尔', '乌兰察布', '锡林郭勒盟', '兴安盟', '阿拉善盟'],
    '山东': ['济南', '青岛', '淄博', '枣庄', '东营', '烟台', '潍坊', '济宁', '泰安', '威海', '日照', '莱芜', '临沂', '德州', '聊城', '滨州', '菏泽'],
    '安徽': ['合肥', '芜湖', '蚌埠', '淮南', '马鞍山', '淮北', '铜陵', '安庆', '黄山', '滁州', '阜阳', '宿州', '巢湖', '六安', '亳州', '池州', '宣城'],
    '浙江': ['杭州', '宁波', '温州', '嘉兴', '湖州', '绍兴', '金华', '衢州', '舟山', '台州', '丽水'],
    '福建': ['福州', '厦门', '莆田', '三明', '泉州', '漳州', '南平', '龙岩', '宁德'],
    '湖南': ['长沙', '株洲', '湘潭', '衡阳', '邵阳', '岳阳', '常德', '张家界', '益阳', '郴州', '永州', '怀化', '娄底', '湘西土家族苗族自治州'],
    '广西': ['南宁', '柳州', '桂林', '梧州', '北海', '防城港', '钦州', '贵港', '玉林', '百色', '贺州', '河池', '来宾', '崇左'],
    '江西': ['南昌', '景德镇', '萍乡', '九江', '新余', '鹰潭', '赣州', '吉安', '宜春', '抚州', '上饶'],
    '贵州': ['贵阳', '六盘水', '遵义', '安顺', '铜仁地区', '毕节地区', '黔西南布依族苗族自治州', '黔东南苗族侗族自治州', '黔南布依族苗族自治州'],
    '云南': ['昆明', '曲靖', '玉溪', '保山', '昭通', '丽江', '普洱', '临沧', '德宏傣族景颇族自治州', '怒江傈僳族自治州', '迪庆藏族自治州', '大理白族自治州', '楚雄彝族自治州', '红河哈尼族彝族自治州', '文山壮族苗族自治州', '西双版纳傣族自治州'],
    '西藏': ['拉萨', '那曲地区', '昌都地区', '林芝地区', '山南地区', '日喀则地区', '阿里地区'],
    '海南': ['海口', '三亚', '五指山', '琼海', '儋州', '文昌', '万宁', '东方', '澄迈县', '定安县', '屯昌县', '临高县', '白沙黎族自治县', '昌江黎族自治县', '乐东黎族自治县', '陵水黎族自治县', '保亭黎族苗族自治县', '琼中黎族苗族自治县'],
    '甘肃': ['兰州', '嘉峪关', '金昌', '白银', '天水', '武威', '酒泉', '张掖', '庆阳', '平凉', '定西', '陇南', '临夏回族自治州', '甘南藏族自治州'],
    '宁夏': ['银川', '石嘴山', '吴忠', '固原', '中卫'],
    '青海': ['西宁', '海东地区', '海北藏族自治州', '海南藏族自治州', '黄南藏族自治州', '果洛藏族自治州', '玉树藏族自治州', '海西蒙古族藏族自治州'],
    '新疆': ['乌鲁木齐', '克拉玛依', '吐鲁番地区', '哈密地区', '和田地区', '阿克苏地区', '喀什地区', '克孜勒苏柯尔克孜自治州', '巴音郭楞蒙古自治州', '昌吉回族自治州', '博尔塔拉蒙古自治州', '石河子', '阿拉尔', '图木舒克', '五家渠', '伊犁哈萨克自治州'],
    '香港': ['香港'],
    '澳门': ['澳门'],
    '台湾': ['台北市', '高雄市', '台北县', '桃园县', '新竹县', '苗栗县', '台中县', '彰化县', '南投县', '云林县', '嘉义县', '台南县', '高雄县', '屏东县', '宜兰县', '花莲县', '台东县', '澎湖县', '基隆市', '新竹市', '台中市', '嘉义市', '台南市']
  };

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

				computedTime: null,		//短信验证码倒计时
				addressSlots:null,
				address:null,
				addressProvince:'北京直辖市',
				addressCity:'昌平区',
				
				// addressSlots:[
				// 	{
				// 		flex: 1,
				// 		values: Object.keys(address),
				// 		className: 'slot1',
				// 		textAlign: 'center'
				// 	}, 
				// 	{
				// 		divider: true,
				// 		content: '-',
				// 		className: 'slot2'
				// 	}, {
				// 		flex: 1,
				// 		values:  [],
				// 		className: 'slot3',
				// 		textAlign: 'center'
				// 	}
				// ],

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
			initArea() {
				var self = this
				Loading.getInstance().open();
				request.getInstance().getData("api/card/getBankCardParams").then(res => {
					this.address=res.data.data;
					console.log(this.address);
					this.addressSlots=[
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
						values: ['昌平区'],
						className: 'slot3',
						textAlign: 'center'
					}
				],
				// 	console.log(222);
				// 	console.log(this.addressSlots);
					
					Loading.getInstance().close();
				})
					.catch(err => {
						console.error(err);
						Loading.getInstance().close();
					});
			},
			// area(res){
			// 	var areaList=[]
			// 	for(let i = 0; i < res.data.data.length; i++){

			// 	}
			// },
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

				if (this.shopId == null) {
					Toast("请选择银行卡所属银行");
					return
				} else if (!this.card_num) {
					Toast("请填写银行卡号");
					return
				} else if (!this.mobile) {
					Toast("请填写银行卡预留手机号");
					return
				} else if (!this.code) {
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

				if (!this.mobile) {
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
					console.error(err);
				})
			},
			onAddressChange(picker, values) {
				if(this.address[values[0]]){  //这个判断类似于v-if的效果（可以不加，但是vue会报错，很不爽）
					picker.setSlotValues(1,Object.keys(this.address[values[0]])); // Object.keys()会返回一个数组，当前省的数组
					console.log(this.address[values[0]]);
					picker.setSlotValues(1,this.address[values[0]]); // 区/县数据就是一个数组
					console.log(values);
					this.addressProvince = values[0];
					this.addressCity = values[1];
				}
			}
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
		}
	}

	.affirm-add {
		display: block;
		width: 100%;
		margin: 2em auto;
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