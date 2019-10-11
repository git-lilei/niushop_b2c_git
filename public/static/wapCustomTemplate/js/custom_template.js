/**
 * 可视化手机端模板插件
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 山西牛酷信息科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: http://www.niushop.com.cn
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 * @date : 2017年10月30日 09:11:30
 * @version : v1.0
 */

 /**
  * 公告模块上下滑动
  */
var notice_index = 0;
var notice_autoTimer = 0;

/**
 * 默认数据
 */
var $Default = {

	goodsLimitCount :[6,12,18,24,30,36],
	goodsListType : [1,2],
	carouselInterval : 2000,
	navHyBridItemCount : 4,
	advShowType :[1,2],
	footerItemCount : 4,
	href : "javascript:;",
	textAlign : [1,2,3],
	textSize : [12,13,14,15,16,17,18,19,20],
	auxiliaryBlankHeightMin : 10,
	auxiliaryBlankHeightMax : 200

};

/**
 * 各个组件的最多可以出现的次数
 * 
 */
var $limit = {

	GoodsSearchMaxCount : 99999,
	GoodsListMaxCount : 99999,
	TitleMaxCount : 99999,
	TextNavigationMaxCount : 99999,
	NoticeMaxCount : 99999,
	ImgAdMaxCount : 99999,
	NavHyBridMaxCount : 99999,
	GoodsClassifyMaxCount : 99999,
	FooterMaxCount : 1,
	RichTextMaxCount : 99999,
	CustomModuleMaxCount : 99999,
	AuxiliaryLinelMaxCount : 99999,
	AuxiliaryBlankMaxCount : 99999,
	CouponsMaxCount : 99999,
	VideoMaxCount : 99999,
	ShowCaseMaxCount : 99999

};

var align_array = ["left","center","right"];//居中方式数据

var link_arr = new Array();
var custom_type = $('#type').val() == undefined || $('#type').val() == '' || $('#type').val() == 'undefined' || $('#type').val() == null ? 1 : $('#type').val();
if (custom_type == 1) {
	link_arr[__URL(APPMAIN + '/index/index')] = '店铺首页';
	link_arr[__URL(APPMAIN + '/goods/cart')] = '购物车';
	link_arr[__URL(APPMAIN + '/member/index')] = '会员中心';
	link_arr[__URL(APPMAIN + '/goods/category')] = '商品分类';
	link_arr[__URL(APPMAIN + '/goods/point')] = '积分中心';
	link_arr[__URL(APPMAIN + '/goods/discount')] = '限时折扣';
	link_arr[__URL(APPMAIN + '/goods/brand')] = '品牌专区';
} else {
	link_arr['/pages/index/index'] = '店铺首页';
	link_arr['/pages/goods/cart/cart'] = '购物车';
	link_arr['/pages/member/member/member'] = '会员中心';
	link_arr['/pages/goods/goodsclassificationlist/goodsclassificationlist'] = '商品分类';
	link_arr['/pages/goods/integralzone/integralzone'] = '积分中心';
	link_arr['/pages/goods/discount/discount'] = '限时折扣';
	link_arr['/pages/goods/brandlist/brandlist'] = '品牌专区';
}

/**
 * 组件集合
 * 
 */
var controlList = new Array();
$(".plug-in li").each(function(){
	controlList[$(this).attr("data-control-name")] = $(this).attr("data-control-name");
});

/**
 * 组件值对象
 */
var $cValue = {

	searchTextColor : {
		name : "文字颜色",
		class_name : "text-color",
		input_class : "js-text-color",
		value : "#333333",
		default_value : "#333333",
		placeholder : "#333333"
	},
	searchBgColor : {
		name : "背景颜色", 
		class_name : "text-color", 
		input_class : "js-bg-color",
		value : "#ffffff", 
		default_value : "#ffffff",
		placeholder : "#ffffff"
	},
	searchInputBgColor : {
		name : "输入框背景颜色",
		class_name : "text-color",
		input_class : "js-input-bg-color",
		value : "#f4f4f4",
		default_value : "#f4f4f4",
		placeholder : "#f4f4f4"
	},
	searchPlaceholder : {
		name : "默认提示内容",
		class_name : "goods-search-placeholder",
		input_class : "js-placeholder",
		value : "",
		default_value : "商品搜索",
		placeholder : "商品搜索"
	},

	title : {
		name : "标题名",
		class_name : "title",
		input_class : "js-title-name",
		value : "『标题名』",
		default_value : "",
		placeholder : "",
	},
	subTitle : {
		name : "副标题",
		class_name : "subtitle",
		input_class : "js-subtitle-name",
		value : "",
		default_value : "",
		placeholder : ""
	},
	titleTextColor : {
		name : "文字颜色", 
		class_name : "text-color",
		input_class : "js-text-color",
		value : "#333333",
		default_value : "#333333",
		placeholder : "#333333"
	},
	titleBgColor : {
		name : "背景颜色", 
		class_name : "text-color",
		input_class : "js-bg-color",
		value : "#ffffff",
		default_value : "#ffffff",
		placeholder :  "#ffffff"
	},

	textNavigation : {
		name : "导航名称",
		class_name : "text-navigation",
		input_class : "js-text-navigation",
		value : "『文本导航』",
		default_value : "",
		placeholder : "请输入导航名称"
	},
	textNavigationColor :{
		name : "文字颜色", 
		class_name : "text-color", 
		input_class : "js-text-color",
		value : "#333333",
		default_value : "#333333",
		placeholder : "#333333"
	},
	textNavigationBgColor :{
		name : "背景颜色", 
		class_name : "text-color", 
		input_class : "js-bg-color",
		value : "#ffffff",
		default_value : "#ffffff",
		placeholder : "#ffffff"
	},

	noticeTextColor :{
		name : "文字颜色", 
		class_name : "text-color", 
		input_class : "js-text-color",
		value : "#ff9900",
		default_value : "#ff9900",
		placeholder :  "#ff9900"
	},
	noticeBgColor :{
		name : "背景颜色",
		class_name : "text-color",
		input_class : "js-bg-color",
		value : "#FFFFCC",
		default_value : "#FFFFCC",
		placeholder :  "#FFFFCC"
	},

	NavHyBridText : {
		name : "文字",
		class_name : "nav-hybrid-text",
		input_class : "js-nav-hybrid-text",
		value : "",
		default_value : "",
		placeholder : ""
	},

	NavHyBridColor :{
		name : "文字颜色", 
		class_name : "text-color", 
		input_class : "js-text-color",
		value : "#666666",
		default_value : "#666666",
		placeholder : "#666666"
	},

	NavHyBridBgColor :{
		name : "背景颜色", 
		class_name : "text-color", 
		input_class : "js-bg-color",
		value : "#ffffff",
		default_value : "#ffffff",
		placeholder : "#ffffff"
	},

	footerTextColor : {
		name : "文字颜色",
		class_name : "text-color",
		input_class : "js-text-color",
		value : "#333333",
		default_value : "#333333",
		placeholder : "#333333"
	},
	textColorHover : {
		name : "选中颜色",
		class_name : "text-color",
		input_class : "js-text-color-hover",
		value : "#126AE4",
		default_value : "#126AE4",
		placeholder : "#126AE4"
	},

	customTemplateName : {
		name : "模板名称",
		class_name : "text-custom-template-name",
		input_class : "js-custom-template-name",
		value : "",
		default_value : "",
		placeholder : "不能超过30个字符"
	},

	footerMenuName : {
		name : "菜单名称",
		class_name : "footer-menu-name",
		input_class : "js-footer-menu-name",
		value : "",
		default_value : "",
		placeholder : "请输入菜单名称"
	},
	borderColor : {
		name : "边框颜色",
		class_name : "border-color",
		input_class : "js-border-color",
		value : "#e5e5e5",
		default_value : "#e5e5e5",
		placeholder :  "#e5e5e5"
	},

	videoUrl : {
		name : "视频地址",
		class_name : "video-url",
		input_class : "js-video-url",
		value : "",
		default_value : "",
		placeholder :  ""
	},
	
	auxiliaryBlankColor :{
		name : "空白颜色", 
		class_name : "bg-color", 
		input_class : "js-bg-color",
		value : "#ffffff",
		default_value : "#ffffff",
		placeholder : "#ffffff"
	}

};

/**
 * 点击组件打开右侧编辑栏
 */
var $edit = {
	
	/**
	 * 点击组件打开右侧编辑栏
	 * @param customFlag 组件标识
	 */
	init : function(customFlag){

		try{

			if(customFlag != undefined && $.inArray(controlList,customFlag)){

				return eval("get" + customFlag + "HTML()");

			}else{

				showTip("非法操作","error," + customFlag);

			}

		}catch(e){

			showTip("非法操作(" + e + ")","error");
			console.log("erorr:" + e);

		}

	}

};

$(function() {

	//预先加载百度编辑器
	preloadBaiDuEditor();

	showBottom();

	/**
	 * 添加组件
	 */
	$(".plug-in li").click(function() {

		addControl(this);

	});

	//浏览器窗口监听事件
	$(window).scroll(function() {
		
		showBottom();

	});

	$(window).resize(function(){
	
		//动态改变右侧编辑信息的位置
		draggableElementClick(getCustom().removeAttr("data-is-show"),"select");

	});

	/**
	 * 编辑模板名称
	 * 1、根据模板名称的长度调整位置（居中）
	 */
	$(".custom-template header").click(function(){

		draggableElementClick(this,"select");
		var h4 = $(".custom-template header>h4");
		var width = h4.width()>170 ? 170 : h4.width();
		h4.css("margin","0 0 0 -" + (width/2) + "px");

	});

	//初始化时打开模块名称，后边如果有组件，则会自动隐藏
	$(".custom-template header").click();

	//初始化分辨率数据
	initCustomResolution();

	//从数据库中加载数据
	loadData();

	/**
	 * 分辨率选择[预览]
	 * 1、功能暂时隐藏
	 */
	$(".custom-resolution button").click(function(){

		var width = parseInt($(this).attr("data-width"));
		$(".custom-template").removeClass().addClass("custom-template").addClass("w"+width);
		if(getCustom().attr("data-is-show")) draggableElementClick(getCustom().removeAttr("data-is-show"),"select");

	});

	/**
	 * 绑定右侧弹出层
	 */
	$(".draggable-element").live("click",function(){

		draggableElementClick(this,"select");

	});

	/**
	 * 保存数据
	 */
	$(".js-save").click(function(){

		if(validation()){

			var request_url = __URL(URL + "NsDiyView/" + ADMINMODULE + "/config/addWapCustomTemplate");

			if(parseInt($("#hidden_id").val()) > 0) request_url = __URL(URL + "NsDiyView/" + ADMINMODULE + "/config/updateWapCustomTemplate");

			$.ajax({
				type : "post",
				url : __URL(request_url),
				data : {
					"id" : $("#hidden_id").val(),
					"template_name" : $(".custom-template header>h4").attr("data-custom-template-name"),
					"template_data" : getTemplateData(),
					'type' : custom_type
				},
				success : function(res){

					if(res.code>0){

						showTip(res.message,"success");

						if(parseInt($("#hidden_id").val()) == 0){
							if (custom_type == 2) {
								setTimeout(function(){
									location.href = __URL(URL + "NsDiyView/" + ADMINMODULE + "/config/wapCustomTemplateList?type=2");
								},1500);
							} else {
								setTimeout(function(){
									location.href = __URL(URL + "NsDiyView/" + ADMINMODULE + "/config/wapCustomTemplateList");
								},1500);
							}
						}

					}else{

						showTip(res.message,"error");

					}
				}
			});

		}

	});

	/**
	 * 自定义模板名称编辑
	 */
	$(".js-custom-template-name").live("keyup",function(){

		var value = $(this).val();

		if(value.length>30){

			value = value.substr(0,30);
			$(this).val(value);
			showTip("模板名称不能超过30个字符,超出部分将会被截取","warning");

		}

		var h4 = $(".custom-template header>h4");
		h4.text(value).attr('title',value).attr("data-custom-template-name",value);
		var width = h4.width()>170 ? 170 : h4.width();
		h4.css("margin","0 0 0 -" + (width/2) + "px");

	});

	$(".js-select-font-size").live("change",function(){

		var value = $(this).val();
		getCustom().find("[data-editable]").css("font-size",value + "px");

		try {

			eval("bind" + getCustom().attr("data-custom-flag") + "Data()");

		} catch (error) {

			getCustom().attr("data-font-size",value);
			console.log("change font size error:" + error);

		}

	});

	$(".js-text-color").live("change",function(){

		var value = $(this).val();
		getCustom().find("[data-editable]").css("color",value);

		try {

			eval("bind" + getCustom().attr("data-custom-flag") + "Data()");

		} catch (error) {

			getCustom().attr("data-text-color",value);
			console.log("change text color error: " + error);

		}

	});

	$(".js-placeholder").live("keyup",function(){

		var value = $(this).val();

		if($(this).val().length>30){

			value = value.substr(0,30);
			$(this).val(value);

		}

		if(empty(value)) value = $(this).attr("data-default-value");

		getCustom().find("[data-editable]").attr("placeholder",value);

		bindGoodsSearchData();

	});

	$(".js-bg-color").live("change",function(){

		var value = $(this).val();
		getCustom().css("background-color",value);

		try {

			eval("bind" + getCustom().attr("data-custom-flag") + "Data()");

		} catch (error) {

			getCustom().attr("data-bg-color",value);
			console.log("change bgcolor error : " + error);

		}

	});

	$(".js-input-bg-color").live("change",function(){

		getCustom().find("[data-editable]").css("background-color",$(this).val());
		bindGoodsSearchData();

	});

	/**
	 * [商品列表组件]->商品显示个数
	 */
	$("input[name='showcount']").live("click",function(){

		bindGoodsListData();

	});

	/**
	 * [商品列表组件]->列表样式选择
	 */
	$("input[name='list_type']").live("click",function(){

		var control_name = getCustom().attr("data-custom-flag");
		var goods_list = getCustom().attr("data-goods-list");

		if(!empty(goods_list)) goods_list = eval("(" + goods_list + ")");

		switch(parseInt($(this).val())){

			case $Default.goodsListType[0]:

				//大图:1
				getCustom().html(getGoodsListBigStyleHTML(goods_list) + getCommonHTML(control_name)).attr("data-list-type",$Default.goodsListType[0]);

				break;

			case $Default.goodsListType[1]:

				//小图:2
				getCustom().html(getGoodsListSmallStyleHTML(goods_list) + getCommonHTML(control_name)).attr("data-list-type",$Default.goodsListType[1]);

				break;

		}

		if($("#show_buy_button").is(":checked")) $(".control-goods-list .control-goods-price>button").show();
		else $(".control-goods-list .control-goods-price>button").hide();

		if($("#show_goods_name").is(":checked")) $(".control-goods-list .control-goods-name").show();
		else $(".control-goods-list .control-goods-name").hide();

		if($("#show_goods_price").is(":checked")) $(".control-goods-list .control-goods-price>em").show();
		else $(".control-goods-list .control-goods-price>em").hide();

		bindGoodsListData();

	});

	/**
	 * [商品列表组件,商品分类组件组件]->显示购买按钮
	 * 1、商品分类也可以操作购买按钮
	 */
	$("#show_buy_button").live("click",function(){

		var checked = $(this).is(":checked");
		var custom_flag = getCustom().attr("data-custom-flag");

		if(checked){

			if($("input[name='buy_button_style']:checked").attr("data-buy-button-style") == 4) $(".js-show-buy-button-style").show().next().show();
			else $(".js-show-buy-button-style").show();

			if(custom_flag == controlList.GoodsList) getCustom().find(".control-goods-price").show().find(".control-goods-buy-style").show();
			else if(custom_flag == controlList.GoodsClassify) getCustom().find(".control-goods-buy-style").show();

		}else{

			if($("input[name='buy_button_style']:checked").attr("data-buy-button-style") == 4) $(".js-show-buy-button-style").hide().next().hide();
			else $(".js-show-buy-button-style").hide();

			if(custom_flag == controlList.GoodsList){

				//如果价格也隐藏了，那整个块都隐藏
				if(getCustom().find(".control-goods-price").children("em").is(":hidden")) getCustom().find(".control-goods-price").hide();
				else getCustom().find(".control-goods-buy-style").hide();

			}else if(custom_flag == controlList.GoodsClassify){

				getCustom().find(".control-goods-buy-style").hide();

			}

		}

		eval("bind" + custom_flag + "Data()");

	});

	/**
	 * [商品列表组件,商品分类组件]->购买按钮样式
	 * 1、商品分类也可以操作购买按钮
	 */
	$("input[name='buy_button_style']").live("click",function(){

		var img = getCustom().find(".control-goods-buy-style>img");
		var style = $(this).attr("data-buy-button-style");//样式选择
		var value = $(this).val();//图片路径

		if(parseInt(style) != 4){

			img.attr("src",__IMG(value));
			$(".custom-buy-style").hide();

		}else{

			//自定义购买按钮
			if(!empty(value)) img.attr("src",__IMG(value));
			else img.removeAttr("src");
			$(".custom-buy-style").show();

		}

		eval("bind" + getCustom().attr("data-custom-flag") + "Data()");

	});

	/**
	 * [商品列表组件]->是否显示商品名称(checkbox)
	 */
	$("#show_goods_name").live("click",function(){

		getCustom().find(".control-goods-name").fadeToggle();//是否显示商品名称
		bindGoodsListData();

	});

	/**
	 * [商品列表组件]->是否显示价格(checkbox)
	 */
	$("#show_goods_price").live("click",function(){

		var checked = $("#show_goods_price").is(":checked");

		if(checked){

			getCustom().find(".control-goods-price").removeClass("position").show().children("em").show();

		}else{

			//如果购买按钮也隐藏了，那整个块都隐藏
			if(getCustom().find(".control-goods-price").children("button").is(":hidden")) getCustom().find(".control-goods-price").hide();
			else getCustom().find(".control-goods-price").addClass("position").children("em").hide();

		}

		bindGoodsListData();

	});

	/**
	 * [选择商品来源公共]->商品分类
	 */
	$(".js-goods-source").live("change",function(){

		bindGoodsListData();

	})

	/**
	 * [标题组件]->标题名编辑
	 */
	$(".js-title-name").live("keyup",function(){

		getCustom().find("h4").text($(this).val());
		bindTitleData();

	});

	/**
	 * [标题组件]->副标题编辑
	 */
	$(".js-subtitle-name").live("keyup",function(){

		getCustom().find("p").text($(this).val());
		bindTitleData();

	});

	/**
	 * [显示方式公共]，居左、居中、居右
	 */
	$("input[name='text_align']").live("click",function(){

		var value = $(this).val();
		getCustom().find("[data-editable]").css("text-align",align_array[value-1]);

		try {

			eval("bind" + getCustom().attr("data-custom-flag") + "Data()");

		} catch (error) {

			getCustom().attr("data-text-align",value);
			console.log("change text-align error: " + error);

		}

	});

	/**
	 * [自定义链接公共]
	 */
	$(".js-custom-link").live("click",function(){

		setLinkCustomMarginLeft();
		$(this).parent().parent().parent().find(".float-link-custom").show();

	});

	/**
	 * [设置链接地址公共]
	 */
	$(".js-link li[class!='js-custom-link']").live("click",function(){

		var text = $(this).text();
		var href = $(this).attr("data-href");
		$(this).parent().parent().parent().find(".selected").text(text).attr("data-href",href).css("display","inline-block");
		bindLink();//绑定当前组件所需要的全部链接地址数据

	});

	/**
	 * [自定义链接公共]->确定（可能会有多个需要进行拼装）
	 */
	$(".float-link-custom .btn-common").live("click",function(){

		var value = $(this).prev().val();
		if(value.length){

			if(validateDomainName(value)){

				$(this).parent().parent().parent().find(".selected").text(value).attr("data-href",value).css("display","inline-block");
				$(this).parent().parent().parent().find(".float-link-custom").hide();
				bindLink();//绑定当前组件所需要的全部链接地址数据

			}else{

				showTip("链接地址错误","warning");

			}

		}else{

			showTip("请输入链接地址","warning");

		}

	});

	/**
	 * [自定义链接公共]->键盘事件（可能会有多个需要进行拼装）
	 */
	$(".float-link-custom input").live("keyup",function(event){

		var value = $(this).val();
		if(value.length){

			if(validateDomainName(value)){

				if( event.keyCode == 13){

					$(this).parent().parent().parent().find(".selected").text(value).attr("data-href",value).css("display","inline-block");
					$(this).parent().parent().parent().find(".float-link-custom").hide();
					bindLink();//绑定当前组件所需要的全部链接地址数据

				}
			}else{

				showTip("链接地址错误","warning");

			}

		}else{

			showTip("请输入链接地址","warning");

		}

	});

	/**
	 * [自定义链接公共]->取消编辑
	 */
	$(".float-link-custom .btn-common-cancle").live("click",function(){

		$(this).parent().parent().parent().find(".float-link-custom").hide();

	});

	/**
	 * [文本导航组件]->导航名称编辑
	 */
	$(".js-text-navigation").live("keyup",function(){

		bindTextNavigationData();
		updateTextNavigationHTML();

	});

	/**
	 * [图片广告组件]->显示方式
	 */
	$("input[name='show_img_ad_type']").live("click",function(){

		var control_name = getCustom().attr("data-custom-flag");

		if($(this).val() == $Default.advShowType[0]){

			getCustom().removeClass("slide").html(getImgAdvSingleHTML() + getCommonHTML(control_name));
			$(this).parent().parent().children(".control-edit.img-ad:first").show().siblings(".control-edit.img-ad").hide();

		}else if($(this).val() == $Default.advShowType[1]){

			getCustom().addClass("slide").html(getImgAdvCarouselHTML() + getCommonHTML(control_name));
			var new_id = getCustom().attr("id") + $(".custom-main [data-custom-flag='" + controlList.ImgAd + "']").length;
			getCustom().attr("id",new_id).find("a").attr("href","#" + new_id);
			$(this).parent().parent().children(".control-edit.img-ad").show();
			$('.carousel').carousel();//轮播停留时间

		}

		bindImgAdData();

	});

	/**
	 * [图文导航组件]->文字编辑
	 */
	$(".js-nav-hybrid-text").live("keyup",function(){

		if($(this).val().length>8){
			showTip("文字不得超出8个字符","warning");
			$(this).val($(this).val().substr(0,8));
		}

		bindNavHyBridData();
		updateNavHyBridHTML();

	});

	/**
	 * [商品分类组件]->添加商品来源(商品分类)
	 */
	$("input[name='goods_classify']").live("change",function(){

		var goods_classify_list = $(this).parent().parent().parent().parent().find(".goods-classify-list>ul");
		var goods_classify_name = $(this).next().text();
		var goods_classify_id = $(this).val();
		var html = '';

		if($(this).is(":checked")){

			html += '<li data-classify-id="' + goods_classify_id + '" data-classify-name="' + goods_classify_name + '" data-show-count="10">';
				html += '<span>商品来源：<em>' + goods_classify_name + '</em></span>';
				html += '<div>';
					html += '<span>显示数量</span>';
					html += '<div class="dropdown">';
						html += '<a class="dropdown-toggle" data-toggle="dropdown" href="#"><span>10</span><b class="caret"></b></a>';
						html += '<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">';
							html += '<li>5</li>';
							html += '<li>10</li>';
							html += '<li>15</li>';
							html += '<li>30</li>';
							html += '<li>50</li>';
							html += '<li>100</li>';
						html += '</ul>';
					html += '</div>';
				html += '</div>';
			html += '</li>';
			goods_classify_list.append(html);

		}else{

			goods_classify_list.find('li[data-classify-id="' + goods_classify_id +'"]').remove();

		}

		bindGoodsClassifyData();

	});

	/**
	 * [商品分类组件]->商品来源设置显示数量
	 */
	$(".goods-classify-list .dropdown-menu li").live("click",function(){

		$(this).parent().parent().find("a>span").text($(this).text());
		$(this).parent().parent().parent().parent().attr("data-show-count",$(this).text());
		bindGoodsClassifyData();

	});

	/**
	 * [底部菜单组件]->菜单名称编辑
	 */
	$(".js-footer-menu-name").live("keyup",function(){

		var index = $(this).parent().parent().attr("data-index");
		updateFooterHTML(index,"label",$(this).val());
		bindFooterData();

	});

	/**
	 * [辅助线组件]->边框颜色编辑
	 */
	$(".js-border-color").live("change",function(){

		getCustom().children("hr").css("border-top-color",$(this).val());
		bindAuxiliaryLineData();

	});

	/**
	 * [辅助空白组件]->空白高度编辑
	 */
	$(".js-blank-height").live("keyup",function(){
		
		var value = $(this).val();
		if(isNaN(value)){

			$(this).val(parseFloat(value));
			showTip("请输入数字","warning");

		}
		if(value<$Default.auxiliaryBlankHeightMin){

			$(this).val($Default.auxiliaryBlankHeightMin);
			showTip("最小空白高度为" + $Default.auxiliaryBlankHeightMin + "px","warning");

		}

		if(value>$Default.auxiliaryBlankHeightMax){

			$(this).val($Default.auxiliaryBlankHeightMax);
			showTip("最大空白高度为" + $Default.auxiliaryBlankHeightMax + "px","warning");

		}

		value = $(this).val();
		bindAuxiliaryBlankData();

		getCustom().css("height",value).children("p").css("line-height",value + "px");
	});

	/**
	 * [自定义模块组件]->
	 */
	$(".js-select-custom-module").live("change",function(){

		var module_name= $(this).find("option:checked").attr("data-module-name");
		getCustom().find("article>p").text(module_name);
		bindCustomModuleData();

	});

	/**
	 * 删除组件
	 */
	$(".control-delete").live("click",function(){

		var control_name = $(this).attr("data-control-name");
		$(this).parent().parent().remove();
		setControlIsDisabled($("li[data-control-name='" + control_name + "']"),control_name);
		$(".pt.pt-left").hide();
		$("#richText").hide();//隐藏百度编辑器
		$(".custom-main .draggable-element").removeAttr("data-is-show").removeClass("selected");
		draggableElementClick($(".custom-main .draggable-element:last"),"select");

		return false;//防止事件冒泡

	});

	/**
	 * 重置编辑，同时还原当前编辑组件的样式
	 * 目前只对颜色(包括文字颜色、背景颜色、边框颜色、输入框背景颜色)控件、文字大小控件进行还原
	 */
	$(".fa-refresh").live("click",function(){

		var control = $(this).parent().children("[data-jsclass]");
		var jsclass = control.attr("data-jsclass");//要还原的对象
		var value = control.attr("data-default-value");//默认值

		switch(jsclass){

			//文字大小还原
			case "js-select-font-size":

				$(".js-select-font-size").find("option[value='" + value + "']").attr("selected",true).siblings().removeAttr("selected");
				$(".js-select-font-size").change();//调用文字大小改变事件

				break;

			//文字颜色还原，背景颜色，输入框背景颜色
			default:

				$("." + jsclass).val(value).change();

				break;

		}

	});

	/**
	 * [优惠券组件]->样式选择
	 */
	$("input[name='coupons_style']").live("click",function(){

		bindCouponsData();
		updateCouponsHTML();

	});

	/**
	 * [视频组件]->视频地址编辑
	 * 1、解决输入链接地址，播放报错问题
	 */
	$(".js-video-url").live("blur",function(){

		var value = $(this).val();
		var video = getCustom().find("video").attr("id");

		if(value.length>0){

			if(validateDomainName(value)){

				$("#video_url").val(value);
				$(".video-url-info span").text(value);
				var myPlayer = videojs(video);

				videojs(video).ready(function(){
					var myPlayer = this;
					myPlayer.src(value);
					myPlayer.load(value);
					myPlayer.play();
					setTimeout(function(){

						if(!getCustom().find(".vjs-error-display").hasClass("vjs-hidden")){

							$("#video_url").val("");//video.js Line:7873
							showTip("媒体不能加载，要么是因为服务器或网络失败，要么是因为格式不受支持。","error");

						}

						bindVideoData();

					},1000);
				});

			}else{

				showTip("链接地址错误","warning");

			}
		}

	});

	/**
	 * [橱窗组件]->布局方式
	 * 
	 * 1、[2列、3列]
	 */
	$("input[name='show-case-layout']").live("click",function(){

		var control_name = getCustom().attr("data-custom-flag");
		var clearance_checked = parseInt($("input[name='show-case-clearance']:checked").val());//图片间隙
		var show_case = getCustom().attr("data-show-case");

		if(!empty(show_case)) show_case = eval("(" + show_case + ")");

		var value = parseInt($(this).val());
		var html = "";

		if(value == 2){

			html = getShowCaseDefaultHTML(show_case);
			$(".pt-left .cont .show-case-pre").remove();
			$(".pt-left .cont").prepend(getShowCasePreHTML(2));

		}else if(value == 3){

			html = getShowCaseMultipleColumnsHTML(show_case);
			$(".pt-left .cont .show-case-pre").remove()
			$(".pt-left .cont").prepend(getShowCasePreHTML(3));

		}

		html += getCommonHTML(control_name);
		getCustom().html(html);
		bindShowCaseData();

	});
	
	/**
	 * [橱窗组件]->图片间隙
	 * 
	 */
	$("input[name='show-case-clearance']").live("click",function(){

		var value = parseInt($(this).val());
		var layout_checked = $("input[name='show-case-layout']:checked").val();//当前选中的布局方式

		if(value){

			if(layout_checked == 2) getCustom().find(".small").removeClass("clear");
			else if(parseInt(layout_checked) == 3) getCustom().find("li").removeClass("clear");

		}else{

			if(layout_checked == 2) getCustom().find(".small").addClass("clear");
			else if(parseInt(layout_checked) == 3) getCustom().find("li").addClass("clear");
		}

		bindShowCaseData();

	});
	
	/**
	 * 上下边距
	 */
	$(".js-padding").live("keyup",function(){

		var value = $(this).val();
		if(isNaN(value)){

			$(this).val(parseFloat(value));
			showTip("请输入数字","warning");

		}

		if(value<0){

			$(this).val(0);
			showTip("必须大于等于0px","warning");

		}

		if(value>200){

			$(this).val(200);
			showTip("不得超过200px","warning");

		}

		value = $(this).val();
		getCustom().css("padding",value + "px 0");

		try {

			eval("bind" +　getCustom().attr("data-custom-flag") + "Data()");

		} catch (error) {

			console.log("change padding error: " + error);

		}

	});
	
	/**
	 * [橱窗组件]->是否显示文字
	 */
	$("input[name^='show-case-show-text']").live("click",function(){

		var value = $(this).val();
		var index = $(this).parent().parent().parent().attr("data-index");//当前编辑的下标

		if(value == 1){

			$(this).parent().parent().next().css("visibility","visible");
			getCustom().find("li:eq(" + index + ") p").show();

		}else{

			$(this).parent().parent().next().css("visibility","hidden");
			getCustom().find("li:eq(" + index + ") p").hide();

		}

		bindShowCaseData();
	});

	/**
	 * [橱窗组件]->文字内容编辑
	 */
	$(".js-show-case-text").live("keyup",function(){

		var index = $(this).parent().parent().attr("data-index");
		var value = $(this).val();
		getCustom().find("li:eq(" + index + ") p").text(value);
		bindShowCaseData();

	});

	/**
	 * 图片删除
	 */
	$(".js-del-img").live("click",function(){

		var index = $(this).parent().find("span>input[type='file']").attr("data-index");
		//删除显示的图片
		$(this).parent().children(".img-block").hide().children("img").removeAttr("src");
		$(this).parent().children("p").show();
		$(this).parent().find("span>input[type='hidden']").val("");

		switch(getCustom().attr("data-custom-flag")){

			case controlList.NavHyBrid:

				updateNavHyBridHTML();

				break;

			case controlList.Footer:

				updateFooterHTML(index,"img","");

				break;

			case controlList.GoodsList:
			
				//商品列表中的购买按钮样式
				$("#show_buy_button_style4").val("");
				getCustom().find(".control-goods-buy-style>img").attr("src","");

				break;

			case controlList.GoodsClassify:

				$("#show_buy_button_style4").val("");
				getCustom().find(".control-goods-buy-style>img").attr("src","");

				break;

			case controlList.ShowCase:

				getCustom().find("li:eq(" + index + ")").css({"background" : "url(" + __IMG(res.data) + ") no-repeat center/100% "}).children("div").css("visibility","hidden");

				break;
			case controlList.Notice:

				var notice = getCustom().attr("data-notice");
				if(!empty(notice)){
					notice = eval("(" + notice + ")");
					getCustom().css("background",notice.bg_color);
				}else{
					getCustom().css("background","");
				}

				break;

			case controlList.TextNavigation:
				
				var text_navigation = getCustom().attr("data-text-navigation");

				if(!empty(text_navigation)){

					text_navigation = eval("(" + text_navigation + ")");
					getCustom().css("background",text_navigation.bg_color);

				}else{

					getCustom().css("background","");

				}

				break;

			case controlList.Title:

				var title = getCustom().attr("data-title");

				if(!empty(title)){

					title = eval("(" + title + ")");
					getCustom().css("background",title.bg_color);

				}else{

					getCustom().css("background","");

				}

				break;
		}

		eval("bind" + getCustom().attr("data-custom-flag") + "Data()");
		$(this).hide();

		return false;

	});
	
	/**
	 * 底部菜单进入离开切换图片
	 */
	$(".control-footer ul li").live("mouseover",function(){

		var footer = $(this).parent().parent().attr("data-footer");
		var index = $(this).attr("data-index");

		if(!empty(footer)){

			footer = eval(footer);
			$(this).children("img").attr("src",__IMG(footer[index].img_src_hover));

		}

	}).live("mouseout",function(){

		var footer = $(this).parent().parent().attr("data-footer");
		var index = $(this).attr("data-index");

		if(!empty(footer)){

			footer = eval(footer);
			$(this).children("img").attr("src",__IMG(footer[index].img_src));

		}

	});
	
	/**
	 * 公告内容
	 */
	$(".js-notice-content").live("keyup",function(){

		bindNoticeData();
		updateNoticeHTML();

	});
	
	/**
	 * 【公告】：选择滚动方式
	 */
	$(".js-select-notice-scroll-way").live("change",function(){

		bindNoticeData();
		updateNoticeHTML();

	});

	/**
	 * 添加一个公告
	 */
	$(".notice-new-addition .add-notice").live("click",function(){

		var index = $(".notice-items").length;
		var add_notice = '<span class="add-notice">+</span>';
		$(this).remove();
		$(".control-edit.notice-new-addition").append(getNoticeItemsHTML("『公告内容』" + index,true,"") + add_notice);
		bindNoticeData();
		updateNoticeHTML();

		if($(".notice-items").length>20) $(".notice-new-addition .add-notice").slideUp();

	});

	$(".notice-close").live("click",function(){

		$(this).parent().remove();
		bindNoticeData();
		updateNoticeHTML();
	});
	
	/**
	 * 【文本导航】->选中排列方式
	 */
	$(".js-nav-arrangement").live("change",function(){

		bindTextNavigationData();
		updateTextNavigationHTML();

	});
	
	/**
	 * [文本导航]->新添加一个横排文本导航
	 */
	$(".text-navigation-new-addition .add-text-navigation").live("click",function(){

		var index = $(".control-edit.text-navigation-block").length;
		var html = getTextNavigationBlockHTML($cValue.textNavigation.value + index) + '<span class="add-text-navigation">+</span>';
		$(this).remove();
		$(".text-navigation-new-addition").append(html);

		bindTextNavigationData();
		updateTextNavigationHTML();
		
		if($(".control-edit.text-navigation-block").length>20) $(".text-navigation-new-addition .add-text-navigation").slideUp();

	});
	
	/**
	 * [文本导航]->删除一个文本导航
	 */
	$(".text-navigation-close").live("click",function(){

		$(this).parent().remove();
		bindTextNavigationData();
		updateTextNavigationHTML();
		
	});
	
	/**
	 * [标题]->是否加粗
	 */
	$("input[name='whether_bold']").live('change',function(){
		
		if($(this).val() == 1) getCustom().find("h4[data-editable]").css("font-weight","bold");
		else getCustom().find("h4[data-editable]").css("font-weight","normal");
		
		bindTitleData();
	});

	$("input[name='line_columns']").live("click",function(){

		var html = '';
		var count = $(this).val();
		var curr_length = $(".nav-hybrid-list").children().length;
		count -= curr_length;

		if(count>0){

			for(var i=0;i<count;i++){

				var curr = {
					index : $(".nav-hybrid-list").children().length+i+1,
					text : "",
					src : "",
					href : $Default.href
				};
				html += getNavHyBridItemHTML(curr);
			}

			$(".nav-hybrid-list").append(html);

		}else{

			var results = curr_length - Math.abs(count);
			$(".nav-hybrid-list").children(":gt(" + (results-1) + ")").remove();

		}

		bindNavHyBridData();
		updateNavHyBridHTML();

	});

});

/**
 * 读取数据库中数据
 */
function loadData(){

	if(!empty(template_data)){

		for(var i=0;i<template_data.length;i++){

			var control_name = template_data[i].control_name;//组件名称
			var control_data = eval("(" + template_data[i].control_data + ")");//数据
			var self = $(".plug-in li[data-control-name='" + control_name + "']");//当前组件
			var additional_attr = "";//附加属性

			if(control_name == controlList.GoodsSearch){

				goods_search = eval("(" + control_data.goods_search + ")");
				additional_attr += "data-goods-search='" + control_data.goods_search + "' ";
				additional_attr += 'style="background:' + goods_search.bg_color + ';"';

			}else if(control_name == controlList.GoodsList){

				additional_attr = "data-goods-list='" + control_data.goods_list + "'";

			}else if(control_name == controlList.Title){

				var title = eval("(" +control_data.title + ")");
				additional_attr = "data-title='" + control_data.title + "' ";
				additional_attr += 'style="padding: ' + title.padding + 'px 0;background:url(' + __IMG(title.bg_img) + ') 50% / 100% no-repeat ' + title.bg_color + ';"';

			}else if(control_name == controlList.AuxiliaryLine){

				additional_attr = "data-auxiliary-line='" + control_data.auxiliary_line + "'";

			}else if(control_name == controlList.AuxiliaryBlank){

				var auxiliary_blank = eval("(" + control_data.auxiliary_blank + ")");

				additional_attr = "data-auxiliary-blank='" + control_data.auxiliary_blank + "'";
				additional_attr += ' style="height:' + auxiliary_blank.height + 'px;background:' + auxiliary_blank.bg_color + ';"';

			}else if(control_name == controlList.Notice){

				var notice = eval("(" + control_data.notice + ")");
				additional_attr = 'style="padding: ' + notice.padding + 'px 0;background:url(' + __IMG(notice.bg_img) + ') 50% / 100% no-repeat ' + notice.bg_color + ';"';
				additional_attr += "data-notice='" + control_data.notice + "'";

			}else if(control_name == controlList.ImgAd){

				additional_attr = 'id="carouselImgAd"';
				additional_attr += " data-img-ad='" + control_data.img_ad + "'";

			}else if(control_name == controlList.TextNavigation){

				var text_navigation = eval("(" + control_data.text_navigation + ")");
				additional_attr = "data-text-navigation='" + control_data.text_navigation + "'";
				additional_attr += 'style="padding: ' + text_navigation.padding + 'px 0;background:url(' + __IMG(text_navigation.bg_img) + ') 50% / 100% no-repeat ' + text_navigation.bg_color + ';"';

			}else if(control_name == controlList.NavHyBrid){

				var nav_hybrid = eval("(" + control_data.nav_hybrid + ")");
				additional_attr = "data-nav-hybrid='" + control_data.nav_hybrid + "'";
				additional_attr += ' style="background:' + nav_hybrid.bg_color + ';"';

			}else if(control_name == controlList.GoodsClassify){

				additional_attr = "data-goods-classify='" + control_data.goods_classify + "'";

			}else if(control_name == controlList.Footer){

				additional_attr = "data-footer='" + control_data.footer + "'";

			}else if(control_name == controlList.RichText){

				//将双引号字符实体替换一下，防止浏览器解析导致显示错误
				var text = control_data.rich_text.replace(/&quot;/g,"&niu_quot;");
				additional_attr = "data-rich-text='" + text + "'";

			}else if(control_name == controlList.CustomModule){

				additional_attr = "data-custom-module='" + control_data.custom_module + "'";

			}else if(control_name == controlList.Coupons){

				additional_attr = "data-coupons='" + control_data.coupons + "'";

			}else if(control_name == controlList.Video){

				additional_attr = "data-video='" + control_data.video + "'";
				var video = eval("(" + control_data.video + ")");
				additional_attr += " style='padding:" + video.padding + "px 0;'";

			}else if(control_name == controlList.ShowCase){

				additional_attr = "data-show-case='" + control_data.show_case + "'";
				var show_case = eval("(" + control_data.show_case + ")");
				additional_attr += " style='padding:" + show_case.padding + "px 0;'";

			}

			if(!empty(self)){

				additional_attr += " data-sort=" + template_data[i].sort;
				addControl(self.attr("data-additional-attr",additional_attr),control_data);

			}
		}
	}
}

/**
 * 初始化分辨率数据
 */
function initCustomResolution(){
	$(".custom-resolution button").each(function(){
		$(this).text($(this).attr("data-width") + "*" + $(".custom-template").height());
	})
}

/**
 * 获取当前编辑的组件
 */
function getCustom(){
	return $(".custom-main>.selected");
}

/**
 * 非空判断
 * @param s
 * @returns {Boolean}
 */
function empty(s){
	return (s == undefined || s == "") ? 1 : 0;
}

/**
 * 验证域名
 * @param str
 * @returns boolean
 */
function validateDomainName(str){
	return true;
//	if(str.indexOf("") != -1) return true;
//	else return false;
	// var strRegex = "^((https|http|ftp|rtsp|mms)?://)"
	// + "?(([0-9a-z_!~*'().&=+$%-]+: )?[0-9a-z_!~*'().&=+$%-]+@)?"
	// + "(([0-9]{1,3}\.){3}[0-9]{1,3}"
	// + "|"
	// + "([0-9a-z_!~*'()-]+\.)*"
	// + "([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\."
	// + "[a-z]{2,6})"
	// + "(:[0-9]{1,4})?"
	// + "((/?)|"
	// + "(/[0-9a-z_!~*'().;?:@&=+$,%#-]+)+/?)$";
	// return new RegExp(strRegex).test(str);
}

function getShowCaseData(event_obj){
	var obj = { show_case : event_obj.attr("data-show-case") };
	return JSON.stringify(obj);
}

function getVideoData(event_obj){
	var obj = { video : event_obj.attr("data-video") };
	return JSON.stringify(obj);
}

/**
 * 获取模板数据(json)
 */
function getTemplateData(){
	var data = new Array();
	$(".custom-main .draggable-element").each(function(i){

		var obj = new Object();
		obj.sort = (i+1);
		//如果有底部菜单，则永远在最后
		obj.control_name = $(this).attr("data-custom-flag");
		obj.control_data = eval("get" + obj.control_name + "Data($(this))");
		data.push(obj);
		
	})
//	console.log(data);
	return JSON.stringify(data);
}

function getCouponsData(event_obj){
	var obj = { coupons : event_obj.attr("data-coupons") };
	return JSON.stringify(obj);
}

function getAuxiliaryBlankData(event_obj){
	var obj = { auxiliary_blank : event_obj.attr("data-auxiliary-blank") };
	return JSON.stringify(obj);
}

function getAuxiliaryLineData(event_obj){

	var obj = { auxiliary_line : event_obj.attr("data-auxiliary-line") };
	return JSON.stringify(obj);
}

function getRichTextData(event_obj){
	var rich_text = event_obj.attr("data-rich-text");
	if(!empty(rich_text)) rich_text = rich_text.toString().replace("'",'"');//replace(/(\n)/g, "").replace(/(\t)/g, "").replace(/(\r)/g, "").replace(/\s*/g, "")
	var obj = { rich_text : rich_text };
	return JSON.stringify(obj);
}

function getCustomModuleData (event_obj){
	var obj = { custom_module : event_obj.attr("data-custom-module") };
	return JSON.stringify(obj);
}

function getFooterData(event_obj){
	var obj = { footer : event_obj.attr("data-footer") };
	return JSON.stringify(obj);
}

function getGoodsClassifyData(event_obj){
	var obj = { goods_classify : event_obj.attr("data-goods-classify") };
	return JSON.stringify(obj);
}

function getNavHyBridData(event_obj){
	var obj = { nav_hybrid : event_obj.attr("data-nav-hybrid") };
	return JSON.stringify(obj);
}

function getImgAdData(event_obj){
	var obj = { img_ad : event_obj.attr("data-img-ad") };
	return JSON.stringify(obj);
}

function getNoticeData(event_obj){
	var obj = { notice : event_obj.attr("data-notice") };
	return JSON.stringify(obj);
}

function getTextNavigationData(event_obj){
	var obj = { text_navigation : event_obj.attr("data-text-navigation") };
	return JSON.stringify(obj);
}

function getTitleData(event_obj){
	var obj = { title : event_obj.attr("data-title") };
	return JSON.stringify(obj);
}

function getGoodsListData(event_obj){
	var obj = { goods_list : event_obj.attr("data-goods-list") };
	return JSON.stringify(obj);
}

function getGoodsSearchData(event_obj){
	var obj = { goods_search : event_obj.attr("data-goods-search") };
	return JSON.stringify(obj);
}

/**
 * 绑定右侧弹出层
 * 1、部分组件由于不需要验证，所以每次打开都要进行绑定数据
 * 
 * @param obj 当前拖拽对象,status ：add:添加,select：选择,validation：验证
 */
function draggableElementClick(obj,status){

	var self = $(obj);
	showBottom();
	
	if(!empty(self.attr("data-custom-flag"))){

		//选中当前点击的组件，清除其他组件的选中样式及状态
		$(".custom-template header").removeAttr("data-is-show");

		if(self.attr("data-custom-flag") == "CustomTemplateName"){

			$(".custom-main>div").removeAttr("data-is-show").removeClass("selected");

		}else{

			self.addClass("selected").siblings().removeAttr("data-is-show").removeClass("selected");

		}
		switch(status){
		case "add":
			//每次添加跳转到末尾
//			$("html,body").animate({
//				scrollTop : self.attr("data-scroll-top")
//			},300);
			break;
		case "select":
			//不进行任何操作
			break;
		case "validation":
			$(window).scrollTop(self.attr("data-scroll-top") - self.height());
			break;
		}

		//如果选择的组件已经打开，无需重新创建
		if($(self).attr("data-is-show") == 1) return;

		var pt_align = "t";
		if(self.attr("data-custom-flag") == controlList.Footer) pt_align = "r";

		$.pt({
			target : self,
			position : 'r',
			align : pt_align,
			width : 500,
			autoClose : false,
			content : $edit.init(self.attr("data-custom-flag")),
			open : function(r){

				self.attr("data-is-show",1);//显示标识
				$("input[type='file']").attr("title"," ");//清空文件上传的提示信息
				$("#richText").hide();//隐藏百度编辑器
				$(".pt-left .cont").css("min-height","");//还原右侧编辑栏的高度
				$(".pt-left").css("left",($(".pt-left").offset().left + 10));
				try {

					eval("bind" + self.attr("data-custom-flag") + "Data()");

				} catch (error) {

					console.log("show after error:" + error);
				}
			}
		});
	}
}

function noticeScrollUpDown(){
	
	if($(".js-select-notice-scroll-way").val() == 2){

		var liHeight = getCustom().find(".notice-block").height();//一个li的高度
		//获取li的总高度再减去一个li的高度(再减一个Li是因为克隆了多出了一个Li的高度)
		var totalHeight = (getCustom().find(".notice-block ul li").length *  getCustom().find(".notice-block ul li").eq(0).height()) -liHeight;
		getCustom().find(".notice-block ul").height(totalHeight);//给ul赋值高度

		//清除上一个
		notice_index = 0;
		clearInterval(notice_autoTimer);

		var temp = true;
		var notice = getCustom().attr("data-notice");
		var sort = getCustom().index();
		var items = null;
		if(!empty(notice)){
			notice = eval("(" + notice + ")");
			items = notice.items;
		}

		//自动轮播
		notice_autoTimer = setInterval(function(){

			if(temp && items.length>1) {
				$(".custom-main .draggable-element:eq(" + (sort-1) + ")").find(".notice-block ul li:eq(0)").clone(true).appendTo($(".custom-main .draggable-element:eq(" + (sort-1) + ")").find(".notice-block ul"));//克隆第一个放到最后(实现无缝滚动)
				temp = false;
			}
			notice_index++;
			if(notice_index > $(".custom-main .draggable-element:eq(" + (sort-1) + ")").find(".notice-block ul li").length -1) {//判断notice_index为最后一个Li时notice_index为0
				notice_index = 0;
			}
			$(".custom-main .draggable-element:eq(" + (sort-1) + ")").find(".notice-block ul").stop().animate({
				top: -notice_index * liHeight
			},500,function(){
				if(notice_index == $(".custom-main .draggable-element:eq(" + (sort-1) + ")").find(".notice-block ul li").length -1) {
					$(".custom-main .draggable-element:eq(" + (sort-1) + ")").find(".notice-block ul").css({top:0});
					notice_index = 0;
				}
			});

		},3000);

	}
}

/**
 * 验证组件
 * 
 */
function validation(){
	var control = eval(getTemplateData());
	var flag = false;//验证标识：true，失败，false：成功
	
	if(empty($(".custom-template header>h4").attr("data-custom-template-name"))){
		showTip("模板名称不能为空","warning");
		draggableElementClick($(".custom-template header"),"validation");
		$(".js-custom-template-name").focus();
		return false;
	}
	
	if(!empty(control) && control.length){
		
		for(var i=0;i<control.length;i++){
			
			var data = eval("(" + control[i].control_data + ")");
		
			if(control[i].control_name == controlList.GoodsList){
				
				var goods_list = eval("(" + data.goods_list + ")");
				if(goods_list.goods_source == 0){

					flag = true;
					$(".js-goods-source").focus();
					showTip("没有发现商品来源，请先去添加商品分类","warning");

				}
				if(goods_list.goods_buy_button_style == 4){

					if(empty(goods_list.goods_buy_button_src)){

						flag = true;
						showTip("请上传自定义的购买按钮图片","warning");

					}
				}
				
			}else if(control[i].control_name == controlList.Title){

				var title = eval("(" + data.title + ")");
				if(!empty(title)){

					if(!empty(title.title_name)){

						if(title.title_name.length>100){

							flag = true;
							showTip($cValue.title.name + "不得超过100个字符","warning");
							$(".js-title-name").focus();

						}

					}else{

						flag = true;
						showTip($cValue.title.name + "不能为空","warning");
						$(".js-title-name").focus();

					}

					if(title.subtitle_name.length>500){

						flag = true;
						showTip($cValue.subTitle.name + "不得超过500个字符","warning");
						$(".js-subtitle-name").focus();

					}

				}else{

					flag = true;
					showTip($cValue.title.name + "不能为空","warning");
					$(".js-title-name").focus();

				}

			}else if(control[i].control_name == controlList.TextNavigation){

				var text_navigation = eval("(" + data.text_navigation + ")");
				if(!empty(text_navigation)){

					for(var k=0;k<text_navigation.items.length;k++){

						if(!empty(text_navigation.items[k].text)){

							if(text_navigation.items[k].text.length>50){

								flag = true;
								showTip($cValue.textNavigation.name + "不得超过50个字符","warning");
								$(".control-edit.text-navigation-block:eq(" + k + ")").find("input").select();
								break;

							}

						}else{

							flag = true;
							showTip($cValue.textNavigation.name + "不能为空","warning");
							$(".control-edit.text-navigation-block:eq(" + k + ")").find("input").select();
							break;

						}
					}

				}else{

					flag = true;
					showTip($cValue.textNavigation.name + "不能为空","warning");
					$(".control-edit.text-navigation-block:eq(0)").find("input").select();

				}

			}else if(control[i].control_name == controlList.ImgAd){

				var img_ad = eval(data.img_ad);
				var adv_show_type = 1;
				var img_count = 0;
				if(img_ad && img_ad.length>0){

					adv_show_type = img_ad[0].adv_show_type;

					for(var j=0;j<img_ad.length;j++){

						if(!empty(img_ad[j].src)) img_count++;

					}

					if(adv_show_type == 1 && img_count < 1){

						flag = true;
						showTip("至少上传一张图片","warning");

					}else if(adv_show_type == 2 && img_count < 2){

						flag = true;
						showTip("至少上传两张图片","warning");

					}

				}else{

					flag = true;
					showTip("至少上传一张图片","warning");

				}

			}else if(control[i].control_name == controlList.NavHyBrid){

				var nav_hybrid = eval("(" + data.nav_hybrid + ")");
				if(!empty(nav_hybrid)){

					for(var j=0;j<nav_hybrid.items.length;j++){

						if(empty(nav_hybrid.items[j].text) && empty(nav_hybrid.items[j].src)){

							flag = true;
							$(".control-edit.nav-hybrid:eq(" + j + ")").css("border","2px dashed #FF5722");
							showTip("请填写图文导航数据","warning");
							break;

						}else{

							$(".control-edit.nav-hybrid:eq(" + j + ")").css("border","1px solid #e5e5e5");

						}
					}

				}else{

					flag = true;
					showTip("请检查图文导航数据","warning");

				}

			}else if(control[i].control_name == controlList.GoodsClassify){

				var goods_classify = eval(data.goods_classify);

				if(!empty(goods_classify)){
					
					if(goods_classify[0].goods_buy_button_style == 4){
				
						if(empty(goods_classify[0].goods_buy_button_src)){

							flag = true;
							showTip("请上传自定义的购买按钮图片","warning");

						}

					}

				}else{

					flag = true;
					showTip("至少选择一个商品分类","warning");

				}

			}else if(control[i].control_name == controlList.Footer){

				var footer = eval(data.footer);
				var footer_menu_name_count = 0;
				var footer_menu_src_count = 0;

				if(!empty(footer) && footer.length>0){

					for(var j=0;j<footer.length;j++){

						if(!empty(footer[j].menu_name)) footer_menu_name_count++;
						if(!empty(footer[j].img_src)) footer_menu_src_count++;

					}

					if(footer_menu_name_count < footer.length && footer_menu_src_count < footer.length){

						flag = true;
						showTip("底部菜单不能为空","warning");

					}

				}else{

					flag = true;
					showTip("底部菜单不能为空","warning");

				}
				
			}else if(control[i].control_name == controlList.RichText){

				if(!empty(data.rich_text)){
					
					if(data.rich_text.length>280000){

						flag = true;
						showTip("字数超出最大允许值！","warning");

					}

				}else{

					flag = true;
					showTip("富文本内容不能为空","warning");

				}
				
			}else if(control[i].control_name == controlList.CustomModule){

				var custom_module = eval("(" + data.custom_module + ")");

				if(!empty(custom_module)){

					if(custom_module.module_id == 0){

						flag = true;
						showTip("没有发现自定义模块","warning");

					}
				}

			}else if(control[i].control_name == controlList.Video){

				var video = eval("(" + data.video + ")");

				if(!empty(video)){

					if(empty(video.url)){

						flag = true;
						showTip("请检查上传的视频文件是否正确，文件大小不能超过500MB！","warning");

					}
				}

			}else if(control[i].control_name == controlList.ShowCase){

				var show_case = eval("(" + data.show_case + ")");

				if(!empty(show_case)){

					for(var j=0;j<show_case.itemList.length;j++){

						var curr = show_case.itemList[j];

						if(empty(curr.src)){

							flag = true;
							showTip("请上传图片","warning");
							break;

						}

					}

				}

			}else if(control[i].control_name == controlList.Notice){
				
				var notice = eval("(" + data.notice + ")");

				if(!empty(notice)){

					for(var j=0;j<notice.items.length;j++){
						if(empty(notice.items[j].notice)){
							flag = true;
							showTip("请输入公告内容","warning");
							$(".notice-items:eq(" + j + ")").find("textarea").focus();
							break;
						}else if(notice.items[j].notice.length>200){

							flag = true;
							showTip("公告内容不得超出个200字符","warning");
							$(".notice-items:eq(" + j + ")").find("textarea").focus();
							break;
						}
					}

				}else{

					flag = true;
					showTip("请输入公告内容","warning");

				}

			}

			if(flag){

				//发现错误，跳转到错误组件位置
				draggableElementClick($(".custom-main .draggable-element:eq(" + (control[i].sort-1) + ")"),"validation");

				break;

			}
		}
	}else{

		showTip("您还没有添加自定义模板哦","warning");
		return false;

	}

	if(flag) return false;

	return true;
}

/**
 * 验证汉字长度
 * @param str 要验证的字符串
 * @returns
 */
function testChinese(str) {
	return /^[a-zA-Z-0-9]{1,10}$/.test((str + '').replace(/[\u4e00-\u9fa5]/g, 'aa'));
}

function showBottom(){
	var body_height = $("body").height();
	var window_scroll_top = $(window).scrollTop() + 50;
	var h = $(document).height()-$(window).height();
	//拥有一定高度时，进行控制浮动按钮是否显示
	if(body_height>1500){
		if(window_scroll_top <= h){
			 $(".js-mask-btn").show();
		}else{
			$(".js-mask-btn").hide();
		}
	}else{
		$(".js-mask-btn").hide();

	}
}

/**
 * 预加载一些百度编辑器
 */
function preloadBaiDuEditor(){
	var html = '<script id="richText" type="text/plain" style="width: 500px; height: 500px;display:none;"></script>';
	html += '<script>var ue = UE.getEditor("richText");';
	
	html += 'ue.ready(function() {';
		html += 'if(getCustom().attr("data-custom-flag") == "RichText" && empty(getCustom().attr("data-rich-text"))){ ue.setContent("『富文本』");';
		html += ' getCustom().attr("data-rich-text","『富文本』"); }';
	html += '});'
	
	html += 'ue.addListener("mouseover",function(){';
		html += 'getCustom().attr("data-rich-text",ue.getContent()).find("article").html(ue.getContent());';
	html += '});';
	
	html += 'ue.addListener("keyup",function(){';
		html += 'getCustom().attr("data-rich-text",ue.getContent()).find("article").html(ue.getContent());';
	html += '});';

	html += '$(".CodeMirror").live("mouseover",function(){';
		html += 'getCustom().attr("data-rich-text",ue.getContent()).find("article").html(ue.getContent());';
	html += '})';
	
	html += '</script>';
	$("body").append(html);
}

/**
 * 获取随机价格，演示用
 * @returns int
 */
function getRandomPrice(){
	return (Math.random()*999).toFixed(2);
}
/**
 * 添加组件
 * 
 * @param self：当前选择的组件，control_data：数据库中返回的数据
 */
function addControl(self,control_data){
	
	//获取当前控件
	var control_name = $(self).attr("data-control-name");
	
	//组件类名，多个逗号隔开
	var class_name = $(self).attr("data-class-name");
	
	//组件附加属性（部分组件用到id属性，例如：轮播图）
	var additional_attr = empty($(self).attr("data-additional-attr")) ? "" : $(self).attr("data-additional-attr");
	
	//要添加的DOM对象
	var custom_main = $(".custom-main");
	
	var html = '<div class="' + class_name+ '" ' + ' data-custom-flag="' + control_name + '" ' + additional_attr + '>';
	
	switch(control_name) {
	
		case controlList.GoodsSearch:

			var style = "";
			var placeholder = $cValue.searchPlaceholder.default_value;

			if(!empty(control_data)){

				var goods_search = eval("(" + control_data.goods_search + ")");
				style = 'style = "color:' + goods_search.text_color + ';';
				style += 'background-color:' + goods_search.input_bg_color + ';';
				style += 'font-size:' + goods_search.font_size + 'px;"';
				placeholder = goods_search.placeholder;

			}

			html += '<input type="text" placeholder="' + placeholder + '" data-editable="1" ' + style + '>';
			html += '<button class="control-btn-search"></button>';
			
			break;

		case controlList.GoodsList:

			//商品列表组件：大图样式、小图样式。默认大图样式

			if(!empty(control_data)){

				var goods_list = eval("(" + control_data.goods_list + ")");
				if(goods_list.goods_list_type == 1) html += getGoodsListBigStyleHTML(goods_list);
				else if(goods_list.goods_list_type == 2) html += getGoodsListSmallStyleHTML(goods_list);

			}else{

				html += getGoodsListBigStyleHTML();

			}

			break;

		case controlList.Title:

			var title_name = '『标题名』';
			var subtitle_name = '『副标题』';
			var style = "";

			if(!empty(control_data)){

				title = eval("(" + control_data.title + ")");
				title_name = title.title_name;
				subtitle_name = title.subtitle_name;
				style = 'style="color:' + title.text_color + ';text-align:' + align_array[title.text_align-1] + '"';

			}

			html += '<h4 data-editable="1" ' + style + '>' + title_name + '</h4>';
			html += '<p data-editable="1" ' + style + '>' + subtitle_name + '</p>';

			break;

		case controlList.AuxiliaryLine:

			if(!empty(control_data)){

				var auxiliary_line = eval("(" + control_data.auxiliary_line + ")");
				html += '<hr style="border-top-color: ' + auxiliary_line.border_color +';" />';

			}else html += '<hr/>';

			break;

		case controlList.AuxiliaryBlank:

			var line_height = $Default.auxiliaryBlankHeightMin;
			if(!empty(control_data)){
				var auxiliary_blank = eval("(" + control_data.auxiliary_blank + ")");
				line_height = auxiliary_blank.height;
			}

			html += '<p style="margin:0;color:#999999;text-align:center;font-size:12px;line-height:' + line_height + 'px">『辅助空白』</p>';

			break;

		case controlList.Notice:

			html += '<div class="notice-block">';
				html += '<ul>';
				if(!empty(control_data)){

					var notice = eval("("+control_data.notice+")");
					var style = 'style="font-size:' + notice.font_size + 'px;color:' + notice.text_color + ';text-align:' + align_array[notice.text_align-1] + ';"';

					if(notice.scroll_way == 0){

						html += '<li><a href="javascript:;" data-editable="1" ' + style + ' >' + notice.items[0].notice + '</a></li>';

					}else if(notice.scroll_way == 1){

						html += '<li><marquee data-editable="1" ' + style + '>' + notice.items[0].notice + '</marquee></li>';

					}else if(notice.scroll_way == 2){
						
						for(var k=0;k<notice.items.length;k++) html += '<li><a href="javascript:;" data-editable="1" ' + style + ' >' + notice.items[k].notice + '</a></li>';

					}

				}else{

					html += '<li><a href="javascript:;" data-editable="1" style="color:' + $cValue.noticeTextColor.value + '">『公告内容』</a></li>';

				}

				html += '</ul>';

			html += '</div>';
			break;

		case controlList.ImgAd:

			if(!empty(control_data)){

				var img_ad = eval(control_data.img_ad);
				if(img_ad[0]["adv_show_type"] == 1) html += getImgAdvSingleHTML(img_ad);
				else if(img_ad[0]["adv_show_type"] == 2) html += getImgAdvCarouselHTML(img_ad);

			}else{

				html += getImgAdvSingleHTML();

			}

			break;

		case controlList.TextNavigation:

			var nav_text = "『文本导航』";
			var style = "";

			if(!empty(control_data)){

				text_navigation = eval("(" + control_data.text_navigation + ")");
				style = 'style="color:' +text_navigation.text_color + ';font-size:' + text_navigation.font_size + 'px;text-align:' + align_array[text_navigation.text_align-1] + ';"';

				html += '<div>';

					if(text_navigation.arrangement == 1){

						//竖排
						html += '<h5 data-editable="1" ' + style + '>';
							html += '<span data-editable="1" ' + style + '>' + text_navigation.items[0].text + '</span>';
							html += '<i class="fa fa-angle-right" data-editable="1" ' + style + '></i>';
						html += '</h5>';

					}else{

						//横排
						html += '<ul class="text-navigation-horizontal">';
						for(var k=0;k<text_navigation.items.length;k++){
							
							html += '<li data-editable="1" ' + style + '>' + text_navigation.items[k].text + '</li>';
						}

						html += '</ul>';
						
					}

				html += '</div>';

			}else{

				html += '<div>';
					html += '<h5 data-editable="1">';
						html += '<span data-editable="1" ' + style + '>' + nav_text + '</span>';
						html += '<i class="fa fa-angle-right" data-editable="1" ' + style + '></i>';
					html += '</h5>';
				html += '</div>';

			}

			break;

		case controlList.NavHyBrid:

			html += '<ul>';

				if(!empty(control_data)){

					var nav_hybrid = eval("(" + control_data.nav_hybrid + ")");
					var class_name = "one-line-four-columns";
					if(nav_hybrid.type == 5) class_name = "one-line-five-columns";
					else if(nav_hybrid.type == 8) class_name = "two-line-four-columns";
					else if(nav_hybrid.type == 10) class_name = "two-line-five-columns";

					for(var i=0;i<nav_hybrid.items.length;i++){

						html += '<li class="' + class_name + '">';

							if(!empty(nav_hybrid.items[i].src)) html += '<div style="background:url(' + __IMG(nav_hybrid.items[i].src) + ') no-repeat 50% 50% / 100%;"></div>';
							if(!empty(nav_hybrid.items[i].text)) html += '<span data-editable="1" style="color:' + nav_hybrid.text_color + ';">' + nav_hybrid.items[i].text + '</span>';

						html += '</li>';

					}

				}

			html += '</ul>';

			break;

		case controlList.GoodsClassify:

			var temp_aside_count = 3;
			var temp_section_count = 4;
			var temp_common_html = '<div>';
			temp_common_html += '<span>此处是商品名称</span>';
			temp_common_html += '<em>￥' + getRandomPrice() + '</em>';
			temp_common_html += '<button class="control-goods-buy-style">';

			if(!empty(control_data)){

				goods_classify = eval(control_data.goods_classify);
				temp_common_html += '<img draggable="false" src="' + __IMG(goods_classify[0].goods_buy_button_src) + '"/>';

			}else{

				temp_common_html += '<img draggable="false" src="' + STATIC + "/wapCustomTemplate/images/goods_buy_button_style1.png" + '"/>';

			}

			temp_common_html += '</button>';
			temp_common_html += '</div>';
			html += '<aside>';
				html += '<ul>';

				if(!empty(control_data)){

					goods_classify = eval(control_data.goods_classify);
					for(var i=0;i<goods_classify.length;i++){

						if(i==0) html += '<li class="selected" title="' + goods_classify[i].name +'">' + goods_classify[i].name +'</li>';
						else html += '<li title="' + goods_classify[i].name +'">' + goods_classify[i].name +'</li>';

					}

				}else{

					for(var i=0;i<temp_aside_count;i++){

						if(i==0) html += '<li class="selected">商品分类一</li>';
						else if(i%3==1) html += '<li>商品分类二</li>';
						else if(i%3==2) html += '<li>商品分类N</li>';

					}

				}

				html += '</ul>';
			html += '</aside>';

			html += '<section>';
				html += '<ul>';

					for(var i=0;i<temp_section_count;i++){

						html += '<li>';
						if(i==0) html += '<div class="blue-bg">第一个商品</div>';
						else if(i%4==1) html += '<div class="pink-bg">第二个商品</div>';
						else if(i%4==2) html += '<div class="green-bg">第三个商品</div>';
						else if(i%4==3) html += '<div class="orange-bg">第N个商品</div>';
						html += temp_common_html;
						html += '</li>';

					}

				html += '</ul>';
			html += '</section>';

			break;

		case controlList.Footer:
			
			html += '<ul>';
			
			if(!empty(control_data)){
			
				var footer = eval(control_data.footer);
			
				if(!empty(footer)){

					for(var i=0;i<footer.length;i++){

						html += "<li data-index=" + i + ">";
						if(!empty(footer[i].img_src)) html += '<img draggable="false" src="' + __IMG(footer[i].img_src) + '">';
						if(!empty(footer[i].menu_name)) html += '<label>' + footer[i].menu_name + '</label>';
						html += "</li>";

					}

				}
			}else{

				var footer_data = [{
					menu_name : "首页",
					color : $cValue.footerTextColor.value,
					color_hover : $cValue.textColorHover.value,
					href : __URL(APPMAIN + '/index/index'),
					img_src : "public/static/wapCustomTemplate/images/control_footer_home.png",
					img_src_hover :  "public/static/wapCustomTemplate/images/control_footer_home_selected.png"
				},{
					menu_name : "商品分类",
					color : $cValue.footerTextColor.value,
					color_hover : $cValue.textColorHover.value,
					href : __URL(APPMAIN + '/goods/category'),
					img_src : "public/static/wapCustomTemplate/images/control_footer_classify.png",
					img_src_hover : "public/static/wapCustomTemplate/images/control_footer_classify_selected.png"
				},{
					menu_name : "购物车",
					color : $cValue.footerTextColor.value,
					color_hover : $cValue.textColorHover.value,
					href : __URL(APPMAIN + '/goods/cart'),
					img_src : "public/static/wapCustomTemplate/images/control_footer_cart.png",
					img_src_hover : "public/static/wapCustomTemplate/images/control_footer_cart_selected.png"
				},{
					menu_name : "会员中心",
					color : $cValue.footerTextColor.value,
					color_hover : $cValue.textColorHover.value,
					href : __URL(APPMAIN + '/member/index'),
					img_src :  "public/static/wapCustomTemplate/images/control_footer_user.png",
					img_src_hover : "public/static/wapCustomTemplate/images/control_footer_user_selected.png"
				}];

				for(var i=0;i<$Default.footerItemCount;i++){

					var curr = footer_data[i];
					html += '<li data-index=' + i + '>';
						html += '<img draggable="false" src="' + __IMG(curr.img_src) + '">';
						html += '<label data-editable="1">' + curr.menu_name + '</label>';
					html += '</li>';

				}
			}
			html += '</ul>';

			break;

		case controlList.Coupons:

			//优惠券
			var style = 1;
			if(!empty(control_data)){
				var coupons = eval("("+control_data.coupons+")");
				style = coupons.style;
			}
			if(style == 1){
				
				html += '<div class="coupon">';
					html += '<img src="' + STATIC + '/wapCustomTemplate/images/index_coupon.png' + '" class="background_img">';
					html += '<p><span>￥</span>' + getRandomPrice() + '</p>';
					html += '<img src="' + STATIC + '/wapCustomTemplate/images/already_received.png' + '" class="already_received">';
				html += '</div>';
	
				html += '<div class="coupon">';
					html += '<img src="' + STATIC + '/wapCustomTemplate/images/index_coupon.png' + '" class="background_img">';
					html += '<p><span>￥</span>' + getRandomPrice() + '</p>';
					html += '<img src="' + STATIC + '/wapCustomTemplate/images/already_received.png' + '" class="already_received">';
				html += '</div>';
			}else if(style == 2){
				for(var j=0;j<3;j++){
					html += '<div class="coupons-style2">';
						html += '<span class="money-number">￥' + getRandomPrice() + '</span>';
						html += '<p class="explanation">满15.00可用</p>';
						html += '<span class="get">领取</span>';
					html += '</div>';
				}
			}

			break;

		case controlList.RichText:

			var content = '<p>『富文本』</p>';
			if(!empty(control_data) && !empty(control_data.rich_text)) content = control_data.rich_text.replace(/&niu_quot;/g,"&quot;");
			html += '<article style="overflow:hidden;">' + content + '</article>';

			break;

		case controlList.CustomModule:

			var content = '『自定义模块』';

			if(!empty(control_data)){

				var custom_module = eval("(" + control_data.custom_module + ")");
				content = custom_module.module_name;

			}

			html += '<article><p>' + content + '</p></article>';

			break;
		case controlList.Video:

			html += '<video id="my-video" class="video-js vjs-big-play-centered" controls style="width:100%;height:232px;" ';
			html += 'poster="' + STATIC + '/wapCustomTemplate/images/video.png">';
				html += '<p class="vjs-no-js">';
					html += 'To view this video please enable JavaScript, and consider upgrading to a web browser that';
				html += '</p>';
			html += '</video>';

			break;

		case controlList.Audio:

			//音频组件[待做]

			break;

		case controlList.ShowCase:

			if(!empty(control_data)){

				var show_case = eval("(" + control_data.show_case + ")");

				if(show_case.layout == 2) html += getShowCaseDefaultHTML(show_case);

				else html += getShowCaseMultipleColumnsHTML(show_case);

			}else{

				html += getShowCaseDefaultHTML();
			}

			break;
	}
	
	if(!$(self).hasClass("disabled")){

			html += getCommonHTML(control_name);
		html += '</div>';

		if(!empty(control_data)){

			//从数据库中查询出的数据，用完删除
			$(self).removeAttr("data-additional-attr");
			if(control_name == controlList.ImgAd) $(self).attr("data-additional-attr","id='carouselImgAd'");

		}

		custom_main.append(html);
		custom_main.children(".draggable-element:last").attr("data-scroll-top",parseFloat($(".draggable-element:last").height() + $(window).scrollTop()).toFixed(2));

		$('.carousel').carousel({ interval : $Default.carouselInterval });//轮播停留时间

		if(control_name == controlList.Footer){

			var bottom = 76 * $("[data-custom-flag='" + controlList.Footer + "']").length;

			$("[data-custom-flag='Footer']").each(function(i){

				//从第二个开始，每个底部菜单组件靠上排列

				if(i>0){
					var prev = $("[data-custom-flag='Footer']").eq(i-1);
					var height = parseFloat(prev.outerHeight()) + parseFloat(prev.css("bottom").replace("px",""));
					$(this).css("bottom",height+"px");
				}

			});

			$(".custom-main").css("padding-bottom", bottom + "px");
			if(empty(control_data)) custom_main.children(".draggable-element:last").attr("data-footer",JSON.stringify(footer_data));
			
		}else if(control_name == controlList.Video){
			
			//重置视频组件id，不然无法播放
			var id = custom_main.children(".draggable-element:last").children("video").attr("id");
			var new_id = id + custom_main.children("[data-custom-flag='" + controlList.Video + "']").length;
			custom_main.children(".draggable-element:last").children("video").attr("id",new_id);

			if(!empty(control_data)){
				var video_data = eval("(" + control_data.video + ")");
				var myPlayer = videojs(new_id);
				var videoUrl = video_data.url;
				videojs(new_id).ready(function(){
					var myPlayer = this;
					myPlayer.src(videoUrl);
					myPlayer.load(videoUrl);
					myPlayer.play();
					myPlayer.pause();
				});
			}
		}

		draggableElementClick($(".draggable-element:last"),"select");//打开右侧编辑

		//绑定拖拽控件
		$('.draggable-element').arrangeable('',{"border":"2px dashed rgba(255, 0, 0, 0.5)"},function(){

			//拖拽时回调函数
			$(".draggable-element").removeAttr("data-is-show").removeClass("selected");
			$(".pt.pt-left").hide();

		});

		setControlIsDisabled(self,control_name);
	}
}

/**
 * 公共操作HTML（删除）
 * @returns html
 */
function getCommonHTML(control_name){
	
	var html = '<div class="control-actions-wrap">';
			html += '<span class="control-delete" data-control-name="' + control_name + '">删除</span>';
		html += '</div>';
	return html;
}

/**
 * 获取商品列表大图样式
 * 更新时间：2017年8月16日 10:09:00 购买按钮可以自定义设置
 * @param control_data 数据库中返回的数据
 * @returns html
 */
function getGoodsListBigStyleHTML(control_data){
	
	var demo = [{ color : "blue-bg", name  : "第一个商品" },{ color : "pink-bg", name  : "第二个商品" },{ color : "green-bg", name  : "第三个商品" },{ color : "orange-bg", name  : "第N个商品" }];
	var html = '<div class="control-goods-list-big">';
		html += '<ul>';

		for(var i=0;i<demo.length;i++){

			html += '<li>';
				html += '<div class="control-thumbnail ' + demo[i].color + '">' + demo[i].name + '</div>';
				if(!empty(control_data) && control_data.goods_show_goods_name == 0) html += '<h5 class="control-goods-name" style="display:none;">商品名称</h5>';
				else html += '<h5 class="control-goods-name">商品名称</h5>';

				if(!empty(control_data) && control_data.goods_show_goods_price == 0 && control_data.goods_show_buy_button == 0){

					html += '<div class="control-goods-price" style="display:none;">';

				}else if(!empty(control_data) && (control_data.goods_show_goods_price == 0 && control_data.goods_show_buy_button == 1)){

					html += '<div class="control-goods-price position">';

				}else{

					html += '<div class="control-goods-price">';

				}

				if(!empty(control_data) && control_data.goods_show_goods_price == 0) html += '<em style="display:none;">￥' + getRandomPrice() + '</em>';
				else html += '<em>￥' + getRandomPrice() + '</em>';

				var buy_img = STATIC + "/wapCustomTemplate/images/goods_buy_button_style1.png";
				if(!empty(control_data) && !empty(control_data.goods_buy_button_src)){

					buy_img = __IMG(control_data.goods_buy_button_src);

				}

				if(!empty(control_data) && control_data.goods_show_buy_button == 0) html += '<button class="control-goods-buy-style" style="display:none;">';
				else html += '<button class="control-goods-buy-style">';

					html += '<img src="' + buy_img + '"/>';
				html += '</button>';
				html += '</div>';
			html += '</li>';

		}

		html += '</ul>';
	html += '</div>';
	return html;
}

/**
 * 获取商品列表小图样式，示例代码
 * 更新时间：2017年8月16日 10:08:33 购买按钮可以自定义设置
 * @param control_data 数据库中返回的数据
 * @returns html
 */
function getGoodsListSmallStyleHTML(control_data){
	
	var demo = [{ color : "blue-bg", name  : "第一个商品" },{ color : "pink-bg", name  : "第二个商品" },{ color : "green-bg", name  : "第三个商品" },{ color : "orange-bg", name  : "第N个商品" }];
	var html = '<div class="control-goods-list-small">';
		html += '<ul>';

		for(var i=0;i<demo.length;i++){

			html += '<li>';
				html += '<div class="control-thumbnail ' + demo[i].color + '">' + demo[i].name + '</div>';

				if(!empty(control_data) && control_data.goods_show_goods_name == 0) html += '<h5 class="control-goods-name" style="display:none;">商品名称</h5>';
				else html += '<h5 class="control-goods-name">商品名称</h5>';

				if(!empty(control_data) && control_data.goods_show_goods_price == 0 && control_data.goods_show_buy_button == 0){
					html += '<div class="control-goods-price" style="display:none;">';
				}else if(!empty(control_data) && (control_data.goods_show_goods_price == 0 && control_data.goods_show_buy_button == 1)){
					html += '<div class="control-goods-price position">';
				}else{
					html += '<div class="control-goods-price">';
				}

					if(!empty(control_data) && control_data.goods_show_goods_price == 0) html += '<em style="display:none;">￥' + getRandomPrice() + '</em>';
					else html += '<em>￥' + getRandomPrice() + '</em>';

					var buy_img = STATIC + "/wapCustomTemplate/images/goods_buy_button_style1.png";
					if(!empty(control_data) && !empty(control_data.goods_buy_button_src)){
						buy_img = __IMG(control_data.goods_buy_button_src);
					}

					if(!empty(control_data) && control_data.goods_show_buy_button == 0) html += '<button class="control-goods-buy-style" style="display:none;">';
					else html += '<button class="control-goods-buy-style">';

						html += '<img src="' + buy_img + '"/>';
					html += '</button>';
				html += '</div>';
				
			html += '</li>';

		}

		html += '</ul>';
	html += '</div>';
	return html;
}

/**
 * 根据组件的限制条件进行禁用启用
 */
function setControlIsDisabled(curr,control){
	$("[data-custom-flag='" + control + "']").length >= eval("$limit." + control + "MaxCount") ? $(curr).addClass("disabled") : $(curr).removeClass("disabled");
}

/**
 * 获取图片广告显示方式：单图广告
 * @param img_ad 从数据库中返回的数据
 * @returns html
 */
function getImgAdvSingleHTML(img_ad){
	var html = '<div class="carousel-single"><img draggable="false" src="' + STATIC + '/wapCustomTemplate/images/control_img_ad_single_default.png"></div>';
	if(!empty(img_ad)) html = '<div class="carousel-single"><img draggable="false" src="' + __IMG(img_ad[0].src) + '"></div>';
	return html;
}

/**
 * 获取图片广告显示方式：多图广告
 * @param img_ad 数据库中返回的数据
 * @returns html
 */
function getImgAdvCarouselHTML(img_ad){

	var html = '<div class="carousel-inner">';
	if(!empty(img_ad)){

		for(var i=0;i<img_ad.length;i++){

			if(i==0) html += '<div class="active item">';
			else html += '<div class="item">';

			if(!empty(img_ad[i].src)) html += '<img draggable="false" src="' + __IMG(img_ad[i].src) + '">';
			else html += '<img draggable="false" src="' + STATIC + '/wapCustomTemplate/images/control_img_ad_carousel_default.png">';
			html += '</div>';
		}
	}else{
		
		for(var i=0;i<4;i++){
			
			if(i==0) html += '<div class="active item">';
			else html += '<div class="item">';
					html += '<img draggable="false" src="' + STATIC + '/wapCustomTemplate/images/control_img_ad_carousel_default.png">';
			html += '</div>';
		}
	}
	html += '</div>';
	html += '<a class="carousel-control left" href="#carouselImgAd" data-slide="prev">&lsaquo;</a>';
	html += '<a class="carousel-control right" href="#carouselImgAd" data-slide="next">&rsaquo;</a>';

	return html;
}

function updateNavHyBridHTML(){

	var html = '';
	var line_columns = $("input[name='line_columns']:checked").val();
	var class_name = 'one-line-four-columns';
	if(line_columns== 5) class_name = "one-line-five-columns";
	else if(line_columns== 8) class_name = "two-line-four-columns";
	else if(line_columns== 10) class_name = "two-line-five-columns";
	else if(line_columns== 99) class_name = "custom-columns";

	$(".control-edit.nav-hybrid").each(function(){

		html += '<li class="' + class_name + '" >';
			if(!empty($(this).find(".add-img input[type='hidden']").val())) html += '<div style="background:url(' + __IMG($(this).find(".add-img input[type='hidden']").val()) + ') no-repeat 50% 50% / 100%;"></div>';
			if(!empty($(this).find(".js-nav-hybrid-text").val())) html += '<span data-editable="1" style="color:' + $(".js-text-color").val() + ';">' + $(this).find(".js-nav-hybrid-text").val() + '</span>';
		html += '</li>';

	});
	getCustom().find("ul").html(html);
}

/**
 * 更新底部菜单组件代码
 * @param index 下标
 * @param type 类型：[文本,图片]
 * @param value 值
 */
function updateFooterHTML(index,type,value){
	var html = "";
	switch(type){
	case "label":

		if(!getCustom().find("ul li:eq(" + index + ") label").length){

			//没有文字的情况下添加<label>
			var li = getCustom().find("ul li:eq(" + index + ")");
			html = '<label data-editable="1">' + value + '</label>';
			li.append(html);

		}else{

			//有文字的情况下修改
			var li = getCustom().find("ul li:eq(" + index + ")");
			li.find("label").text(value);

		}

		break;

	case "img":

		if(!getCustom().find("ul li:eq(" + index + ") img").length){

			//没有图片的情况下添加<label>
			var li = getCustom().find("ul li:eq(" + index + ")");
			if(!empty(value)) html = '<img draggable="false" src="' + value + '">';
			else html = '<img draggable="false" style="display:none;">';

			if(li.find("label").length) li.prepend(html);
			else li.append(html);

		}else{

			//有图片的情况下修改
			var li = getCustom().find("ul li:eq(" + index + ")");
			if(!empty(value)) li.find("img").attr("src",value);
			else li.find("img").hide().removeAttr("src");

		}

		break;
	}
}

/**
 * 橱窗默认风格代码
 * 1、默认风格，一大（左）两小（右）
 */
function getShowCaseDefaultHTML(show_case){

	var layout_array = [
		{ layout : "big",   bgcolor : "blue-bg" },
		{ layout : "small", bgcolor : "pink-bg" },
		{ layout : "small", bgcolor : "green-bg" }
	];
	var html = '<div class="show-case-default">';
		html += '<ul>';
		for(var i=0;i<layout_array.length;i++){
			var curr = layout_array[i];

			if(!empty(show_case)){

				var item = show_case.itemList[i];
				var background = "";
				var clear = "";

				if(i!=0 && show_case.clearance == 0) clear = "clear";

				if(!empty(item.src)) background = 'style="background: url(' + __IMG(item.src) + ') 50% 50% / 100% no-repeat;"';

				html += '<li class="' + curr.layout + ' ' + curr.bgcolor + ' ' + clear + '" ' + background + '>';

					if(!empty(background)) html += '<div style="visibility:hidden;">橱窗</div>';
					else html += '<div>橱窗</div>';

					if(item.show_text == 0) html += '<p style="display:none;">' + item.text + '</p>';
					else html += '<p>' + item.text + '</p>';
				html += '</li>';

			}else{

				html += '<li class="' + curr.layout + ' ' + curr.bgcolor + '">';
					html += '<div>橱窗</div>';
					html += '<p>橱窗文字</p>';
				html += '</li>';
			}
		}

		html += '</ul>';
	html += '</div>';

	return html;
}

/**
 * 橱窗（3列）风格代码
 */
function getShowCaseMultipleColumnsHTML(show_case){
	
	var count = 3;
	var color = ["blue-bg","pink-bg","green-bg"];
	var html = '<div class="show-case-multiple-columns">';
		html += '<ul>';
		for(var i=0;i<count;i++){
			if(!empty(show_case)){

				var item = show_case.itemList[i];
				var background = "";
				if(!empty(item.src)) background = 'style="background: url(' + __IMG(item.src) + ') 50% 50% / 100% no-repeat;"';

				if(show_case.clearance == 0) html += '<li class="clear" ' + background + '>';
				else html += '<li ' + background + '>';

				if(!empty(item.src)) html += '<div class="' + color[i] + '" style="visibility:hidden;">多列橱窗</div>';
				else html += '<div class="blue-bg">多列橱窗</div>';

				if(item.show_text == 0) html += '<p style="display:none;">' + item.text + '</p>';
				else html += '<p>' + item.text + '</p>';

			}else{

				html += '<li>';
					html += '<div class="' + color[i] + '">多列橱窗</div>';
					html += '<p>橱窗文字</p>';
				html += '</li>';

			}
		}
		html += '</ul>';
	html += '</div>';
	return html;
}

function getTextSizeHTML(){
	
	var sizeArr = $Default.textSize;
	var value = $Default.textSize[2];//默认字体大小14
	//当前组件设置过文字大小，则进行赋值

	switch(getCustom().attr("data-custom-flag")){
		case controlList.Notice:

			var notice = getCustom().attr("data-notice");
			if(!empty(notice)){
				notice = eval("(" + notice + ")");
				value = notice.font_size;
			}
			break;

		case controlList.TextNavigation:

			var text_navigation = getCustom().attr("data-text-navigation");
			if(!empty(text_navigation)){
				text_navigation = eval("(" + text_navigation + ")");
				value = text_navigation.font_size;
			}

			break;

		case controlList.GoodsSearch:

			var goods_search = getCustom().attr("data-goods-search");
			if(!empty(goods_search)){
				goods_search = eval("(" + goods_search + ")");
				value = goods_search.font_size;
			}

			break;

		default:

			if(getCustom().attr("data-font-size")) value = getCustom().attr("data-font-size");

			break;

	}

	var html = '<div class="control-edit font-size">';
		html += '<label>文字大小：</label>';
		html += '<select class="select-common js-select-font-size" data-jsclass="js-select-font-size" data-default-value="' + $Default.textSize[2] + '">';
		for(i in sizeArr){

			if(sizeArr[i] == value){

				html += '<option value="' + sizeArr[i] + '" selected="selected">' + sizeArr[i] + 'px</option>';
				continue;

			}

			html += '<option value="' + sizeArr[i] + '">' + sizeArr[i] + 'px</option>';

		}

		html += '</select>';
		html += '<i class="fa fa-refresh fr"></i>';
	html += '</div>';

	return html;
}

/**
 * 获取输入框代码
 * 第一个参数是type：[color,input]输入框类型:是否必填标识，最后一个参数是要赋给前边的值
 */
function getInputHTML(){
	
	var html = '';
	var type = arguments[0];//输入框类型:是否必填标识

	//从第二个开始循环
	for(var i =1;i<arguments.length;i++){
		var obj = arguments[i];

		if(typeof obj == "string") break;

		var value = obj.value;//值传递，不修改引用对象

		if((i+1) == (arguments.length-1) && typeof arguments[arguments.length-1] == "string"){

			value = arguments[i+1];

		}else{

			var data = getCustom().attr(obj.input_class.replace("js-","data-"));
			//存在就进行赋值操作，条件是：不能与默认值相同
			if(!empty(data) && data != obj.default_value) value = data;

		}

		//当前组件如果有颜色，则进行赋值
		var type_arr = type.split(":");
		var required = "";

		if(!empty(type_arr[1])) required = "<span>*</span>";

		html += '<div class="control-edit ' + obj.class_name + '">';
			html += '<label>' + required + obj.name + '：</label>';
			html += '<input type="' + type_arr[0] + '" class="input-common harf ' + obj.input_class + '" value="' + value + '" data-jsclass="' + obj.input_class + '" data-default-value="' + obj.default_value + '" placeholder="' + obj.placeholder + '" >';
			if(type_arr[0] == "color") html += '<i class="fa fa-refresh fr"></i>';

		html += '</div>';

	}
	
	return html;
}

function getGoodsListHTML(){
	
	var goods_list = getCustom().attr("data-goods-list");//存在则赋值

	if(!empty(goods_list)) goods_list = eval("(" + goods_list + ")");

	var html = '<div class="control-edit goods-source">';
		html += '<label><span>*</span>商品来源：</label>';
		html += '<div>';
			html += '<select class="select-common js-goods-source">';

			if(!empty(goods_category_list) && goods_category_list.length>0){

				for(var i=0;i<goods_category_list.length;i++){

					var goods_category = goods_category_list[i];

					if(!empty(goods_list) && goods_list.goods_source == goods_category.category_id){

						html += '<option value="' + goods_category.category_id + '" selected="selected">' + goods_category.category_name + "</option>";
						continue;

					}

					html += '<option value="' + goods_category.category_id + '">' + goods_category.category_name + "</option>";

				}
			}else{

				html += '<option value="0">没有发现商品来源</option>';

			}

			html += '</select>';
			html += '<p class="description">选择商品来源后，左侧实时预览暂不支持显示其包含的商品数据</p>';
		html += '</div>';
	html += '</div>';
	
	html += '<div class="control-edit goods-count">';
		html += '<label>显示个数：</label>';
		html += '<div>';
		for(var i=0;i<$Default.goodsLimitCount.length;i++){

			var curr = $Default.goodsLimitCount[i];
			var checked = '';

			if(!empty(goods_list) && goods_list.goods_limit_count == curr) checked = 'checked="checked"';
			else if(i == 0) checked = 'checked="checked"';

			html += '<input type="radio" ' + checked + ' value="' + curr + '" id="show_count' + curr + '" name="showcount">&nbsp;';
			html += '<label for="show_count' + curr + '" class="label-for">' + curr + '</label>';

		}
		html += '</div>';

	html += '</div>';
	
	html += '<div class="control-edit list-style">';
		html += '<label>列表样式：</label>';

		if(!empty(goods_list) && goods_list.goods_list_type == $Default.goodsListType[1]){
			
			html += '<input type="radio" value="' + $Default.goodsListType[0] + '" id="list_type1" name="list_type">&nbsp;';
			html += '<label for="list_type1" class="label-for">大图</label>';
			html += '<input type="radio" value="' + $Default.goodsListType[1] + '" id="list_type2" name="list_type" checked="checked">&nbsp;';
			html += '<label for="list_type2" class="label-for">小图</label>';

		}else{

			html += '<input type="radio" value="' + $Default.goodsListType[0] + '" id="list_type1" name="list_type" checked="checked">&nbsp;';
			html += '<label for="list_type1" class="label-for">大图</label>';
			html += '<input type="radio" value="' + $Default.goodsListType[1] + '" id="list_type2" name="list_type">&nbsp;';
			html += '<label for="list_type2" class="label-for">小图</label>';

		}

	html += '</div>';
	
	html += '<div class="control-edit-attribute">';

		html += '<div class="js-show-buy-button">';
			var show_buy_button_checked = "checked='checked'";
			if(!empty(goods_list) && parseInt(goods_list.show_buy_button) == 0) show_buy_button_checked = "";

			html += '<input type="checkbox" ' + show_buy_button_checked + ' value="0" id="show_buy_button">&nbsp;';
			html += '<label for="show_buy_button" class="label-for">显示购买按钮</label>';
		html += '</div>';

		html += '<div class="js-show-buy-button-style show-buy-button-style">';

			var buy_button_style1_checked = "";
			var buy_button_style2_checked = "";
			var buy_button_style3_checked = "";
			var buy_button_style4_checked = "";
			var buy_button_src = "";//购买按钮图片路径

			if(!empty(goods_list) && goods_list.goods_buy_button_style == 1){

				buy_button_style1_checked = "checked='checked'";

			}else if(!empty(goods_list) && goods_list.goods_buy_button_style == 2){

				buy_button_style2_checked = "checked='checked'";

			}else if(!empty(goods_list) && goods_list.goods_buy_button_style == 3){

				buy_button_style3_checked = "checked='checked'";

			}else if(!empty(goods_list) && goods_list.goods_buy_button_style == 4) {

				buy_button_style4_checked = "checked='checked'";
				buy_button_src = goods_list.goods_buy_button_src;

			}else {

				buy_button_style1_checked = "checked='checked'";

			}

			html += '<input type="radio" ' + buy_button_style1_checked + ' value="public/static/wapCustomTemplate/images/goods_buy_button_style1.png" id="buy_button_style1" name="buy_button_style" data-buy-button-style="1">&nbsp;';
			html += '<label for="buy_button_style1" class="label-for">样式1</label>';
			html += '<input type="radio" ' + buy_button_style2_checked + ' value="public/static/wapCustomTemplate/images/goods_buy_button_style2.png" id="buy_button_style2" name="buy_button_style" data-buy-button-style="2">&nbsp;';
			html += '<label for="buy_button_style2" class="label-for">样式2</label>';
			html += '<input type="radio" ' + buy_button_style3_checked + ' value="public/static/wapCustomTemplate/images/goods_buy_button_style3.png" id="buy_button_style3" name="buy_button_style" data-buy-button-style="3">&nbsp;';
			html += '<label for="buy_button_style3" class="label-for">样式3</label>';
			html += '<input type="radio" ' + buy_button_style4_checked + ' value="' + buy_button_src + '" id="show_buy_button_style4" name="buy_button_style" data-buy-button-style="4">&nbsp;';
			html += '<label for="show_buy_button_style4" class="label-for">自定义样式</label>';

		html += '</div>';

		if(!empty(goods_list) && goods_list.goods_buy_button_style == 4) html += '<div class="control-edit custom-buy-style" style="display:block;">';
		else html += '<div class="control-edit custom-buy-style">';

			html += '<div class="add-img">';
				if(!empty(goods_list) && goods_list.goods_buy_button_style == 4 && !empty(buy_button_src)){

					html += '<div class="img-block" style="display:block;"><img id="img_custom_buy_style" style="max-height:100%;" src="' + __IMG(buy_button_src) + '"></div>';
					html += '<span>';
					html += '<input class="input-file" name="file_upload" id="upload_img_custom_buy_style" type="file" onchange="imgUpload(this);">';
					html += '<input type="hidden" id="custom_buy_style" value="' + buy_button_src + '">';
					html += '</span>';
					html += '<p id="text_custom_buy_style" style="display:none;">添加图片<br><span>建议尺寸40*30</span></p>';
					html += '<i class="fa fa-close js-del-img" style="display:block;"></i>';

				}else{

					html += '<div class="img-block"><img id="img_custom_buy_style"></div>';
					html += '<span>';
					html += '<input class="input-file" name="file_upload" id="upload_img_custom_buy_style" type="file" onchange="imgUpload(this);">';
					html += '<input type="hidden" id="custom_buy_style">';
					html += '</span>';
					html += '<p id="text_custom_buy_style">添加图片<br><span>建议尺寸40*30</span></p>';
					html += '<i class="fa fa-close js-del-img"></i>';

				}
			html +='</div>';
		html += '</div>';

		html += '<div class="js-show-goods-name">';
			var show_goods_name_checked = "checked='checked'";

			if(!empty(goods_list) && parseInt(goods_list.goods_show_goods_name) == 0) show_goods_name_checked = "";
			
			html += '<input type="checkbox" ' + show_goods_name_checked + ' value="0" id="show_goods_name">&nbsp;';
			html += '<label for="show_goods_name" class="label-for">显示商品名称</label>';
		html += '</div>';

		html += '<div class="js-show-goods-price">';
		var show_goods_price_checked = "checked='checked'";

		if(!empty(goods_list) && parseInt(goods_list.goods_show_goods_price) == 0) show_goods_price_checked = "";

		html += '<input type="checkbox" ' + show_goods_price_checked + ' value="0" id="show_goods_price">&nbsp;';
		html += '<label for="show_goods_price" class="label-for">显示价格</label>';
		html += '</div>';

	html += '</div>';

	return html;
}

function getTextAlignHTML(){

	var text_align = 1;

	switch(getCustom().attr("data-custom-flag")){

		case controlList.Notice:

			var notice = getCustom().attr("data-notice");
			if(!empty(notice)){
				notice = eval("(" + notice + ")");
				text_align = notice.text_align;
			}

			break;

		case controlList.TextNavigation:

			var text_navigation = getCustom().attr("data-text-navigation");
			if(!empty(text_navigation)){
				text_navigation = eval("(" + text_navigation + ")");
				text_align = text_navigation.text_align;
			}

			break;

		case controlList.Title:

			var title = getCustom().attr("data-title");
			if(!empty(title)){
				title = eval("(" + title + ")");
				text_align = title.text_align;
			}

			break;

		default:

			text_align = getCustom().attr("data-text-align");//存在则进行赋值

			break;

	}

	var left = "",center = "",right = "";
	var html = '<div class="control-edit text-align">';
		html += '<label>显示：</label>';

		if(text_align && text_align == $Default.textAlign[0]) left = 'checked="checked"';
		else if(text_align && text_align == $Default.textAlign[1]) center = 'checked="checked"';
		else if(text_align && text_align == $Default.textAlign[2]) right = 'checked="checked"';
		else left = 'checked="checked"';

		html += '<input type="radio" value="' + $Default.textAlign[0] + '" ' + left + ' id="text_align_left" name="text_align">&nbsp;';
		html += '<label for="text_align_left" class="label-for">居左</label>';
		html += '<input type="radio" value="' + $Default.textAlign[1] + '" ' + center + ' id="text_align_center" name="text_align">&nbsp;';
		html += '<label for="text_align_center" class="label-for">居中</label>';
		html += '<input type="radio" value="' + $Default.textAlign[2] + '" ' + right + ' id="text_align_right" name="text_align">&nbsp;';
		html += '<label for="text_align_right" class="label-for">居右</label>';
	html += '</div>';

	return html;
}

function getLinkHTML(href){
	
	var href_name = "";
	
	if(!empty(template_list) && template_list.length>0){

		for(var i=0;i<template_list.length;i++){

			var curr = template_list[i];
			link_arr[__URL(APPMAIN + '/CustomTemplate/customTemplateIndex?id=' +curr.id)] = curr.template_name;

		}

	}
	
	if(href == $Default.href) href = "";
	
	if(!empty(href)) href_name = link_arr[href];
	
	if(!empty(getCustom().attr("data-href")) && getCustom().attr("data-href") != $Default.href && empty(href)) href = link_arr[getCustom().attr("data-href")];
	
	if(empty(href_name)) href_name = href;

	var html = '<div class="control-edit link">';
		html += '<label>链接地址：</label>';

		if(href) html += '<span class="selected" style="display:inline-block;" title="' + href + '" data-href="' + href + '">' + href_name + '</span>';
		else html += '<span class="selected"></span>';

		html += '<div class="custom-input">';
			html += '<div class="float-link-custom">';
			html += '<div class="arrow"></div>';
				html += '<input type="text" class="input-common" placeholder="链接地址：http://example.com" />';
				html += '<button class="btn-common">确定</button>';
				html += '<button class="btn-common-cancle">取消</button>';
			html += '</div>';
		html += '</div>';
		html += '<div class="dropdown">';
			html += '<a class="dropdown-toggle" id="dLabel" role="button" data-toggle="dropdown" data-target="#" href="javascript:;">';
				html += '设置链接地址';
				html += '<b class="caret"></b>';
			html += '</a>';

			html += '<ul class="dropdown-menu js-link" role="menu" aria-labelledby="dLabel">';
				for(link in link_arr){

					html += '<li data-href="' + link + '">' + link_arr[link] + '</li>';

				}
				html += '<li class="js-custom-link">自定义链接</li>';
			html += '</ul>';
		html += '</div>';
	html += '</div>';
	
	return html;
}

/**
 * 设置自定义链接编辑框的位置
 */
function setLinkCustomMarginLeft(){
	var margin_left = ($(".control-edit.link .selected").outerWidth() + $(".control-edit.link .dropdown").outerWidth()+100)/2;
	$(".float-link-custom").css("margin-left",-margin_left);
}

function getGoodsClassifyHTML(){
	
	var goods_classify = getCustom().attr("data-goods-classify");//存在则赋值
	if(!empty(goods_classify)){

		goods_classify = eval(goods_classify);

	}

	var html = '<div class="control-edit-attribute" style="padding:10px;">';
		html += '<div class="js-show-buy-button" style="margin-left:0;">';
			html += '<input type="checkbox" checked="checked" value="0" id="show_buy_button">&nbsp;';
			html += '<label for="show_buy_button" class="label-for">显示购买按钮</label>';
		html += '</div>';

		html += '<div class="js-show-buy-button-style show-buy-button-style" style="margin-left:10px;">';

			var buy_button_style1_checked = "";
			var buy_button_style2_checked = "";
			var buy_button_style3_checked = "";
			var buy_button_style4_checked = "";
			var buy_button_src = "";//购买按钮图片路径

			if(!empty(goods_classify) && goods_classify[0].goods_buy_button_style == 1){

				buy_button_style1_checked = "checked='checked'";

			}else if(!empty(goods_classify) && goods_classify[0].goods_buy_button_style == 2){

				buy_button_style2_checked = "checked='checked'";

			}else if(!empty(goods_classify) && goods_classify[0].goods_buy_button_style == 3){

				buy_button_style3_checked = "checked='checked'";

			}else if(!empty(goods_classify) && goods_classify[0].goods_buy_button_style == 4) {

				buy_button_style4_checked = "checked='checked'";
				buy_button_src = goods_classify[0].goods_buy_button_src;

			}else {

				buy_button_style1_checked = "checked='checked'";

			}

			html += '<input type="radio" ' + buy_button_style1_checked + ' value="public/static/wapCustomTemplate/images/goods_buy_button_style1.png" id="buy_button_style1" name="buy_button_style" data-buy-button-style="1">&nbsp;';
			html += '<label for="buy_button_style1" class="label-for">样式1</label>';
			html += '<input type="radio" ' + buy_button_style2_checked + ' value="public/static/wapCustomTemplate/images/goods_buy_button_style2.png" id="buy_button_style2" name="buy_button_style" data-buy-button-style="2">&nbsp;';
			html += '<label for="buy_button_style2" class="label-for">样式2</label>';
			html += '<input type="radio" ' + buy_button_style3_checked + ' value="public/static/wapCustomTemplate/images/goods_buy_button_style3.png" id="buy_button_style3" name="buy_button_style" data-buy-button-style="3">&nbsp;';
			html += '<label for="buy_button_style3" class="label-for">样式3</label>';
			html += '<input type="radio" ' + buy_button_style4_checked + ' value="' + buy_button_src + '" id="show_buy_button_style4" name="buy_button_style" data-buy-button-style="4">&nbsp;';
			html += '<label for="show_buy_button_style4" class="label-for">自定义样式</label>';

		html += '</div>';

		if(!empty(buy_button_src)) html += '<div class="control-edit custom-buy-style" style="margin-left:10px;display:block;">';
		else html += '<div class="control-edit custom-buy-style" style="margin-left:10px;">';

			html += '<div class="add-img">';

				if(!empty(buy_button_src)) html += '<div class="img-block" style="display: block;"><img id="img_custom_buy_style" src="' + __IMG(buy_button_src) + '" style="max-height: 100%;"></div>';
				else html += '<div class="img-block"><img id="img_custom_buy_style"></div>';

				html += '<span>';
					html += '<input class="input-file" name="file_upload" id="upload_img_custom_buy_style" type="file" onchange="imgUpload(this);">';
					html += '<input type="hidden" id="custom_buy_style" value="' + buy_button_src + '">';
				html += '</span>';
				if(!empty(buy_button_src)){

					html += '<p id="text_custom_buy_style" style="display:none;">添加图片<br><span>建议尺寸40*30</span></p>';
					html += '<i class="fa fa-close js-del-img" style="display:block;"></i>';

				}else{

					html += '<p id="text_custom_buy_style">添加图片<br><span>建议尺寸40*30</span></p>';
					html += '<i class="fa fa-close js-del-img"></i>';

				}

			html += '</div>';
		html += '</div>';

	html += '</div>';
	html += '<div class="control-edit goods-classify">';
		html += '<div class="goods-classify-list">';
			html += '<ul>';

				if(!empty(goods_classify)){

					for(var i=0;i<goods_classify.length;i++){

						if(!empty(goods_classify[i].name)){

							html += '<li data-classify-id="' + goods_classify[i].id + '" data-classify-name="' + goods_classify[i].name + '" data-show-count="' + goods_classify[i].show_count + '">';
								html += '<span>商品来源：<em>' + goods_classify[i].name + '</em></span>';
								html += '<div>';
									html += '<span>显示数量</span>';
									html += '<div class="dropdown">';
										html += '<a class="dropdown-toggle" data-toggle="dropdown" href="#"><span>' + goods_classify[i].show_count +'</span><b class="caret"></b></a>';
										html += '<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">';
											html += '<li>5</li>';
											html += '<li>10</li>';
											html += '<li>15</li>';
											html += '<li>30</li>';
											html += '<li>50</li>';
											html += '<li>100</li>';
										html += '</ul>';
									html += '</div>';
								html += '</div>';
							html += '</li>';
						}
					}
				}
			html += '</ul>';
		html += '</div>';
		if(goods_category_list != null && goods_category_list.length>0){

			html += '<div class="add-goods-classify">选择商品分类';
				html += "<ul>";
					for(var i=0;i<goods_category_list.length;i++){

						var goods_category = goods_category_list[i];
						var checkbox = '';

						if(!empty(goods_classify)){

							for(var j=0;j<goods_classify.length;j++){

								if(parseInt(goods_classify[j].id) == parseInt(goods_category.category_id)){

									checkbox = 'checked="checked"';
									break;

								}

							}

						}

						html += '<li><input value="' + goods_category.category_id + '" ' + checkbox + ' type="checkbox" id="goods_classify'+ goods_category.category_id +'" name="goods_classify"><label class="label-for" for="goods_classify'+ goods_category.category_id +'">' + goods_category.category_name + "</label></li>";

					}

				html += "</ul>";
		}else{

			html += '<div class="add-goods-classify" style="color:#999999;">暂无商品分类';

		}
		html += '</div>';
		html += '<p class="description">选择商品来源后，左侧实时预览暂不支持显示其包含的商品数据</p>';
	html += '</div>';

	return html;
}

function getImgAdHTML(){
	
	var single = "";
	var multiple = "";
	var count = 4;//图片个数
	var adv_show_type = $Default.advShowType[0];//默认是单图广告
	var img_ad = getCustom().attr("data-img-ad");//存在则赋值
	if(!empty(img_ad)){

		img_ad = eval(img_ad);
		adv_show_type = img_ad[0].adv_show_type;

	}
	
	if(adv_show_type == $Default.advShowType[0]) single = 'checked="checked"';
	else if(adv_show_type == $Default.advShowType[1]) multiple = 'checked="checked"';
	else single = 'checked="checked"';
	
	var html = '';

		html += '<div class="video-upload-pre"><pre>实际效果会根据图片尺寸进行等比例缩放！</pre></div>';

		html += '<div class="control-edit img-ad-align">';
		html += '<label>显示方式：</label>';
		html += '<input type="radio" value="' + $Default.advShowType[0] + '" id="show_img_ad_type_single" name="show_img_ad_type" ' + single + '>&nbsp;';
		html += '<label for="show_img_ad_type_single" class="label-for">单图广告</label>';
		html += '<input type="radio" value="' + $Default.advShowType[1] + '" id="show_img_ad_type_multiple" name="show_img_ad_type" ' + multiple + '>&nbsp;';
		html += '<label for="show_img_ad_type_multiple" class="label-for">多图轮播广告</label>';
	html += '</div>';
	
	for(var i=0;i<count;i++){

		if(i!=0 && adv_show_type == $Default.advShowType[0]){

			html += '<div class="control-edit img-ad" style="display:none;">';

		}else{

			html += '<div class="control-edit img-ad">';

		}
			if(!empty(img_ad) && !empty(img_ad[i]) && !empty(img_ad[i].src)){

				html += '<div class="add-img">';
					html += '<div class="img-block" style="display:block;"><img id="img_imgad' + i + '" style="max-height:100%;" src="' + __IMG(img_ad[i].src) + '"></div>';
					html += '<span>';
						html += '<input class="input-file" name="file_upload" id="upload_imgad' + i + '" type="file" onchange="imgUpload(this);">';
						html += '<input type="hidden" id="imgad' + i + '" value="' + img_ad[i].src + '">';
					html += '</span>';
					html += '<p id="text_imgad' + i + '" style="display:none;">添加图片<br><span>建议尺寸320*80</span></p>';
					html += '<i class="fa fa-close js-del-img" style="display:block;"></i>';

				}else{

					html += '<div class="add-img">';
					html += '<div class="img-block"><img id="img_imgad' + i + '"></div>';
					html += '<span>';
					html += '<input class="input-file" name="file_upload" id="upload_imgad' + i + '" type="file" onchange="imgUpload(this);">';
					html += '<input type="hidden" id="imgad' + i + '">';
					html += '</span>';
					html += '<p id="text_imgad' + i + '">添加图片<br><span>建议尺寸320*80</span></p>';
					html += '<i class="fa fa-close js-del-img"></i>';

				}

			html +='</div>';
			html += '<div class="info">';

				if(!empty(img_ad) && !empty(img_ad[i]) && !empty(img_ad[i].href)) html += getLinkHTML(img_ad[i].href);
				else html += getLinkHTML();

			html += '</div>';
		html += '</div>';
	}
	return html;
}

/**
 * 图文导航项代码
 */
function getNavHyBridItemHTML(item){

	var html = '';
	html += '<div class="control-edit nav-hybrid">';
	
		html += '<div class="info">';

		html += getInputHTML("text",$cValue.NavHyBridText,item.text);
		
		 html += getLinkHTML(item.href);

		html += '</div>';

		if(!empty(item.src)){

			html += '<div class="add-img">';
				html += '<div class="img-block" style="display:block;"><img id="img_imgad' + item.index + '" style="max-height:100%;" src="' + __IMG(item.src) + '"></div>';
				html += '<span>';
					html += '<input class="input-file" name="file_upload" id="upload_imgad' + item.index + '" type="file" onchange="imgUpload(this);" data-index="' + item.index + '">';
					html += '<input type="hidden" id="imgad' + item.index + '" value="' + item.src + '">';
				html += '</span>';
				html += '<p id="text_nav_hybrid_img' + item.index + '" style="display:none;">+</p>';
				html += '<i class="fa fa-close js-del-img" style="display:block;"></i>';

		}else{

			html += '<div class="add-img">';
				html += '<div class="img-block"><img id="img_nav_hybrid_img' + item.index + '"></div>';
				html += '<span>';
					html += '<input class="input-file" name="file_upload" id="upload_nav_hybrid_img' + item.index + '" type="file" onchange="imgUpload(this);" data-index="' + item.index + '">';
					html += '<input type="hidden" id="nav_hybrid_img' + item.index + '">';
				html += '</span>';
				html += '<p id="text_nav_hybrid_img' + item.index + '">+</p>';
				html += '<i class="fa fa-close js-del-img"></i>';

		}

		html +='</div>';
	html += '</div>';
	return html;
}

function getNavHyBridHTML(){

	var html = '';
	var nav_hybrid = getCustom().attr("data-nav-hybrid");//存在则进行赋值
	var items = null;
	var text_color = $cValue.NavHyBridColor.value;
	var type = 4;//默认1行4列
	var one_line_four_columns = "checked='checked'";
	var one_line_five_columns = "";
	var two_line_four_columns = "";
	var two_line_five_columns = "";
	var custom_columns = "";
	var bg_color = $cValue.NavHyBridBgColor.value;

	if(!empty(nav_hybrid)){
		nav_hybrid = eval("(" + nav_hybrid + ")");
		items = nav_hybrid.items;
		text_color = nav_hybrid.text_color;
		type = nav_hybrid.type;
		bg_color = nav_hybrid.bg_color;
	}
	
	if(type == 5){

		one_line_four_columns = "";
		one_line_five_columns = "checked='checked'";
		two_line_four_columns = "";
		two_line_five_columns = "";
		custom_columns = "";

	}else if(type == 8){

		one_line_four_columns = "";
		one_line_five_columns = "";
		two_line_four_columns = "checked='checked'";
		two_line_five_columns = "";
		custom_columns = "";

	}else if(type == 10){

		one_line_four_columns = "";
		one_line_five_columns = "";
		two_line_four_columns = "";
		two_line_five_columns = "checked='checked'";
		custom_columns = "";

	}else if(type == 99){
		
		one_line_four_columns = "";
		one_line_five_columns = "";
		two_line_four_columns = "";
		two_line_five_columns = "";
		custom_columns = "checked='checked'";
	}

	html += getInputHTML("color",$cValue.NavHyBridColor,text_color);
	
	html += getInputHTML("color",$cValue.NavHyBridBgColor,bg_color);

	html += '<div class="control-edit list-style">';

		html += '<label>样式：</label>';
		html += '<input type="radio" value="4" id="one_line_four_columns" name="line_columns" '+ one_line_four_columns + '>&nbsp;';
		html += '<label for="one_line_four_columns" class="label-for">1行4列</label>';
		
		html += '<input type="radio" value="5" id="one_line_five_columns" name="line_columns" ' + one_line_five_columns + '>&nbsp;';
		html += '<label for="one_line_five_columns" class="label-for">1行5列</label>';
		
		html += '<input type="radio" value="8" id="two_line_four_columns" name="line_columns" ' + two_line_four_columns + '>&nbsp;';
		html += '<label for="two_line_four_columns" class="label-for">2行4列</label>';
		
		html += '<input type="radio" value="10" id="two_line_five_columns" name="line_columns" ' + two_line_five_columns + '>&nbsp;';
		html += '<label for="two_line_five_columns" class="label-for">2行5列</label>';

		// html += '<input type="radio" value="99" id="custom_columns" name="line_columns" ' + custom_columns + '>&nbsp;';
		// html += '<label for="custom_columns" class="label-for">自定义</label>';

	html += '</div>';

	html += '<div class="nav-hybrid-list">';
		for(var i=0;i<type;i++){
			var curr = {
				text : "",
				src : "",
				href : $Default.href
			};

			if(!empty(items)) curr = items[i];

			curr.index = (i+1);
			html += getNavHyBridItemHTML(curr);
		}
	html += '</div>';
	
	return html;
}

function getFooterHTML(){
	
	var footer = getCustom().attr("data-footer");//存在则赋值

	if(!empty(footer)) footer = eval(footer);
	
	var html = '';
	if(!empty(footer) && !empty(footer[0]) && !empty(footer[0].color)) html += getInputHTML("color",$cValue.footerTextColor,footer[0].color);
	else html += getInputHTML("color",$cValue.footerTextColor);
	
	if(!empty(footer) && !empty(footer[0]) && !empty(footer[0].color_hover)) html += getInputHTML("color",$cValue.textColorHover,footer[0].color_hover);
	else html += getInputHTML("color",$cValue.textColorHover);
	
	for(var i=0;i<$Default.footerItemCount;i++){

		html += '<div class="control-edit footer">';
			html += '<div class="imglist">';
				if(!empty(footer) && !empty(footer[i]) && !empty(footer[i].img_src)){

					html += '<div class="add-img js-img-footer">';
						html += '<div class="img-block" style="display:block;"><img id="img_footer' + i + '" style="max-height:100%;" src="' + __IMG(footer[i].img_src) + '"></div>';
						html += '<span>';
							html += '<input class="input-file" name="file_upload" id="upload_footer_img' + i + '" type="file" onchange="imgUpload(this);" data-index="' + i + '">';
							html += '<input type="hidden" id="footer' + i + '" value="' + footer[i].img_src + '">';
						html += '</span>';
						html += '<p id="text_footer' + i + '" style="display:none;">未选中的图片<br><span>建议尺寸25*25</span></p>';
						html += '<i class="fa fa-close js-del-img" style="display:block;"></i>';
					html +='</div>';

				}else{

					html += '<div class="add-img js-img-footer">';
						html += '<div class="img-block"><img id="img_footer' + i + '"></div>';
						html += '<span>';
							html += '<input class="input-file" name="file_upload" id="upload_footer_img' + i + '" type="file" onchange="imgUpload(this);" data-index="' + i + '">';
							html += '<input type="hidden" id="footer' + i + '">';
						html += '</span>';
						html += '<p id="text_footer' + i + '">未选中的图片<br><span>建议尺寸25*25</span></p>';
						html += '<i class="fa fa-close js-del-img"></i>';
					html +='</div>';

				}

				if(!empty(footer) && !empty(footer[i]) && !empty(footer[i].img_src_hover)){

					html += '<div class="add-img js-img-footer-hover">';
						html += '<div class="img-block" style="display:block;"><img id="img_footer_hover' + i + '" style="max-height:100%;" src="' + __IMG(footer[i].img_src_hover) + '"></div>';
						html += '<span>';
							html += '<input class="input-file" name="file_upload" id="upload_footer_hover_img' + i + '" type="file" onchange="imgUpload(this);" data-index="' + i + '">';
							html += '<input type="hidden" id="footer_hover' + i + '" value="' + footer[i].img_src_hover + '">';
						html += '</span>';
						html += '<p id="text_footer_hover' + i + '" style="display:none;">选中后的图片<br><span>建议尺寸25*25</span></p>';
						html += '<i class="fa fa-close js-del-img" style="display:block;"></i>';
					html +='</div>';

				}else{

					html += '<div class="add-img js-img-footer-hover">';
						html += '<div class="img-block"><img id="img_footer_hover' + i + '"></div>';
						html += '<span>';
							html += '<input class="input-file" name="file_upload" id="upload_footer_hover_img' + i + '" type="file" onchange="imgUpload(this);" data-index="' + i + '">';
							html += '<input type="hidden" id="footer_hover' + i + '">';
						html += '</span>';
						html += '<p id="text_footer_hover' + i + '">选中后的图片<br><span>建议尺寸25*25</span></p>';
						html += '<i class="fa fa-close js-del-img"></i>';
					html +='</div>';

				}
			html += '</div>';
			html += '<div class="info" data-index="' + i + '">';
					
			if(!empty(footer) && !empty(footer[i]) && !empty(footer[i].menu_name)) html += getInputHTML("text",$cValue.footerMenuName,footer[i].menu_name);
			else html += getInputHTML("text",$cValue.footerMenuName);
			
			if(!empty(footer) && !empty(footer[i]) && !empty(footer[i].href)) html += getLinkHTML(footer[i].href);
			else html += getLinkHTML();
			html += '</div>';
		html += '</div>';
	}
	
	return html;
}

function getAuxiliaryBlankHTML(){
	
	var auxiliary_blank = getCustom().attr("data-auxiliary-blank");
	var height = $Default.auxiliaryBlankHeightMin;
	var bg_color = $cValue.auxiliaryBlankColor.value;

	if(!empty(auxiliary_blank)){
		auxiliary_blank = eval("(" + auxiliary_blank + ")");
		height = auxiliary_blank.height;
		bg_color = auxiliary_blank.bg_color;
	}
	
	var html = '<div class="control-edit auxiliary-blank">';
		html += '<label>空白高度：</label>';

		html += '<input type="text"class="input-common js-blank-height" value="' + height + '">';
		html += '<span>&nbsp;px</span>';
	html += '</div>';

	html += getInputHTML("color",$cValue.auxiliaryBlankColor,bg_color);
	
	return html;
}

function imgUpload(event) {
	
	var fileid = $(event).attr("id");
	var index = $(event).attr("data-index");
	var data = { 'file_path' : "custom_template" };
	var id = $(event).next().attr("id");
	var del_img = $(event).parent().parent().find("i");
	
	uploadFile({
		url: __URL(ADMINMAIN + '/goods/uploadimage'),
		fileId: fileid,
		data : data,
		callBack: function (res) {
			
			if(res.code){
				
				$("#" + id).val(res.data.path);
				$("#img_" + id).attr("src",__IMG(res.data.path)).css("max-height","100%").parent().show();
				$("#text_" + id).hide();
				del_img.show();
				
				switch(getCustom().attr("data-custom-flag")){
					
					case controlList.NavHyBrid:
						
						updateNavHyBridHTML();
						
						break;
					
					case controlList.Footer:
						
						updateFooterHTML(index,"img",__IMG(res.data.path));
						
						break;
					
					case controlList.GoodsList:
						
						//商品列表中的购买按钮样式
						$("#show_buy_button_style4").val(res.data.path);
						getCustom().find(".control-goods-buy-style>img").attr("src",__IMG(res.data.path));
						
						break;
					
					case controlList.GoodsClassify:
						
						$("#show_buy_button_style4").val(res.data.path);
						getCustom().find(".control-goods-buy-style>img").attr("src",__IMG(res.data.path));
						
						break;
					
					case controlList.ShowCase:
						
						getCustom().find("li:eq(" + index + ")").css({"background" : "url(" + __IMG(res.data.path) + ") no-repeat center/100% "}).children("div").css("visibility","hidden");
						
						break;
					
					case controlList.Notice:
						
						getCustom().css({"background" : "url(" + __IMG(res.data.path) + ") no-repeat center/100% "});
						
						break;
					
					case controlList.TextNavigation:
						
						getCustom().css({"background" : "url(" + __IMG(res.data.path) + ") no-repeat center/100% "});
						
						break;
					
					case controlList.Title:
						
						getCustom().css({"background" : "url(" + __IMG(res.data.path) + ") no-repeat center/100% "});
						
						break;
					
				}
				
				eval("bind" + getCustom().attr("data-custom-flag") + "Data()");
				showTip(res.message,"success");
				
			}else{
				
				showTip(res.message,"error");
				
			}
		}
	});

}

function getCouponsHTML(){
	var coupons = getCustom().attr("data-coupons");
	var coupons_style1_checked = "";
	var coupons_style2_checked = "";

	if(!empty(coupons)){

		coupons = eval("(" + coupons + ")");

		switch(parseInt(coupons.style)){

			case 1:

				coupons_style1_checked = 'checked="checked"';

				break;
				
			case 2:
				
				coupons_style2_checked = 'checked="checked"';
				
				break;

		}

	}else{

		coupons_style1_checked = 'checked="checked"';

	}

	var html = '';
		html += '<div class="control-edit coupons">';
			html += '<label>样式：</label>';
			html += '<div>';
				html += '<input type="radio" value="1" id="coupons_style1" name="coupons_style" ' + coupons_style1_checked + '>&nbsp;';
				html += '<label for="coupons_style1" class="label-for">样式一</label>';
				html += '<input type="radio" value="2" id="coupons_style2" name="coupons_style" ' + coupons_style2_checked + '>&nbsp;';
				html += '<label for="coupons_style2" class="label-for">样式二</label>';
				html += '<p class="description" style="margin:10px 0 0 140px;">根据优惠券的数量，手机端显示样式也会进行调整。</p>';
			html += '</div>';
		html += '</div>';

	return html;
}

/**
 * 获取自定义模块代码
 * 1、选择自定义页面，排除当前自己的
 * 1、排除当前自己的同时，排除自己被引用的
 * 2、如果当前编辑的模板被其他模板所引用，则不会出现
 */
function getCustomModuleHTML(){
	
	var custom_module = getCustom().attr("data-custom-module");
	if(!empty(custom_module)) custom_module = eval("(" + custom_module + ")");
	
	var html = '<div class="control-edit custom-module">';
			html += '<label>自定义模块：</label>';
			html += '<select class="select-common js-select-custom-module">';
			
			if(!empty(template_list) && template_list.length>0){

				var count = 0;
				for(var i=0;i<template_list.length;i++){

					var curr = template_list[i];
					var template_data = eval(curr.template_data);
					var flag = false;
					
					//如果当前模板被其他模板所引用了，则不能出现。防止递归出现的死循环
					for(var j=0;j<template_data.length;j++){
						
						if(template_data[j].control_name == controlList.CustomModule){

							var control_data = eval("(" + template_data[j].control_data +")");
							var data = eval("(" + control_data.custom_module + ")");

							if($("#hidden_id").val() == data.module_id){

								flag = true;
								break;

							}

						}

					}
					
					if(flag) continue;
					count++;
					
					if(!empty(custom_module) && custom_module.module_id == curr.id){

						html += '<option value="' + curr.id + '" data-module-name="' + curr.template_name + '" selected="selected">' + curr.template_name + "</option>";

					}else{

						html += '<option value="' + curr.id + '" data-module-name="' + curr.template_name + '">' + curr.template_name + "</option>";

					}

				}

				if(count == 0) html += '<option value="0">暂无可用的模块</option>';

			}else{

				html += '<option value="0">暂无可用的模块</option>';

			}

			html += '</select>';
			html += '<p class="description" style="margin:10px 0 0 140px;">选择自定义模块后，左侧实时预览暂不支持显示其包含的自定义模块数据（查询数据排除当前自定义模板）。<br/>';
				html += '<strong style="color:#FF5722;">备注：<br/>1、不能选择当前编辑的模块。</br>2、如果当前编辑的模板被其他模板所引用，则不会出现</strong>';
			html += '</p>';
		html += '</div>';

	return html;
}

function getVideoHTML(){
	
	var video = getCustom().attr("data-video");
	var url = "";
	var padding = 0;
	if(!empty(video)){

		video = eval("(" + video + ")");
		url = video.url;
		padding = video.padding;

	}

	var html = '<div class="video-upload-pre">';
			html += '<pre>PHP默认上传限制为2MB，需要在php.ini配置文件中修改“post_max_size”和“upload_max_filesize”的大小。<br><b>(注：视频地址、视频上传文件填写一个即可！)</b></pre>';
		html += '</div>';
		html += getInputHTML("text",$cValue.videoUrl,url);
		html += '<div class="control-edit video-upload">';
			html += '<label>视频上传：</label>';
			html += '<div class="add-img">';
				html += '<span>';
					html += '<input class="input-file" name="file_upload" id="videoupload" type="file" onchange="fileUpload(this);">';
					html += '<input type="hidden" id="video_url" value="' + url + '">';
				html += '</span>';
				html += '<p id="text_video_url"><button>上传文件</button></p>';
				
			html += '</div>';
		html += '</div>';
	
		html += '<div class="control-edit video-url-info">';
			html += '<label>文件地址：</label>';
			html += '<span>' + url + "</span>";
		html += '</div>';
	
		html += getPaddingHTML(padding);

	return html;
}

/**
 * 文件上传（视频、音频）
 */
function fileUpload(event) {
	
	var fileid = $(event).attr("id");
	var index = $(event).attr("data-index");
	var data = { 'file_path' : "custom_template_video" };
	var id = $(event).next().attr("id");
	var video = getCustom().find("video").attr("id");
	var dom = document.getElementById(fileid);
	var file =  dom.files[0];//File对象;
	var fileTypeArr = ['video/mp4'];
	var flag = true;
	if(!empty(file)){

		for(var i=0;i<fileTypeArr.length;i++){

			if(file.type == fileTypeArr[i]){

				flag = false;
				break;

			}

		}

	}
	if(flag){

		showTip("文件类型不合法，请上传.mp4文件","warning");

	}else{
		
		uploadFile({
			url: __URL(ADMINMAIN + '/goods/uploadvideo'),
			fileId: fileid,
			data : data,
			callBack: function (res) {
				
				if(res.code){
					
					$("#" + id).val(__IMG(res.data.path));
					$(".video-url-info span").text(res.data.path);
					var myPlayer = videojs(video);
					var videoUrl = __IMG(res.data.path);
					videojs(video).ready(function(){
						
						var myPlayer = this;
						myPlayer.src(videoUrl);
						myPlayer.load(videoUrl);
						myPlayer.play();
						bindVideoData();
						
					});
					
					showTip(res.message,"success");
					
				}else{
					
					showTip(res.message,"error");
					
				}
			}
		});

	}

}

/**
 * 上下边距代码
 * @returns {String} html
 */
function getPaddingHTML(value){

	var padding = 0;
	if(!empty(value)) padding = value;
	
	var html = '<div class="control-edit padding">';
		html += '<label>上下边距：</label>';
		html += '<input type="text" class="input-common js-padding" data-jsclass="js-padding" value="' + padding + '" data-default-value="0">';
		html += '<span>&nbsp;px</span>';
	html += '</div>';
	return html;

}

function getShowCaseHTML(){

	//布局方式
	var two_column_checked = "";
	var three_column_checked = "";
	
	//图片间隙
	var keep_checked = "";
	var clear_checked = "";
	
	var padding = 0;
	
	//提示信息
	var pre_html = getShowCasePreHTML(2);
	
	var show_case = getCustom().attr("data-show-case");
	if(!empty(show_case)){
		
		show_case = eval("(" + show_case + ")");
		if(show_case.layout == 2){
			two_column_checked = 'checked="checked"';
		}else if(show_case.layout == 3){
			three_column_checked = 'checked="checked"';
			pre_html = getShowCasePreHTML(3);
		}
		
		if(show_case.clearance == 0) clear_checked = 'checked="checked"';
		else keep_checked = 'checked="checked"';
		
		padding = show_case.padding;
		
	}else{
		
		two_column_checked = 'checked="checked"';
		keep_checked = 'checked="checked"';
		
	}
	
	var html = pre_html;
		html += '<div class="control-edit show-case-layout">';
			html += '<label>布局方式：</label>';
			html += '<div>';
				html += '<input type="radio" value="2" id="show-case-two-column" name="show-case-layout" ' + two_column_checked + '>&nbsp;';
				html += '<label for="show-case-two-column" class="label-for">2列</label>';
				
				html += '<input type="radio" value="3" id="show-case-three-column" name="show-case-layout" ' + three_column_checked + '>&nbsp;';
				html += '<label for="show-case-three-column" class="label-for">3列</label>';
			html += '</div>';
		html += '</div>';

		html += '<div class="control-edit show-case-clearance">';
			html += '<label>图片间隙：</label>';
			html += '<div>';
				html += '<input type="radio" value="1" id="show-case-keep" name="show-case-clearance" ' + keep_checked + '>&nbsp;';
				html += '<label for="show-case-keep" class="label-for">保留</label>';
				
				html += '<input type="radio" value="0" id="show-case-clear" name="show-case-clearance" ' + clear_checked + '>&nbsp;';
				html += '<label for="show-case-clear" class="label-for">消除</label>';
			html += '</div>';
		html += '</div>';
		
		html += getPaddingHTML(padding);
		
		for(var i=0;i<3;i++){
			
			var show_text_checked = "";
			var hidden_text_checked = "";
			var text = "";
			var src = "";
			var href = "";
			if(!empty(show_case)){

				var item = show_case.itemList[i];

				if(item.show_text == 1) show_text_checked = 'checked="checked"';

				else if(item.show_text == 0) hidden_text_checked = 'checked="checked"';

				text = item.text;
				src = item.src;
				href = item.href;

			}else{

				show_text_checked = 'checked="checked"';

			}
			
			html += '<div class="control-edit show-case-info">';
				html += '<div class="add-img">';

					if(!empty(src)){

						html += '<div class="img-block" style="display:block;"><img id="img_show_case_img' + i + '" src="' + __IMG(src) + '" style="max-height:100%;"></div>';
						html += '<span>';
							html += '<input class="input-file" name="file_upload" id="upload_show_case_img' + i + '" type="file" onchange="imgUpload(this);" data-index="' + i + '">';
							html += '<input type="hidden" id="show_case_img' + i + '" value = ' + src + '>';
						html += '</span>';
						
					}else{
						
						html += '<div class="img-block"><img id="img_show_case_img' + i + '"></div>';
						html += '<span>';
							html += '<input class="input-file" name="file_upload" id="upload_show_case_img' + i + '" type="file" onchange="imgUpload(this);" data-index="' + i + '">';
							html += '<input type="hidden" id="show_case_img' + i + '">';
						html += '</span>';
						html += '<p id="text_show_case_img' + i + '">添加图片<br><span>参考上方建议尺寸</span></p>';
						
					}

				html += '</div>';
				
				html += '<div class="info" data-index="' + i + '">';
					html += getLinkHTML(href);

					html += '<div class="control-edit show-case-show-text">';
						html += '<label>是否显示文字：</label>';
						html += '<div>';
							html += '<input type="radio" ' + show_text_checked + ' value="1" id="show-case-show-text' + i + '" name="show-case-show-text' + i + '">&nbsp;';
							html += '<label for="show-case-show-text' + i + '" class="label-for">显示</label>';
							
							html += '<input type="radio" ' + hidden_text_checked + ' value="0" id="show-case-hidden-text' + i + '" name="show-case-show-text' + i + '">&nbsp;';
							html += '<label for="show-case-hidden-text' + i + '" class="label-for">隐藏</label>';
						html += '</div>';
					html += '</div>';
					
					if(!empty(hidden_text_checked)) html += '<div class="control-edit show-case-text" style="visibility:hidden;">';
					else html += '<div class="control-edit show-case-text">';

						html += '<label>显示文字：</label>';
						html += '<input type="text" class="input-common js-show-case-text" value="' + text + '">';
					html += '</div>';
				
				html += '</div>';
			html += '</div>';
		}
	return html;
}

/**
 * 根据所选择的布局方式，返回不同的图片尺寸提示信息
 * @param layout [2列,3列]
 */
function getShowCasePreHTML(layout){

	if(layout == 2) return '<div class="show-case-pre"><pre>长方形图片建议尺寸：<b style="font-size: 14px;">450px*580px</b><br/>正方形图片建议尺寸：<b style="font-size: 14px;">290px*280px</b></pre></div>';
	else if(layout == 3) return '<div class="show-case-pre"><pre>图片建议尺寸：<b style="font-size: 14px;">240px*420px</b></pre></div>';

}

function getNoticeHTML(){

	var notice = getCustom().attr("data-notice");
	var notice_bg_color = $cValue.noticeBgColor.value;
	var notice_text_color = $cValue.noticeTextColor.value;
	var scroll_way = 0;//滚动方式
	var padding = 0;
	var bg_img = "";
	var items = null;

	if(!empty(notice)){

		notice = eval("(" + notice + ")");
		notice_bg_color = notice.bg_color;
		notice_text_color = notice.text_color;
		scroll_way = notice.scroll_way;
		padding = notice.padding;
		bg_img = notice.bg_img;
		items = notice.items;

	}

	var html = '';
	
	html += '<div style="background: #ffffff;border: 1px solid #e5e5e5;">';

		html += getInputHTML("color",$cValue.noticeBgColor,notice_bg_color);
		
		html += getBgImgHTML("notice_img",bg_img);

		html += getInputHTML("color",$cValue.noticeTextColor,notice_text_color);
		
		html += getTextSizeHTML();
		
		html += getTextAlignHTML();
		
		html += getPaddingHTML(padding);
		
		html += '<div class="control-edit font-size">';
			html += '<label>滚动方式：</label>';
			html += '<select class="select-common js-select-notice-scroll-way" data-jsclass="js-select-notice-scroll-way" data-default-value="0">';
				if(scroll_way == 0){

					html += '<option value="0" selected="selected">无</option>';
					html += '<option value="1">左右滚动</option>';
					html += '<option value="2">上下滚动</option>';

				}else if(scroll_way == 1){

					html += '<option value="0">无</option>';
					html += '<option value="1" selected="selected">左右滚动</option>';
					html += '<option value="2">上下滚动</option>';

				}else if(scroll_way == 2){
					
					html += '<option value="0">无</option>';
					html += '<option value="1">左右滚动</option>';
					html += '<option value="2" selected="selected">上下滚动</option>';

				}
			html += '</select>';
			html += '<i class="fa fa-refresh fr"></i>';
		html += '</div>';

	html += '</div>';
	
	if(!empty(items)){
		for(var i=0;i<items.length;i++){

			html += getNoticeItemsHTML(items[i].notice,i,items[i].href);

		}
	}else{

		html += getNoticeItemsHTML("『公告内容』");
	}

	html += '<div class="control-edit notice-new-addition"><span class="add-notice">+</span></div>';

	return html;

}

function getNoticeItemsHTML(notice_content,flag,href){
	var html = '<div class="notice-items">';
		html += '<div class="control-edit notice">';
			html += '<label><span>*</span>公告：</label>';
			html += '<textarea placeholder="请输入公告内容，不得超出200个字符" class="js-notice-content">' + notice_content + '</textarea>';
		html += '</div>';
		if(flag) html += '<i class="notice-close" title="删除公告"></i>';
		if(href) html += getLinkHTML(href);
		else html += getLinkHTML();
	html += '</div>';
	return html;
}

function getTextNavigationHTML(){

	var html = "";
	var text_color = $cValue.textNavigationColor.value;
	var bg_color = $cValue.textNavigationBgColor.value;
	var padding = 10;
	var arrangement = 1;
	var text_navigation = getCustom().attr("data-text-navigation");
	var items = new Array();
	var bg_img = "";

	if(!empty(text_navigation)){
	
		text_navigation = eval("(" + text_navigation + ")");
		text_color = text_navigation.text_color;
		bg_color = text_navigation.bg_color;
		items = text_navigation.items;
		padding = text_navigation.padding;
		arrangement = text_navigation.arrangement;
		bg_img = text_navigation.bg_img;

	}
	
	html += '<div style="background: #ffffff;border: 1px solid #e5e5e5;">';

		html += getTextSizeHTML();
		html += getInputHTML("color",$cValue.textNavigationColor,text_color);
		html += getInputHTML("color",$cValue.textNavigationBgColor,bg_color);
		
		html += getBgImgHTML("nav_text_img",bg_img);
		
		html += getPaddingHTML(padding);
		html += getTextAlignHTML();

		html += '<div class="control-edit nav-arrangement">';
			html += '<label>排列方式：</label>';
			html += '<select class="select-common js-nav-arrangement" data-jsclass="js-nav-arrangement" data-default-value="1">';

				if(arrangement == 1){

					html += '<option value="1" selected="selected">竖排</option>';
					html += '<option value="2">橫排</option>';
					
				}else{
					
					html += '<option value="1">竖排</option>';
					html += '<option value="2" selected="selected">橫排</option>';
					
				}

			html += '</select>';
			html += '<i class="fa fa-refresh fr"></i>';
		html += '</div>';

	html += '</div>';

	html += '<div class="control-edit text-navigation-block">';

		if(items.length>0){

			html += getInputHTML("text:required",$cValue.textNavigation,items[0].text);
			html += getLinkHTML(items[0].href);

		}else{

			html += getInputHTML("text:required",$cValue.textNavigation);
			html += getLinkHTML();

		}

	html += '</div>';
	
	if(items.length>0 && arrangement == 2){
		
		html += '<div class="control-edit text-navigation-new-addition" style="display:block;">';
		
			for(var i=1;i<items.length;i++) html += getTextNavigationBlockHTML(items[i].text,items[i].href);
				
			html += '<span class="add-text-navigation">+</span>';
		
		html += '</div>';
		
	}else{
		
		html += '<div class="control-edit text-navigation-new-addition">';
		
		html += '<span class="add-text-navigation">+</span>';
	
		html += '</div>';
		
	}

	return html;
}

/**
 * 获取一个文本导航编辑代码
 */
function getTextNavigationBlockHTML(text,href){

	var html = '';
	html += '<div class="control-edit text-navigation-block">';
		html += getInputHTML("text:required",$cValue.textNavigation,text);
		html += getLinkHTML(href);
		html += '<i class="text-navigation-close" title="删除导航"></i>';
	html += '</div>';

	return html;
}

function updateTextNavigationHTML(){

	var text_navigation = getCustom().attr("data-text-navigation");
	var style = "";
	var html = "";
	if(!empty(text_navigation)){

		text_navigation = eval("(" + text_navigation + ")");
		style = 'style="font-size:' + text_navigation.font_size + 'px;color:' + text_navigation.text_color + ';text-align:' + align_array[text_navigation.text_align-1] + ';"';

	}

	switch(parseInt($(".js-nav-arrangement").val())){
		case 1:
			
			//竖排
			html += '<h5 data-editable="1" ' + style + '>';
				html += '<span data-editable="1" ' + style + '>' + $(".control-edit.text-navigation-block:eq(0)").find("input").val() + '</span>';
				html += '<i class="fa fa-angle-right" data-editable="1" ' + style + '></i>';
			html += '</h5>';
			$(".control-edit.text-navigation-new-addition").slideUp();
			
			break;
			
		case 2:
			
			//横排
			html += '<ul class="text-navigation-horizontal" id="slider">';
			$(".control-edit.text-navigation-block").each(function(){
				
				html += '<li data-editable="1" ' + style + '>' + $(this).find("input").val() + '</li>';
			});
			html += '</ul>';
			$(".control-edit.text-navigation-new-addition").slideDown();
			
			break;
	}

	getCustom().children("div:first").html(html);

}

function getGoodsSearchHTML(){
	
	var text_color = $cValue.searchTextColor.value;
	var bg_color = $cValue.searchBgColor.value;
	var input_bg_color = $cValue.searchInputBgColor.value;
	var placeholder = "";
	var html = '';
	var goods_search = getCustom().attr("data-goods-search");
	
	if(!empty(goods_search)){

		goods_search = eval("(" + goods_search + ")");
		text_color = goods_search.text_color;
		bg_color = goods_search.bg_color;
		input_bg_color = goods_search.input_bg_color;
		if(goods_search.placeholder != $cValue.searchPlaceholder.default_value) placeholder = goods_search.placeholder;

	}
	
	html += getTextSizeHTML();
	html += getInputHTML("color",$cValue.searchTextColor,text_color);
	html += getInputHTML("color",$cValue.searchBgColor,bg_color);
	html += getInputHTML("color",$cValue.searchInputBgColor,input_bg_color);
	html += getInputHTML("text",$cValue.searchPlaceholder,placeholder);

	return html;
}

/**
 * 获取背景图代码
 * @param flag 标识
 * @param bg_img 图片路径
 * @returns {String}
 */
function getBgImgHTML(flag,bg_img){

	if(empty(bg_img)) bg_img = "";
	
	var html = '';
	html += '<div class="control-edit background">';
		html += '<label>背景图：</label>';
		html += '<div class="add-img">';

			if(!empty(bg_img)){

				html += '<div class="img-block" style="display:block;"><img id="img_' + flag + '" style="max-height:100%;" src="' + __IMG(bg_img) + '"></div>';

			}else{

				html += '<div class="img-block"><img id="img_' + flag + '"></div>';

			}
			html += '<span>';
				html += '<input class="input-file" name="file_upload" id="upload_' + flag + '" type="file" onchange="imgUpload(this);">';
				html += '<input type="hidden" id="' + flag + '" value="' + bg_img + '">';
			html += '</span>';

			if(!empty(bg_img)){

				html += '<p id="text_' + flag + '" style="display:none;">添加背景图片<br><span>建议尺寸320*20</span></p>';
				html += '<i class="fa fa-close js-del-img" style="display:block;"></i>';

			}else{

				html += '<p id="text_' + flag + '">添加背景图片<br><span>建议尺寸320*20</span></p>';
				html += '<i class="fa fa-close js-del-img"></i>';

			}
		html += '</div>';
	html += '</div>';

	return html;
}

function getTitleHTML(){
	
	var title = getCustom().attr("data-title");
	var title_name = "『标题名』";
	var subtitle_name = "『副标题』";
	var whether_bold = 1;
	var text_color = $cValue.titleTextColor.value;
	var bg_color = $cValue.titleBgColor.value;
	var bg_img = "";
	var padding = 0;
	var href = "";
	var html = '';
	
	if(!empty(title)){

		title = eval("(" + title + ")");
		title_name = title.title_name;
		subtitle_name = title.subtitle_name;
		whether_bold = title.whether_bold;
		text_color = title.text_color;
		bg_color = title.bg_color;
		bg_img = title.bg_img;
		padding = title.padding;
		href = title.href;

	}

	html += getInputHTML("text:required",$cValue.title,title_name);
	
	html += getInputHTML("text",$cValue.subTitle,subtitle_name);
	
	html += getTextAlignHTML();
	
	html += '<div class="control-edit text-align">';
		html += '<label>是否加粗：</label>';
		
		if(whether_bold == 1){
			html += '<input type="radio" value="1" checked="checked" id="whether_bold_yes" name="whether_bold">&nbsp;';
			html += '<label for="whether_bold_yes" class="label-for">加粗</label>';
			
			html += '<input type="radio" value="2" id="whether_bold_no" name="whether_bold">&nbsp;';
			html += '<label for="whether_bold_no" class="label-for">不加粗</label>';
		}else{
			html += '<input type="radio" value="1" id="whether_bold_yes" name="whether_bold">&nbsp;';
			html += '<label for="whether_bold_yes" class="label-for">加粗</label>';
			
			html += '<input type="radio" value="2" checked="checked" id="whether_bold_no" name="whether_bold">&nbsp;';
			html += '<label for="whether_bold_no" class="label-for">不加粗</label>';
			
		}
		
	html += '</div>';
	
	html += getInputHTML("color",$cValue.titleTextColor,text_color);
	
	html += getInputHTML("color",$cValue.titleBgColor,bg_color);
	
	html += getBgImgHTML("title_img", bg_img);
	
	html += getPaddingHTML(padding);
	
	html += getLinkHTML(href);
	
	return html;
}

function bindTitleData(){
	
	var json = {
		title_name : $(".js-title-name").val(),
		subtitle_name : $(".js-subtitle-name").val(),
		text_align : $("input[name='text_align']:checked").val(),
		text_color : $(".js-text-color").val(),
		bg_color : $(".js-bg-color").val(),
		bg_img : $("#title_img").val(),
		padding : $(".js-padding").val(),
		whether_bold : $("input[name='whether_bold']:checked").val(),
		href : !empty($(".control-edit.link>.selected").attr("data-href")) ? $(".control-edit.link>.selected").attr("data-href") :  $Default.href
	};
	
	getCustom().attr("data-title",JSON.stringify(json));
}

function bindGoodsSearchData(){
	
	var json = {
		font_size : $(".js-select-font-size").val(),
		text_color : $(".js-text-color").val(),
		bg_color : $(".js-bg-color").val(),
		input_bg_color : $(".js-input-bg-color").val(),
		placeholder : !empty($(".js-placeholder").val()) ? $(".js-placeholder").val() : $cValue.searchPlaceholder.default_value
	};
	getCustom().attr("data-goods-search",JSON.stringify(json));

}

function bindTextNavigationData(){
	
	var items = new Array();

	$(".control-edit.text-navigation-block").each(function(){

		var obj = {
			text : $(this).find(".js-text-navigation").val(),
			href : !empty($(this).find(".control-edit.link>.selected").attr("data-href")) ? $(this).find(".control-edit.link>.selected").attr("data-href") :  $Default.href
		};

		items.push(obj);

	});
	
	var json = {
		bg_color : $(".js-bg-color").val(),
		font_size : $(".js-select-font-size").val(),
		text_color : $(".js-text-color").val(),
		text_align : $("input[name='text_align']:checked").val(),
		padding : $(".js-padding").val(),
		items : items,
		bg_img : $("#nav_text_img").val(),
		arrangement : $(".js-nav-arrangement").val()
	};

	getCustom().attr("data-text-navigation",JSON.stringify(json));

}

function bindNoticeData(){

	var items = new Array();
	$(".notice-items").each(function(){

		var obj = {
			notice : $(this).find("textarea").val(),
			href : !empty($(this).find(".control-edit.link>.selected").attr("data-href")) ? $(this).find(".control-edit.link>.selected").attr("data-href") : $Default.href
		};
		items.push(obj);

	});

	var json = {
		bg_color : $(".js-bg-color").val(),
		font_size : $(".js-select-font-size").val(),
		text_color : $(".js-text-color").val(),
		scroll_way : $(".js-select-notice-scroll-way").val(),
		text_align : $("input[name='text_align']:checked").val(),
		padding : $(".js-padding").val(),
		bg_img : $("#notice_img").val(),
		items : items
	};

	getCustom().attr("data-notice",JSON.stringify(json));

}

function bindShowCaseData(){
	
	var itemList = new Array();

	$(".show-case-info").each(function(){

		var obj = {
			src : $(this).find("input[type='hidden']").val(),
			show_text : $(this).find("input[name^='show-case-show-text']:checked").val(),
			text : $(this).find(".js-show-case-text").val(),
			href : !empty($(this).find(".control-edit.link>.selected").attr("data-href")) ? $(this).find(".control-edit.link>.selected").attr("data-href") :  $Default.href
		};

		itemList.push(obj);

	});

	var json = {
		layout : $("input[name='show-case-layout']:checked").val(),
		clearance : $("input[name='show-case-clearance']:checked").val(),
		padding : $(".js-padding").val(),
		itemList : itemList
	};

	getCustom().attr("data-show-case",JSON.stringify(json));
	
}

function bindVideoData(){
	
	var json = {
		url : $("#video_url").val(),
		padding : $(".js-padding").val(),
		id : getCustom().find("video").attr("id")
	};
	getCustom().attr("data-video",JSON.stringify(json));

}

function bindCustomModuleData(){
	
	var json = {
		module_id : $(".js-select-custom-module").val(),
		module_name : $(".js-select-custom-module").find("option:checked").attr("data-module-name")
	};
	
	//如果自定义模块只有一个，则左侧实时预览界面，要更新文字内容
	if($(".js-select-custom-module option").length == 1) getCustom().find("article>p").text($(".js-select-custom-module option").text());
	getCustom().attr("data-custom-module",JSON.stringify(json));

}

function bindGoodsListData(){
	
	var json = {
		goods_source : $(".js-goods-source").val(),																//商品来源
		goods_limit_count : $("input[name='showcount']:checked").val(),											//显示个数
		goods_list_type : $("input[name='list_type']:checked").val(),											//列表样式
		goods_show_goods_name : Number($("#show_goods_name").is(":checked")),									//显示商品名称
		goods_show_goods_price : Number($("#show_goods_price").is(":checked")),									//显示价格
		goods_show_buy_button : Number($("#show_buy_button").is(":checked")),									//显示购买按钮
		goods_buy_button_style : $("input[name='buy_button_style']:checked").attr("data-buy-button-style"),		//购买按钮样式选择
		goods_buy_button_src : $("input[name='buy_button_style']:checked").val()								//购买按钮图片路径
	};
	getCustom().attr("data-goods-list",JSON.stringify(json));

}

function bindCouponsData(){
	
	var json = { style : $("input[name='coupons_style']:checked").val() };
	getCustom().attr("data-coupons",JSON.stringify(json));

}

function bindFooterData(){
	var json = new Array();
	
	$(".js-footer-menu-name").each(function(){
		//菜单名称，颜色，选中颜色
		json.push({ menu_name : $(this).val(), color : $(".js-text-color").val(), color_hover : $(".js-text-color-hover").val() });
	});
	
	$(".control-edit.link>.selected").each(function(i){
		json[i].href = !empty($(this).attr("data-href")) ? $(this).attr("data-href") : $Default.href;
	});
	
	$(".js-img-footer input[type='hidden']").each(function(i){
		json[i].img_src = $(this).val();//未选择的图片
	});
	
	$(".js-img-footer-hover input[type='hidden']").each(function(i){
		json[i].img_src_hover = $(this).val();//选中的图片
	});
	
	// //底部菜单：调整宽度
	getCustom().find("li").css("width",parseInt(100/$Default.footerItemCount) + "%");
	getCustom().attr("data-footer",JSON.stringify(json));
}

function bindGoodsClassifyData(){
	
	var html = "";
	var json = new Array();

	if($(".goods-classify-list>ul>li").length){
		
		getCustom().find("aside>ul").html("");
		$(".goods-classify-list>ul>li").each(function(i){

			var id = $(this).attr("data-classify-id");
			var name = $(this).attr("data-classify-name");
			var show_count = $(this).attr("data-show-count");
			html = "<li title='" + name + "'>" + name + "</li>";
			if(i==0) html = "<li class='selected' title='" + name + "'>" + name + "</li>";

			json.push({
				id : id,
				name : name,
				show_count : show_count,
				goods_show_buy_button : Number($("#show_buy_button").is(":checked")),
				goods_buy_button_style : $("input[name='buy_button_style']:checked").attr("data-buy-button-style"),
				goods_buy_button_src : $("input[name='buy_button_style']:checked").val()
			});

			getCustom().find("aside ul").append(html);

		});
		
	}else{
		
		//还原
		var temp_aside_count = 3;
		for(var i=0;i<temp_aside_count;i++){

			if(i==0) html += '<li class="selected">商品分类一</li>';
			else if(i%3==1) html += '<li>商品分类二</li>';
			else if(i%3==2) html += '<li>商品分类N</li>';

		}

		getCustom().find("aside ul").html(html);
		
	}
	getCustom().attr("data-goods-classify",JSON.stringify(json));
}

function bindImgAdData(){
	
	//数据库中查询的数据
	if(!empty(template_data)){
		
		//重置id，不然无法进行轮播
		var img_ad = eval(getCustom().attr("data-img-ad"));

		if(!empty(img_ad)){

			var new_id = getCustom().attr("id") + $(".custom-main [data-custom-flag='" + controlList.ImgAd + "']").length;
			getCustom().attr("id",new_id).find("a").attr("href","#" + new_id);

			if(img_ad[0].adv_show_type == 2){
				
				getCustom().addClass("slide");//多图广告添加轮播
				$('.carousel').carousel();//轮播停留时间
				
			}

		}

	}

	var json = new Array();
	var adv_show_type = $("input[name='show_img_ad_type']:checked").val();

	$(".control-edit .add-img input[type='hidden']").each(function(i){

		json.push({ src : $(this).val(), adv_show_type : adv_show_type });
		if(adv_show_type == $Default.advShowType[0] && i == 0) return false;//单图只循环一次

	});

	$(".control-edit.link>.selected").each(function(i){

		json[i].href = !empty($(this).attr("data-href")) ? $(this).attr("data-href") : $Default.href;
		if(adv_show_type == $Default.advShowType[0] && i == 0) return false;//单图只循环一次

	});

	getCustom().attr("data-img-ad",JSON.stringify(json));

	for(var i=0;i<json.length;i++){

		var src = STATIC + "/wapCustomTemplate/images/control_img_ad_single_default.png";
		if(!empty(json[i].src)) src = __IMG(json[i].src);
		getCustom().find("img:eq(" + i + ")").attr("src",src);
		getCustom().find("img").unbind("load");

	}

}

function bindNavHyBridData(){

	var items = new Array();
	$(".control-edit.nav-hybrid").each(function(){

		var obj = {
			text : $(this).find(".js-nav-hybrid-text").val(),
			href : !empty($(this).find(".control-edit.link>.selected").attr("data-href")) ? $(this).find(".control-edit.link>.selected").attr("data-href") : $Default.href,
			src : $(this).find(".add-img input[type='hidden']").val()
		};
		items.push(obj);

	});

	var json = {
		text_color : $(".js-text-color").val(),
		font_size : $(".js-select-font-size").val(),
		type : $("input[name='line_columns']:checked").val(),
		bg_color : $(".js-bg-color").val(),
		items : items
	};

	getCustom().attr("data-nav-hybrid",JSON.stringify(json));
}

function bindAuxiliaryBlankData(){
	
	var json = {
		height : $(".js-blank-height").val(),
		bg_color : $(".js-bg-color").val()
	};

	getCustom().attr("data-auxiliary-blank",JSON.stringify(json));
}

function bindLink(){
	
	try {

		eval("bind" + getCustom().attr("data-custom-flag") + "Data()");

	} catch (error) {
		
		var href = new Array();
		$(".control-edit.link .selected").each(function(){
			href.push($(this).attr("data-href"));
		});
		getCustom().attr("data-href",href.toString());
		console.log("bind link error: " + error);

	}

}

function getCustomTemplateNameHTML(){
	return getInputHTML("text:required",$cValue.customTemplateName,$(".custom-template>header>h4").attr("data-custom-template-name"));
}

function bindCustomTemplateNameData(){}

function getRichTextHTML(){
	
	//处理异步请求，导致的错误显示问题
	setTimeout(function(){

		var content = "『富文本』";
		if(!empty(getCustom().attr("data-rich-text"))) content = getCustom().attr("data-rich-text");
		
		ue.ready(function() {

			getCustom().attr("data-rich-text",content);
			ue.setContent(content);

		});

	},1);

	return "";
}

function getAuxiliaryLineHTML(){
	var auxiliary_line = getCustom().attr("data-auxiliary-line");
	var border_color = $cValue.borderColor.value;
	if(!empty(auxiliary_line)){

		auxiliary_line = eval("(" + auxiliary_line + ")");
		border_color = auxiliary_line.border_color;
	}
	return getInputHTML("color",$cValue.borderColor,border_color);
}

function updateNoticeHTML(){

	var html = '';
	var notice = getCustom().attr("data-notice");
	var style = '';

	if(!empty(notice)){

		notice = eval("(" + notice + ")");
		style = 'style="font-size:' + notice.font_size + 'px;color:' + notice.text_color + ';text-align:' + align_array[notice.text_align-1] + ';"';

	}
	$(".notice-items").each(function(){

		if($(".js-select-notice-scroll-way").val() == 1){
			
			html += '<li ' + style + '><marquee data-editable="1" data-editable="1">' + $(this).find("textarea").val() + '</marquee></li>';
			return false;

		}else{

			html += '<li ' + style + ' data-editable="1">' + $(this).find("textarea").val() + '</li>';

		}

	});
	
	switch(parseInt($(".js-select-notice-scroll-way").val())){
		case 0:
			$(".notice-new-addition").slideUp();
			$(".notice-items:gt(0)").remove();
			getCustom().removeAttr("data-interval-id");
			getCustom().find(".notice-block ul").removeAttr("style");
			break;
		case 1:
			$(".notice-new-addition").slideUp();
			$(".notice-items:gt(0)").remove();
			getCustom().removeAttr("data-interval-id");
			getCustom().find(".notice-block ul").removeAttr("style");
			break;
		case 2:
			$(".notice-new-addition .add-notice").show();
			$(".notice-new-addition").slideDown();
			noticeScrollUpDown();
			break;
	}

	getCustom().find(".notice-block ul").html(html);
	
}

function bindRichTextData(){

	//调整百度编辑器的位置、以及显示
	var self = getCustom();
	$(".pt.pt-left .cont").css("min-height","604px");//字数统计23px，边框2px，工具栏79，正文500px
	var l = $(".pt.pt-left").offset().left + 10;
	var t = $(".pt.pt-left").offset().top;
	var t = self.offset().top + 10;
	$("#richText").css({ position : "absolute", top : t, left : l, display : "block", zIndex : 1001 });

	//第一次加载标识
	if(empty(self.attr("data-first-init-flag"))) self.attr("data-first-init-flag",1);

	setTimeout(function(){

		//只有在第一次加载的情况下需要重新调整top值
		if(self.attr("data-first-init-flag") == 1){

			var results = 0;
			var last_height = 0;
			$(".custom-main .draggable-element").each(function(i){
				if(i>=self.index()) results+=$(this).outerHeight();
				if((i+1) == $(".custom-main .draggable-element").length) last_height = $(this).outerHeight();
			});
			//公式:当前top + 当前富文本的实际高度 + 当前位置之后的模块高度累加 - 最后一个模块的高度 - 1
			$(".pt.pt-left").css("top",(t+results-last_height-10) + "px");
			$(".custom-main .draggable-element:eq(17)")
			self.attr("data-first-init-flag",0);
		}

	},1500);

}

function bindAuxiliaryLineData(){

	var json = { border_color : $(".js-border-color").val() };
	getCustom().attr("data-auxiliary-line",JSON.stringify(json));

}

/**
 * 切换优惠券样式代码
 * 创建时间：2018年1月29日11:30:23
 */
function updateCouponsHTML(){
	var coupons = getCustom().attr("data-coupons");
	var html = '';
	if(!empty(coupons)){
		coupons = eval("("+coupons+")");
	}
	if(coupons.style == 1){
		
		html += '<div class="coupon">';
			html += '<img src="' + STATIC + '/wapCustomTemplate/images/index_coupon.png' + '" class="background_img">';
			html += '<p><span>￥</span>' + getRandomPrice() + '</p>';
			html += '<img src="' + STATIC + '/wapCustomTemplate/images/already_received.png' + '" class="already_received">';
		html += '</div>';

		html += '<div class="coupon">';
			html += '<img src="' + STATIC + '/wapCustomTemplate/images/index_coupon.png' + '" class="background_img">';
			html += '<p><span>￥</span>' + getRandomPrice() + '</p>';
			html += '<img src="' + STATIC + '/wapCustomTemplate/images/already_received.png' + '" class="already_received">';
		html += '</div>';
	}else if(coupons.style == 2){
		for(var j=0;j<3;j++){
			html += '<div class="coupons-style2">';
				html += '<span class="money-number">￥' + getRandomPrice() + '</span>';
				html += '<p class="explanation">满15.00可用</p>';
				html += '<span class="get">领取</span>';
			html += '</div>';
		}
	}
	getCustom().find(".coupon").remove();
	getCustom().find(".coupons-style2").remove();
	getCustom().append(html);
}