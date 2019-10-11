/**
 * 首页排版设计，支持拖拽排序
 *
 * 数据结构：
 * { tag : "模块标识" , name: "模块名称", className: "样式",index : 下标, sort: 排序号, selected: 当前是否选中编辑，默认false, isVisible: 是否显示，默认true}
 *
 */
var vue;

$(function () {

	vue = new Vue({
		el: ".page-layout",
		created: function () {
			setTimeout(function () {
				//绑定拖拽控件
				$('.draggable-element').arrangeable('', {"border": "2px dashed rgba(255, 0, 0, 0.5)"}, function () {
					//拖拽时回调函数
					$(".draggable-element").removeClass("selected");
					$(".pt.pt-left").hide();
				},function () {
					//拖拽结束后回调函数
					vue.refresh();
				});
			},10);

			//编辑时赋值，初始化数据
			if(value){
				this.data = value;
				for (var i=0;i<this.data.length;i++){
					this.data[i].index = i;
					this.data[i].sort = i;
					this.data[i].selected = false;
				}
			}
		},
		data: {
			data : [
					{ tag : "follow-wechat" , name: "关注微信公众号", className: "item-follow-wechat-public-account",index : 0, sort: 0, selected: false, isVisible: true, url : '/wchat/config'},
					{ tag : "banner" , name: "轮播图", className: "item-banner",index : 1, sort: 1, selected: false, isVisible: true, url : '/system/updateshopadvposition?terminal=2&ap_id=1105'},
					{ tag : "search" , name: "搜索栏", className: "item-search",index : 2, sort: 2, selected: false, isVisible: true, url : '' },
					{ tag : "nav" , name: "导航栏", className: "item-nav",index : 3, sort: 3, selected: false, isVisible: true, url : '/config/shopNavigationList?nav_type=2'},
					{ tag : "notice" , name: "公告", className: "item-notice",index : 4, sort: 4, selected: false, isVisible: true, url : '/config/userNotice'},
					{ tag : "coupons" , name: "优惠券", className: "item-coupons",index : 5, sort: 5, selected: false, isVisible: true, url : '/promotion/coupontypelist'},
					{ tag : "games" , name: "游戏活动", className: "item-games",index : 6, sort: 6, selected: false, isVisible: true, url : '/promotion/index' },
					{ tag : "discount" , name: "限时折扣", className: "item-discount",index : 7, sort: 7, selected: false, isVisible: true, url : '/promotion/getdiscountlist'},
					{ tag : "spell-bargain" , name: "砍价推荐", className: "item-spell-bargain",index : 8, sort: 8, selected: false, isVisible: true, url : "/NsBargain/" + ADMINMODULE + "/bargain/index" },
					{ tag : "spell-group" , name: "拼团推荐", className: "item-spell-group",index : 9, sort: 9, selected: false, isVisible: true, url : "/NsPintuan/" + ADMINMODULE + "/tuangou/pintuanlist" },
					// { tag : "adv" , name: "广告位", className: "item-adv",index : 10, sort: 10, selected: false, isVisible: true, url : '/system/shopadvpositionlist?terminal=2' },
					{ tag : "goods" , name: "推荐商品", className: "item-goods",index : 11, sort: 11, selected: false, isVisible: true, url : '/config/goodsRecommend' },
					{ tag : "cube" , name: "魔方", className: "item-cube",index : 12, sort: 12, selected: false, isVisible: true, url : '/config/shopCube'},
					{ tag : "bottom" , name: "底部导航", className: "item-bottom",index : 13, sort: 13, selected: false, isVisible: true, url : ''},
				],

			//重置
			rest : ["follow-wechat","banner","search","nav","notice","coupons","games","discount","spell-bargain","spell-group","adv","goods","cube","bottom"],

			//当前选中的模块
			current : {
				name: "",
				isVisible: true,
				index: -1
			}

		},
		methods: {

			//点击模块
			clickModule: function (index) {
				this.current.isVisible = false;
				this.current.index = -1;
				for (var i = 0; i < this.data.length; i++) {
					if (index == i) {
						this.data[i].selected = true;
						this.current.name = this.data[i].name;
						this.current.isVisible = this.data[i].isVisible;
						this.current.url = this.data[i].url;
						this.current.index = i;
					} else {
						this.data[i].selected = false;
					}
				}
				var self = $(".page-layout .preview-body li[data-index=" + index + "]");
				var href = "";
				if(vue.current.url){
					if(vue.current.url.indexOf("NsPintuan") != -1){
						href = __URL(URL + vue.current.url);
					}else if(vue.current.url.indexOf("NsBargain") != -1){
						href = __URL(URL + vue.current.url);
					}else{
						href = __URL(ADMINMAIN + vue.current.url);
					}
				}
				var html = '<div class="edit-wrap">' +
								'<div class="item-title">'+
									'<h4>' +vue.current.name + '</h4>' +
									'<a href="' + href + '" target="_black" ' + (href == "" ? "style='display:none;'" : "") +'>设置</a>' +
								'</div>'+
								'<div class="item">' +
									'<label>是否显示</label>' +
									'<div class="switch-wrap ' + (vue.current.isVisible ? "checked" : "") + '">' +
										'<small></small>' +
									'</div>' +
								'</div>' +
							'</div>';
				$.pt({
					target: self,
					position: 'r',
					align: "t",
					width: 160,
					autoClose: false,
					content: html,
					open: function (r) {
						$(".pt-left").css("left", ($(".pt-left").offset().left + 10));
					}
				});
			},

			//刷新数据排序
			refresh : function(){
				var self = this;
				//vue框架执行，异步操作组件列表的排序
				setTimeout(function(){

					$(".draggable-element").each(function(i){
						$(this).attr("data-sort",i);
					});
					for(var i=0;i<self.data.length;i++){
						self.data[i].index = $(".draggable-element[data-index=" + i + "]").attr("data-index");
						self.data[i].sort = $(".draggable-element[data-index=" + i + "]").attr("data-sort");
					}

					//触发变异方法，进行视图更新。不能用sort()方法，会改变组件的顺序，导致显示的顺序错乱
					self.data.push({});
					self.data.pop();

				},10);

			},
			save : function () {

				var value = JSON.stringify(vue.data);
				value = eval(value);
				//重新排序
				value.sort(function(a,b){
					return a.sort-b.sort;
				});
				for(i in value){
					delete value[i].selected;
					delete value[i].index;
					delete value[i].sort;
				}
				$.ajax({
					type : "post",
					url : __URL(ADMINMAIN + "/config/pagelayout"),
					data : { data : JSON.stringify(value) },
					success : function (res) {
						if (res["code"] > 0) {
							showTip(res["message"],"success");
						}else{
							showTip(res["message"],'error');
						}
					}
				});
			},
			//重置排序
			reset : function () {
				for (var i=0;i<this.rest.length;i++){
					for (var j=0;j<this.data.length;j++){
						if(this.rest[i] == this.data[j].tag){
							this.data[j].sort = i;
							this.data[j].isVisible = true;
						}
					}
				}
				this.data.sort(function(a,b){
					return a.sort-b.sort;
				});
			}
		}
	});

	//设置是否显示
	$(".switch-wrap").live("click",function () {
		var checked = $(this).hasClass("checked");
		if(checked) $(this).removeClass("checked");
		else $(this).addClass("checked");
		vue.data[vue.current.index].isVisible = !checked;
	});

});