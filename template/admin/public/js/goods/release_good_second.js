	/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 山西牛酷信息科技有限公司, 保留所有权利。
 * ---------------------------------------------- 官方网址:
 * http://www.niushop.com.cn 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 * 
 * @date : 2016年12月16日 16:17:13
 * @version : v1.0.0.0 商品发布中的第二步，编辑商品信息
 * 修改时间：2018年3月2日14:41:21 Line：1423
 * 更新日志：
 * 1、 [新增] 修改规格值功能
 * 2、 [新增] 删除规格、规格值功能
 * 3、 [新增] 修改商品时，如果该商品没有选择过规格，则加载公共规格列表
 * 4、 [新增] 修改商品时，筛选条件默认选择当前商品，也可以选择其他筛选条件，查询公共的规格列表
 * 5、 [新增] 筛选条件选择当前商品规格时，显示已选中的商品规格
 * 6、 [优化] 清空规格时，会弹出确认提示框，防止误删
 * 7、 [解决] 编辑商品时，加载了规格，但没有加载规格图片
 * 8、 [优化] 添加规格、规格值会检查是否重复
 * 9、 [新增] 永远都会显示新增的规格
 * 10、[新增] 支持编辑规格值的展示方式
 * 
 */
$(function() {
	
	if ($("input[name='goods_type']:checked").val() == 1) $("#presell_set").show();
	else $("#presell_set").hide();
	
	//编辑商品时，赋值
	if(parseInt($("#goodsId").val()) > 0){
		//初始化规格图片记录数组
		if($.trim(sku_picture_array_str) != "" && $.trim(sku_picture_array_str) != undefined){
			$sku_goods_picture = eval(sku_picture_array_str);
		}

	}
	
	/**
	 * 根据选择的商品类型，查询规格属性
	 */
	$("#goodsType").change(function(){
//		goodsTypeChangeData();
		getGoodsSpecListByAttrId($(this).val());
//		removeSpecPictureBox();
		if(parseInt($(this).val()) == 0){
//			//如果没有选择商品类型，则清空属性信息
			$(".js-goods-attribute-block").hide();
			$(".js-goods-sku-attribute").html("");
		}
	});
	
	/**
	 * 规格类型
	 */
	$("input[name='sku_type']").change(function() {
		if ($("input[name='sku_type']:checked").val() == 0) {
			$('.sku_type_1').show();
			$('.sku_type_2').hide();
			$('.goods-sku-picture').hide();
		}else{
			$('.sku_type_1').hide();
			$('.sku_type_2').show();
			$('.goods-sku-picture').show();
		}
	})
	

	//***********************************选择运费方式***********************************
	$("input[name='fare']").change(function() {
		if ($("input[name='fare']:checked").val() == 1) {
			$("#commodity-weight").show();
			$("#commodity-volume").show();
			$("#valuation-method").show();
			$("#express_Company").show();
		} else {
			$("#commodity-weight").hide();
			$("#commodity-volume").hide();
			$("#valuation-method").hide();
			$("#express_Company").hide();
		}
	});
	
	
	
	//***********************************选择积分兑换***********************************
	$("input[name='integralSelect']").change(function() {
		if ($("input[name='integralSelect']:checked").val() == 1) {
			$("#integral-exchange").show();
		} else {
			$("#integral-exchange").hide();
		}
	});
	
	//***********************************选择积分兑换***********************************
	//***********************************选择预售首次加载***********************************
	var open_presell_org = $('[name="open_presell"]:checked').val();
	if(open_presell_org == 1){
		$('.presell').removeClass('hide');
		var presell_delivery_type = $('[name="presell_delivery_type"]:checked').val();
		if(presell_delivery_type == 1){
			$('#presell_time').parents('dl').removeClass('hide');
			$('#presell_day').parents('dl').addClass('hide');
		}else{
			$('#presell_day').parents('dl').removeClass('hide');
			$('#presell_time').parents('dl').addClass('hide');
		}
	}else{
		$('.presell').addClass('hide');
	}
	
	//***********************************选择预售点击事件***********************************
	$('input[name="open_presell"]').change(function(){
		
		var open_presell = $(this).val();
		if(open_presell ==1){
			$('.presell').removeClass('hide');
			
			var presell_delivery_type = $('[name="presell_delivery_type"]:checked').val();
			if(presell_delivery_type == 1){
				$('#presell_time').parents('dl').removeClass('hide');
				$('#presell_day').parents('dl').addClass('hide');
			}else{
				$('#presell_day').parents('dl').removeClass('hide');
				$('#presell_time').parents('dl').addClass('hide');
			}
		}else{
			$('.presell').addClass('hide');
			$("#presell_price").val('');
			$("#presell_time").val('');
			$("#presell_day").val('');
		}
	});
	
	$('[name="presell_delivery_type"]').change(function(){
		var presell_delivery_type = $(this).val();
		if(presell_delivery_type == 1){
			$('#presell_time').parents('dl').removeClass('hide');
			$('#presell_day').parents('dl').addClass('hide');
			$('#presell_day').val('');
		}else{
			$('#presell_day').parents('dl').removeClass('hide');
			$('#presell_time').parents('dl').addClass('hide');
			$('#presell_time').val('');
		}
	});
	//***********************************选择预售***********************************
	
	/**
	 * 循环处理价格 不让价格为空
	 */
	$('input[name="sku_price"],input[name="market_price"],input[name="cost_price"],input[name="stock_num"],input[name="code"]').live('blur',function() {
		var $this = $(this);
		var reg = /^\d+(.{0,1})\d{0,2}$/;
		if($this.attr("name") == "sku_price" || $this.attr("name") == "market_price" || $this.attr("name") == "cost_price" || $this.attr("name") == "stock_num"){
			if($this.val().length>0){
				if(reg.test($this.val())){
					if ($this.val().replace(/(^\s*)|(\s*$)/g, "") == "") {
						if($this.attr("name") == "stock_num"){
							$this.val("0");
						}else{
							$this.val("0.00");
						}
						$this.parent().find(".help-inline").css("display","block");
					} else {
						num = parseInt($this.val());
						$this.css("border-color", "");
						$this.parent().find(".help-inline").hide();
					}
					switch($this.attr("name")){
					case "sku_price":
						eachPrice();
						break;
					case "market_price":
						eachMarketPrice();
						break;
					case "cost_price":
						eachCostPrice();
						break;
					case "stock_num":
						eachInput();
						break;
					}
				}else{
					if($this.attr("name") == "stock_num"){
						$this.val("0");
					}else{
						$this.val("0.00");
					}
				}
			}else{
				if($this.attr("name") == "stock_num"){
					$this.val("0");
				}else{
					$this.val("0.00");
				}
			}
		}else{
			//商家编码处理
			if($this.val().length>20) $this.val($this.val().substr(0,20));
			eachMerchantCode();
		}
	});
	
	/**
	 * 离开焦点事件也要进行处理
	 */
	$('input[name="sku_price"],input[name="market_price"],input[name="cost_price"],input[name="stock_num"],input[name="code"]').live("blur",function(){
		$(this).keyup();
	});
	
	/**
	 * 循环 处理库存
	 */
	$('input[name="stock_num"]').live('keyup', function() {
		$stock = $(this);
		if ($stock.val().replace(/(^\s*)|(\s*$)/g, "") == "") {
			$stock.parent().find(".help-inline").show();
		} else {
			$stock.css("border-color", "");
			$stock.parent().find(".help-inline").hide();
		}
		eachInput();
	});

	$(".brick.small").live('mouseover', function() {
		$(this).children().next().show();
	}).live("mouseout", function() {
		$(this).children().next().hide();
	});
	
	//***********************************选择商品分组***********************************
	$("#area-select,#procategory").on("mouseover", function() {
		$("#procategory").show();
	});

	$("#area-select,#procategory").on("mouseout", function() {
		$("#procategory").hide();
	});

	$(".input-checked").each(function(index, element) {
		if ($(this).prop("checked")) {
			$("#productcategory-selected").append("<span class='label'>" + $(this).val() + "<i class='categoryclose'></i></span>");
		}
	});
	
	$(".input-checked").live("change",function() {
		var $this = $(this);
		if ($this.prop("checked")) {
			$("#productcategory-selected").append("<span class='label' id=" + $(this).attr("id") + ">" + $this.val() + "<i class='categoryclose'></i></span>");
		} else {
			$("#productcategory-selected span").each(function() {
				if ($this.val() == $(this).text()) {
					$(this).remove();
				}
			});
		}
	});
	
	$("#productcategory-selected").delegate(".categoryclose","click",function() {
		var $this = $(this);
		$(this).parentsUntil("#productcategory-selected").remove();
		/*$("#procategory li").each(function(index, element) {
			if ($this.parent().text() == $(this).find(".input-checked").val()) {
				$(this).find(".input-checked").prop("checked",false);
			}
		});*/
		//移除分组本标签,并还原本分组
		var group_id = $this.parent().attr("id");
		var group_name = $this.parent().attr("group_name");
		$(".goods-gruop-select").append("<option value='"+ group_id +"'>"+ group_name +"</option>");
	});
	//***********************************选择商品分组***********************************
	
	$('.edit-sku-popup-body aside .attribute-item .item-name').click(function(){
		
		obj = $(this).parent().children('.original-sku-list');
		if(obj.css("display") == 'none' ){
			$(this).children('span').removeClass('close-rotate');
			obj.show();
		}else{
			$(this).children('span').addClass('close-rotate');
			obj.hide();
		}
		
	})
	
	/**
	 * 选择类目、扩展类目
	 */
	$("#tbcNameCategory,#tbcExtendNameCategory").live("click",function(){
		var goodsid = $(this).attr("data-goods-id");
		var category_id = $(this).attr("cid");
		var flag = $(this).attr("data-flag");
		OpenCategoryDialog(ADMINMAIN,category_id,goodsid,flag);
	});
	
	/**
	 * 页面导航
	 */
	$("#fixedNavBar li").click(function(){
		var obj = "."+$(this).attr("data-floor");
		var top = $(obj).offset().top;
		$("html, body").animate({ scrollTop: top }, {duration: 500,easing: "swing"});
	});

	/**
	 * 商品图片：从图片空间选择
	 */
	$('#img_box').live('click',function(e){
		var js_img = $(this).attr("js-img");
		shopImageFlag = js_img;//所点击的商品图片标识
		speciFicationsFlag = 0;
		OpenPricureDialog("PopPicure", ADMINMAIN, 0, 1,0,0,"goods");
		
	});
	
	/**
	 * 规格图片，从图片空间选择
	 */
	$('#sku_img_box').live('click',function(e){
		var js_img = $(this).attr("js-img");
		var spec_id = $(this).attr("spec_id");
		var spec_value_id = $(this).attr("spec_value_id");
		shopImageFlag = js_img;//所点击的商品图片标识
		speciFicationsFlag = 0;
		OpenPricureDialog("PopPicure", ADMINMAIN, 0, 2, spec_id, spec_value_id);
	});
	
	//点击选择商品类型
	$(".goods_type_select .goods_type_item").click(function(){
		if($(this).attr('disabled')) return;
		$(".goods_type_select .goods_type_item").removeClass("selected");
		$(this).addClass("selected");
		var goods_type = $(this).attr("data-goods-type");
		
		$('[name="sku_type"][value="0"]').trigger('click');
		$('[name="sku_type"][value="1"]').parents('dl').hide();
		
		$(".js-virtual-goods-type-block").hide(); //虚拟商品隐藏
		$(".js-virtual-goods-cloud-download-resources").hide(); //网盘下载专属值
		$(".js-virtual-goods-point-card-inventory").hide(); //点卡专属值
		$(".js-virtual-goods-site-directly-download-resources").hide(); //网站下载专属值
		
		// 实物商品
		if(goods_type == 1){
			$('#txtProductCount').parents('dl').show();
			$("[data-c='block-goods-sku']").show().next().show();
			$("#presell_set").show();
			$(".goods_type_select .goods_type_item input[name='virtual_goods_type']").prop("checked",false);
			$('[name="sku_type"][value="1"]').parents('dl').show();
		}else if(goods_type == 0){
			
		//虚拟商品
			$("[data-c='block-goods-sku']").hide();
			$(".js-virtual-goods-type-block").show();
			$("#presell_set").hide();
			//选中虚拟商品类型
			$(".goods_type_select .goods_type_item input[name='virtual_goods_type']").prop("checked",false);
			$(this).find("[name='virtual_goods_type']").prop("checked", true);
			var v = $(this).find("[name='virtual_goods_type']").val() * 1;
			switch(v){
				case 1:
					//网上服务
					$(".js-virtual-goods-cloud-download-resources").hide();
					$(".js-virtual-goods-point-card-inventory").hide();
					$(".js-virtual-goods-site-directly-download-resources").hide();
					$(".js-confine_use_number").text("1次");
				break;
				case 2:
					//网盘下载
					$(".js-virtual-goods-cloud-download-resources").show();
					$(".js-virtual-goods-point-card-inventory").hide();
					$(".js-virtual-goods-site-directly-download-resources").hide();
					$("#confine_use_number").val(0).attr("disabled","disabled");
					$(".js-confine_use_number").text("不限制");
				break;
				case 3:
					//点卡
					$(".js-virtual-goods-cloud-download-resources").hide();
					$(".js-virtual-goods-point-card-inventory").show();
					$(".js-virtual-goods-site-directly-download-resources").hide();
					$("#confine_use_number").val(1).attr("disabled","disabled");
					$(".js-confine_use_number").text("1次");
					$('#txtProductCount').parents('dl').hide();
				break;
				case 4:
					//网站直接下载
					$(".js-virtual-goods-cloud-download-resources").hide();
					$(".js-virtual-goods-point-card-inventory").hide();
					$(".js-virtual-goods-site-directly-download-resources").show();
					$("#confine_use_number").val(0).attr("disabled","disabled");
					$(".js-confine_use_number").text("不限制");
				break;
			}
		}
	})

	/**
	 * 实物类别选择
	 */
	$("input[name='goods_type']").live("click",function(){
		if($(this).val() == 0){
			// $("[data-c='block-goods-sku']").hide().next().hide();
			$("[data-c='block-goods-sku']").hide();
			$(".js-virtual-goods-type-block").show();
			$("#presell_set").hide();
		}else{
			$('#txtProductCount').parents('dl').show();
			$("[data-c='block-goods-sku']").show().next().show();
			$(".js-virtual-goods-type-block").hide();
			$("#presell_set").show();
		}
	});
	
	//商品品牌搜索
	$(".searchable-select-input").live("keyup",function(){
		if($(this).val().length>100){
			showTip("查询限制在100个字符以内","warning");
			return;
		}
		if($(this).attr("data-value") != $(this).val()){
			$(this).attr("data-value",$(this).val());
			getGoodsBrandList($(".searchable-select-holder").text(),$(this).val());
		}
	});

	//选择虚拟商品类型
	$("input[name='virtual_goods_type']").click(function(){
		var v = parseInt($(this).val());
		$('#txtProductCount').parents('dl').show();
		switch(v){
		case 1:
			//网上服务
			$(".js-virtual-goods-cloud-download-resources").hide();
			$(".js-virtual-goods-point-card-inventory").hide();
			$(".js-virtual-goods-site-directly-download-resources").hide();
			$(".js-confine_use_number").text("1次");
			break;
		case 2:
			//网盘下载
			$(".js-virtual-goods-cloud-download-resources").show();
			$(".js-virtual-goods-point-card-inventory").hide();
			$(".js-virtual-goods-site-directly-download-resources").hide();
			$("#confine_use_number").val(0).attr("disabled","disabled");
			$(".js-confine_use_number").text("不限制");
			break;
		case 3:
			//点卡
			$(".js-virtual-goods-cloud-download-resources").hide();
			$(".js-virtual-goods-point-card-inventory").show();
			$(".js-virtual-goods-site-directly-download-resources").hide();
			$("#confine_use_number").val(1).attr("disabled","disabled");
			$(".js-confine_use_number").text("1次");
			$('#txtProductCount').parents('dl').hide();
			break;
		case 4:
			//网站直接下载
			$(".js-virtual-goods-cloud-download-resources").hide();
			$(".js-virtual-goods-point-card-inventory").hide();
			$(".js-virtual-goods-site-directly-download-resources").show();
			$("#confine_use_number").val(0).attr("disabled","disabled");
			$(".js-confine_use_number").text("不限制");
			break;
		}
	});

	//切换tab
	$(".goods-nav ul li").click(function(){
//		if($(this).attr("data-c")=="block-basic-setting") $(".h50").show();
//		else  $(".h50").hide();
		$("."+$(this).attr("data-c")).show().siblings("[class^='block-']").hide();
		$(this).addClass("selected").siblings().removeClass("selected");
		resizeBtnSubmit();
	});

	sku_spec_sel_obj = null;
	//打开编辑商品规格弹出框
	edit_sku_popup = function(e){

		$(".edit-sku-popup header h3").text("选择规格");
		$(".edit-sku-popup-mask-layer").fadeIn();
		$(".edit-sku-popup-body").show();
		$(".edit-sku-popup").fadeIn();
		
		sku_spec_sel_obj = $(e).parents('.spec-item');
		if(typeof(sku_spec_sel_obj) != 'undefined'){
			
			spec_id = sku_spec_sel_obj.find('[name="spec_name"]').attr('spec_id');
			t_obj = $(`.original-sku-list li[data-spec-id="${spec_id}"]`).trigger('click');
			sku_spec_sel_obj.find('.spec-value-item').each(function(){
				v = $(this).find('[name="spec_value"]').attr('spec_value_id');
				$(`.edit-sku-popup .sku-value ul li[data-spec-value-id="${v}"]`).addClass('selected');
			})
		}
		
	};

	
	//关闭商品规格弹出框
	$(".edit-sku-popup>header>span,.edit-sku-popup footer .btn-bottom").click(function(){
		$(".edit-sku-popup-mask-layer").fadeOut();
		$(".edit-sku-popup").fadeOut();
	});
	
	//规格生成
	sku_popup_spec_generate = function(){
		
		get_obj = $('.original-sku-list li.selected,curr');
		set_obj = sku_spec_sel_obj.find('[name="spec_name"]'); 
		set_obj.val(get_obj.attr('data-spec-name'));
		set_obj.attr('spec_id', get_obj.attr('data-spec-id'));
		
		let sel_value_arr = new Array();
		sku_spec_sel_obj.find('.spec-content').html('');
		spec_show_type = sku_spec_sel_obj.find('[name="spec_value_subsidiary"]:checked').val();
		$('.sku-value-list ul li.selected').each(function(){
			
			v = $(this).attr('data-spec-value-name');
			id = $(this).attr('data-spec-value-id');
			sku_spec_sel_obj.find('.spec-value-item');
			h = `<div class="spec-value-item" title ="${v}">`;
			if(spec_show_type == 2){
		 		h += `<div class="value-item-affiliate upload-btn-common"> <div class="affiliate_img"><img src="${ADMINIMG}/spec_value_item_affiliate.png" alt=""></div></div>`;
		 	}else if(spec_show_type == 3){
		 		h += `<div class ="value-item-affiliate"><input type="color" class="input-common-color" value="#000000"></div>`;
		 	}
			h += `<div class="input_div"><span>${v}</span><input type="text" value = "${v}" name = "spec_value"  spec_value_id = "${id}"/></div>
			 		<i onclick = "spec_del(this, 'spec-value-item')" sku-data-generate>×</i>
			 	</div>`;
			 	sku_spec_sel_obj.find('.spec-content').append(h);
		})
		
		//规格表格生成
		sku_table_generate();
		
		//关闭弹出框
		$(".edit-sku-popup footer .btn-bottom").click();
	}
	
	$("#video_url").blur(function(){
		if($(this).val().length>0){
			var video = "my-video";
			var myPlayer = videojs(video);
			var value = $(this).val();

			videojs(video).ready(function(){
				var myPlayer = this;
				myPlayer.src(value);
				myPlayer.load(value);
				myPlayer.play();
				setTimeout(function(){

					if(!$(".video-thumb .vjs-error-display").hasClass("vjs-hidden")){

						$("#video_url").val("");//video.js Line:7873
						showTip("媒体不能加载，要么是因为服务器或网络失败，要么是因为格式不受支持。","error");

					}

				},1000);
			});
		}
		
	});
	
	//批量修改
	$(".volume-set-sku-info a").click(function(){
		
		var tag = $(this).attr("data-tag");
		var placeholder = $(this).text();
		$(this).parent().children("a").hide();
		$(this).siblings("input,button").show();
		$(this).siblings("input").attr("placeholder",placeholder).attr("data-tag",tag).focus();
	});
	
	//回车事件
	$(".volume-set-sku-info input").keyup(function(event){
		if(event.keyCode == 13){
			$(".volume-set-sku-info .btn-common").click();
		}
	});
	
	//批量操作，确定按钮
	$(".volume-set-sku-info .btn-common").click(function(){

		var input = $(this).prev();
		var tag = input.attr("data-tag");
		var placeholder = input.attr("placeholder");
		var v = input.val();

		//批量修改规格信息
		var price_regex = /^\d+(.{0,1})\d{0,2}$/;//价格正则表达式
		var stock_regex = /^\d+$/;//库存正则表达式
		var is_update = true;//是否更新
		
		if(v.length>0){
			if(tag == "stock-num"){
				//验证库存
				if(!stock_regex.test(v)){
					showTip(placeholder + "格式错误" ,"warning");
					is_update = false;
					input.focus();
				}
			}else{
				//验证价格输入是否正确
				if(!price_regex.test(v)){
					showTip(placeholder + "格式错误","warning");
					input.focus();
					is_update = false;
				}
			}
			
			if(is_update){
				
				//更新价格、库存
				if(tag == "sku-price"){
					$(".block-goods-sku>.goods-sku-list .input-common[name='sku_price']").val(v);
				}else if(tag == "market-price"){
					$(".block-goods-sku>.goods-sku-list .input-common[name='market_price']").val(v);
				}else if(tag == "cost-price"){
					$(".block-goods-sku>.goods-sku-list .input-common[name='cost_price']").val(v);
				}else if(tag == "stock-num"){
					$(".block-goods-sku>.goods-sku-list .input-common[name='stock_num']").val(v);
				}else if(tag == "volume"){
					$(".block-goods-sku>.goods-sku-list .input-common[name='volume']").val(v);
				}else if(tag == "weight"){
					$(".block-goods-sku>.goods-sku-list .input-common[name='weight']").val(v);
				}
				
				$(".goods-sku-list tbody tr td input").change();
				
				//循环计算商品数据
				eachPrice();
				eachMarketPrice();
				eachCostPrice();
				eachInput();
				
				input.val("");
				$(".volume-set-sku-info .btn-common-cancle").click();
			}
		}else{
			input.focus();
			showTip("请输入" + placeholder,"warning");
		}
		
	});
	
	//批量操作，取消按钮
	$(".volume-set-sku-info .btn-common-cancle").click(function(){
		$(this).parent().children("a").show();
		$(this).parent().children("input,button").hide();
		$(this).parent().children("input").removeAttr("data-tag").removeAttr("placeholder");
	});

	/**
	 * 选择规格值的展示方式
	 * 创建时间：2018年4月11日11:07:46
	 */
	$(".edit-sku-popup-body article>div.sku-display-mode nav ul li").click(function(){

		var curr_sku = $(".edit-sku-popup-body aside .current-selected-sku ul.sku-value-list ul  li.curr.selected");
		var show_type = curr_sku.attr("data-show-type");
		var spec_value = eval(curr_sku.attr("data-spec-value-json"));
		var select_show_type = $(this).attr("data-spec-show-type");
		$(this).addClass("selected").siblings().removeClass("selected");
		$(".edit-sku-popup-body article>div.sku-display-mode>ul li").removeClass("selected");
		
		$(".edit-sku-popup-body article>div.sku-display-mode>ul li[data-spec-show-type='" + select_show_type + "']:visible").addClass("selected");
		$(".edit-sku-popup-body article>div.sku-display-mode nav ul li .radio-common").removeClass("selected").children("input").removeAttr("checked");
		$(this).find(".radio-common").addClass("selected").children("input").attr("checked","checked");

		//修改当前选中的规格的展示类型，同时触发事件，重新加载规格值
		if(show_type != select_show_type){
			$(".edit-sku-popup-body aside .current-selected-sku ul.sku-value-list ul  li.selected.curr").attr("data-show-type",select_show_type).click();
			//由文字或者颜色展示方式改为图片展示方式的时候，给予标识，后续用于判断是否上传了图片 
			if(select_show_type == 3 &&　curr_sku.attr("data-is-update-img") == undefined) curr_sku.attr("data-is-update-img",0);
		}

		//阻止后续事件触发
		return false;
	});
	
	/**
	 * 二次修改规格值的颜色
	 * 创建时间：2018年4月11日16:07:57
	 */
	$(".edit-sku-popup-body article>div.sku-display-mode>ul li input[type='color']").live("change",function(){
		var curr_sku = $(".edit-sku-popup-body aside .current-selected-sku ul.sku-value-list ul  li.curr.selected")
		
		var spec_value = eval(curr_sku.attr("data-spec-value-json"));
		if(spec_value != null){
//			$(".edit-sku-popup-body article>div.sku-display-mode nav ul li[data-spec-show-type=2]").click();，颜色不影响，可以不用
			for(var i=0;i<spec_value.length;i++){
				if($(this).attr("data-spec-value-id") == spec_value[i].spec_value_id) spec_value[i].spec_value_data = $(this).val();
			}
			curr_sku.attr("data-spec-value-json",JSON.stringify(spec_value)).click();
		}
	});
	
	/**
	 * 删除规格值
	 */
	$(".edit-sku-popup-body article>div.sku-value ul li .shut").live("click",function(){
		
		var $this = $(this);
		if($(".sku-value-list ul  li.selected.curr").length == 0){
			showTip("原始规格请到商品规格中删除","warning");
			return;
		}
		$( "#dialog").dialog({
			buttons: {
				"确定": function() {
					var curr_li = $(".sku-value-list ul  li.selected.curr");
					var spec_value = eval(curr_li.attr("data-spec-value-json"));
					var spec_value_id = $this.parent().attr("data-spec-value-id");
					
					//触发取消选择规格值事件，动态改变父级规格的属性
					$this.parent().addClass("selected").click();

					//找到当前的规格值，并且移除
					for(var i=0;i<spec_value.length;i++) if(spec_value[i].spec_value_id == spec_value_id) spec_value.splice(i,1);
					
					curr_li.attr("data-spec-value-json",JSON.stringify(spec_value)).attr("data-spec-value-length",spec_value.length).click();
					
					$(this).dialog('close');
				},
				"取消,#f5f5f5,#666": function() {
					$(this).dialog('close');
				},
			},
			contentText:"确定要删除此规格值吗？",
		});
		
		//阻止后续事件触发
		return false;
	});
	
	/**
	 * 编辑商品规格值
	 * 创建时间：2018年4月14日09:21:25
	 */
	$(".edit-sku-popup-body article>div.sku-display-mode>ul li[data-spec-show-type='1'] strong").live("click",function(){
		
		var curr_li = $(this).parent();
		
		if(curr_li.children("input").length==0){
			var html = '<input type="text" class="input-common middle" placeholder="规格值名称(回车保存)" value="' + $(this).prev().text() + '" />';
			curr_li.append(html);
		}
		if($(this).text() == "编辑"){
			$(this).text("取消").prev().hide().next().next().show().focus();
		}else{
			$(this).text("编辑").prev().show().next().next().hide();
		}
	});
	
	/**
	 * 输入要编辑的商品规格值
	 * 创建时间：2018年4月14日09:37:062
	 */
	$(".edit-sku-popup-body article>div.sku-display-mode>ul li[data-spec-show-type='1'] input").live("keyup",function(event){
		if(event.keyCode == 13){
			var v = $(this).val();
			var curr_value_li = $(this).parent();
			var spec_value_id = curr_value_li.attr("data-spec-value-id");
			
			if(v == ""){
				showTip("请输入规格值","warning");
				$(this).focus();
				return;
			}

			if(!vertifyStr(v)){
				showTip("规格值不能包含特殊字符", "error");
				return;
			}

			var space = new RegExp(" ","g");
			v = v.replace(space, "&nbsp;");
			
			var is_exist = false;
			$(".edit-sku-popup-body article>div.sku-value ul li[data-spec-value-id]").each(function(){
				//排除自己的规格值
				if($(this).attr("data-spec-value-id") != spec_value_id){
					if($(this).attr("data-spec-value-name") == v){
						is_exist = true;
						return false;
					}
				}
			});

			if(is_exist){
				showTip("规格值已存在，请修改名称","warning");
				return;
			}
			
			var curr_li = $(".sku-value-list ul  li.selected.curr");
			var spec_value = eval(curr_li.attr("data-spec-value-json"));
			for(var i=0;i<spec_value.length;i++){
				if(spec_value[i].spec_value_id == spec_value_id){
					spec_value[i].spec_value_name = v;
					break;
				}
			}
			
			curr_li.attr("data-spec-value-json",JSON.stringify(spec_value)).click();
			
			$(this).parent().children("span").text(v);
			$(this).hide().prev().text("编辑").prev().show();
		}
	});
});

/**
 * 根据商品类型id，查询商品规格信息
 * @param attr_id 规格属性id
 */ 
function getGoodsSpecListByAttrId(attr_id,callBack){
	if(!isNaN(attr_id) && attr_id > 0){
		$.ajax({
			url : __URL(ADMINMAIN+"/goods/getGoodsSpecListByAttrId"),
			type : "post",
			data : { "attr_id" : parseInt(attr_id)},
			success : function(res){
				if(res !=-1){
					var sku_list_html = "";//规格弹出框列表
					var spec_length = res.spec_list.length;
					var attribute_length = res.attribute_list.length;
					//商品属性集合
					if(attribute_length>0){
						var html ="";
						for(var i=0;i<attribute_length;i++){
							var curr = res.attribute_list[i];
							if($.trim(curr.value_items) == "" && parseInt(curr.type) !=1) continue;
							if($.trim(curr.attr_value_name) != ""){
							
							html += '<tr style="padding-top:15px;padding-bottom:15px;">';
								html += '<td width="10%" style="border:1px solid #E9E9E9;"align="right" class="txt12" data-value="'+curr.attr_value_name+'">'+curr.attr_value_name+'</td>';
								html += '<td width="80%" style="border:1px solid #E9E9E9;">';
									switch(parseInt(curr.type)){
										case 1:
											//输入框
											html += '<input type="text" class="js-attribute-text input-common" id="input-text-'+curr.attr_value_id+'-'+curr.attr_value_id+'"data-attribute-value-id="'+curr.attr_value_id+'" data-attribute-value="'+curr.attr_value_name+'" data-attribute-sort="'+curr.sort+'"/>';
											break;
										case 2:
											//单选框
											for(var j=0;j<curr.value_items.length;j++){
												var value = curr.value_items[j];
												if($.trim(value) != ""){
													html += '<div class="goods-sku-attribute-item-radio">';
														html += '<i class="radio-common"><input type="radio" value="'+value+'" class="js-attribute-radio" id="radio_value_item'+curr.attr_value_id+'-'+j+'" data-attribute-value-id="'+curr.attr_value_id+'" data-attribute-value="'+curr.attr_value_name+'"  name="radio_value'+i+'" data-attribute-sort="'+curr.sort+'"/></i>&nbsp;';
														html += '<label for="radio_value_item'+curr.attr_value_id+'-'+j+'">'+value+'</label>';
													html += '</div>';
												}
											}
											break;
										case 3:
											//复选框
											for(var j=0;j<curr.value_items.length;j++){
												var value = curr.value_items[j];
												if($.trim(value) != ""){
													html += '<div class="goods-sku-attribute-item-checkbox">';
														html += '<i class="checkbox-common"><input type="checkbox" value="'+value+'" class="js-attribute-checkbox" id="checkbox_value_item'+curr.attr_value_id+'-'+j+'" data-attribute-value-id="'+curr.attr_value_id+'" data-attribute-value="'+curr.attr_value_name+'"  name="checkbox_value_item'+i+'" data-attribute-sort="'+curr.sort+'"/></i>&nbsp;';
														html += '<label for="checkbox_value_item'+curr.attr_value_id+'-'+j+'">'+value+'</label>';
													html += '</div>';
												}
											}
											break;
									}
								html += '</td>';
							html += '</tr>';
							}
						}
						$(".js-goods-sku-attribute").html(html);
					}
					if(callBack != undefined) callBack();
					
					$(".js-goods-attribute-block").show();
				}
			}
		});
	}
}

//验证
function ValidateUserInput() {
	//基础设置
	
	// 商品标题
	if (!IsEmpty("#txtProductTitle")) {
		$(".goods-nav ul li:eq(0)").click();
		$("#txtProductTitle").next("span").text("请填写商品名称").show();
		$("#txtProductTitle").focus();
		return false;
	}else if($("#txtProductTitle").val().length>60){
		$(".goods-nav ul li:eq(0)").click();
		$("#txtProductTitle").next("span").text("商品标题不能大于60个字").show();
		$("#txtProductTitle").focus();
		return false;
	} else {
		$("#txtProductTitle").next("span").hide();
	}
	
	//商品分类
	if($("#tbcNameCategory").attr("cid") == undefined || $("#tbcNameCategory").attr("cid")==""){
		$(".goods-nav ul li:eq(0)").click();
		$("#tbcNameCategory .help-inline").show();
		$('html,body').animate({scrollTop : 0 }, 200);
		return false;
	}else{
		$("#tbcNameCategory .help-inline").hide();
	}
	
	//扩展分类
	var extend_name_category_flag = false;
	$(".extend-name-category").each(function() {
		if(!($(this).attr("cid") > 0)){
			extend_name_category_flag = true;
			$(this).find(".help-inline").show();
			return false;
		}else{
			$(this).find(".help-inline").hide();
		}
	});
	
	if(extend_name_category_flag){
		$(".goods-nav ul li:eq(0)").click();
		return false;
	}
	
	//实物类型，选择虚拟商品，要选择虚拟商品类型
	if($(".goods_type_select .goods_type_item.selected").attr("data-goods-type") == 0){
		
		if($("#validity_period").val().length>0){
			if(isNaN($("#validity_period").val())){
				$(".goods-nav ul li:eq(0)").click();
				showTip("有效期格式错误","warning");
				$("#validity_period").focus();
				return false;
			}
		}
		
		//遍历虚拟商品分组，验证
		var v = parseInt($("input[name='virtual_goods_type']:checked").val());
		if(v == 2){
			if($("#cloud_address").val().length == 0){
				showTip("请输入网盘地址","warning");
				$("#cloud_address").focus();
				$(".goods-nav ul li:eq(0)").click();
				return false;
			}
		}else if(v == 3){
			
		}else if(v == 4){
			if($("#download_resources").val().length == 0){
				showTip("请上传文件","warning");
				$(".goods-nav ul li:eq(0)").click();
				return false;
			}
		}
	}

	// 商品促销语
	if($("#txtIntroduction").val().length>100){
		$(".goods-nav ul li:eq(0)").click();
		$("#txtIntroduction").focus();
		$("#txtIntroduction").next("span").show();
		return false;
	} else{
		$("#txtIntroduction").next("span").hide();
	}
	
	//关键词
	if($("#txtKeyWords").val().length>40){
		$(".goods-nav ul li:eq(0)").click();
		$("#txtKeyWords").focus();
		$("#txtKeyWords").next("span").show();
		return false;
	}else{
		$("#txtKeyWords").next("span").hide();
	}
	
	//商品单位
//	if($("#goodsUnit").val().length == 0 || $("#goodsUnit").val().length>10){
//		$(".goods-nav ul li:eq(0)").click();
//		$("#goodsUnit").focus();
//		$("#goodsUnit").next("span").show();
//		return false;
//	}else{
//		$("#goodsUnit").next("span").hide();
//	}
	
	//商家编码
	if($("#txtProductCodeA").val().length>40){
		$(".goods-nav ul li:eq(0)").click();
		$("#txtProductCodeA").focus();
		$("#txtProductCodeA").next("span").show();
		return false;
	}else{
		$("#txtProductCodeA").next("span").hide();
	}

	//销售价格
	if (!IsNum("#txtProductSalePrice") || parseFloat($("#txtProductSalePrice").val()) < 0) {
		$(".goods-nav ul li:eq(0)").click();
		$("#txtProductSalePrice").nextAll("span:last").text("商品销售价不能为空，且不能为负数").show();
		$("#txtProductSalePrice").focus();
		return false;
	} else {
		$("#txtProductSalePrice").nextAll("span:last").hide();
	}

	//保质期天数
	if($("#shelf_life").val().length>0){
		if(!IsPositiveNum("#shelf_life")){
			$(".goods-nav ul li:eq(0)").click();
			$("#shelf_life").nextAll("span:last").show();
			$("#shelf_life").focus();
			return false;
		}else{
			$("#shelf_life").nextAll("span:last").hide();
		}
	}
	
	// 总库存
	if (!IsPositiveNum("#txtProductCount")) {
		$(".goods-nav ul li:eq(0)").click();
		$("#txtProductCount").nextAll("span:last").show();
		$("#txtProductCount").focus();
		return false;
	} else {
		$("#txtProductCount").nextAll("span:last").hide();
	}
	
	if (parseInt($("#txtProductCount").val()) < 0) {
		$(".goods-nav ul li:eq(0)").click();
		$("#txtProductCount").nextAll("span:last").show();
		$("#txtProductCount").focus();
		return false;
	} else {
		$("#txtProductCount").nextAll("span:last").hide();
	}
	
	// 库存预警
	if (!IsPositiveNum("#txtMinStockLaram")) {
		$(".goods-nav ul li:eq(0)").click();
		$("#txtMinStockLaram").nextAll("span:last").show();
		$("#txtMinStockLaram").focus();
		return false;
	} else {
		$("#txtMinStockLaram").nextAll("span:last").hide();
	}

	if (parseInt($("#txtMinStockLaram").val()) < 0) {
		$(".goods-nav ul li:eq(0)").click();
		$("#txtMinStockLaram").nextAll("span:last").show();
		$("#txtMinStockLaram").focus();
		return false;
	} else {
		$("#txtMinStockLaram").nextAll("span:last").hide();
	}
	
	var reg_integral = /^\+?[1-9][0-9]*$/;
	//如果是积分商品，则必须设置积分
	if($("input[name='integralSelect']:checked").val() > 0){
		if($("#integration_available_use").val()=="" || $("#integration_available_use").val()==0){
			$(".goods-nav ul li:eq(0)").click();
			$("#integration_available_use").nextAll("span:last").text("请设置兑换所需积分").show();
			return false;
		}else if(!reg_integral.test($("#integration_available_use").val())){
			$(".goods-nav ul li:eq(0)").click();
			$("#integration_available_use").nextAll("span:last").text("积分必须为整数").show();
			return false;
		}else{
			$("#integration_available_use").nextAll("span:last").hide();
		}
	}

	if($("input[name='integral_give_type']:checked").val() == 0){
		if($("#integration_available_give_ratio").val() < 0){
			showTip("赠送积分不可为负数", "warning");
			return false;
		}
	}else{
		if($("#integration_available_give_ratio").val() < 0 || $("#integration_available_give_ratio").val() > 100){
			showTip("积分比率需在0-100之间", "warning");
			return false;
		}
	}

	//阶梯优惠
	var is_error = false;
	var ladder_arr = new Array();
	var min_price = $("#txtProductSalePrice").val() * 100; //最低价格
	$(".ladder_preference").each(function(){
		var ladder = $(this).find(".ladder").val();
		var preference = parseFloat($(this).find(".preference").val()).toFixed(2) * 100;
		var $this = $(this);
		if(ladder > 1){
			if($.inArray(ladder, ladder_arr) > -1){
				$(".goods-nav ul li:eq(0)").click();
				showTip("该优惠等级已存在","error");
				$this.find(".ladder").addClass("input-error");
				is_error = true;
				return false;
			}else{
				is_error = false;
				$(".ladder").removeClass("input-error");
			}
			ladder_arr.push(ladder);
		}else{
			$(".goods-nav ul li:eq(0)").click();
			showTip("阶梯优惠商品件数不能为少于两件","error");
			$this.find(".ladder").addClass("input-error");
			is_error = true;
			return false;
		}

		if(preference >= min_price){
			$(".goods-nav ul li:eq(0)").click();
			showTip("优惠价格不能大于或等于商品最小价格","error");
			$this.find(".preference").addClass("input-error");
			is_error = true;
			return false;
		}else if(preference < 0){
			$(".goods-nav ul li:eq(0)").click();
			showTip("优惠价格不可为负数","error");
			$this.find(".preference").addClass("input-error");
			is_error = true;
			return false;
		}else if(preference == 0){
			$(".goods-nav ul li:eq(0)").click();
			showTip("优惠价格不可为0","error");
			$this.find(".preference").addClass("input-error");
			is_error = true;
			return false;
		}else{
			is_error = false;
			$(".preference").removeClass("input-error");
		}
	});
	
	// 运费设置
	if ($("input[name='fare']:checked").val() == 1) {
		if($("input[name='shipping_fee_type']:checked").val() == 2){
			var goods_volume = parseFloat($("#goods_volume").val()).toFixed(2);
			if(goods_volume == '' || goods_volume <= 0){
				$(".goods-nav ul li:eq(0)").click();
				$("#goods_volume").focus();
				$("#goods_volume").nextAll("span:last").show();
				$("#goods_weight").nextAll("span:last").hide();
				return false;
			}else{
				$("#goods_volume").nextAll("span:last").hide();
			}
		}else if($("input[name='shipping_fee_type']:checked").val() == 1){
			var goods_weight = parseFloat($("#goods_weight").val()).toFixed(2);
			if(goods_weight == '' || goods_weight <= 0){
				$(".goods-nav ul li:eq(0)").click();
				$("#goods_weight").focus();
				$("#goods_weight").nextAll("span:last").show();
				$("#goods_volume").nextAll("span:last").hide();
				return false;
			}else{
				$("#goods_weight").nextAll("span:last").hide();
			}
		}
	}

	//最小购买数限制
	if(!(parseInt($("#PurchaseSum").val()) >= parseInt($("#minBuy").val())) && (parseInt($("#PurchaseSum").val()) > 0)){
		$(".goods-nav ul li:eq(0)").click();
		$("#minBuy").nextAll("span:last").text("限购数不为0时,最小购买数必须小于等于限购数量").show();
		return false;
	}else{
		$("#minBuy").nextAll("span:last").hide();
	}
	
	//最少购买数
	if ($("#minBuy").val() < 0) {
		$(".goods-nav ul li:eq(0)").click();
		$("#minBuy").nextAll("span:last").show();
		$("#minBuy").focus();
		return false;
	} else {
		$("#minBuy").nextAll("span:last").hide();
	}

	if($(".upload_img_id").length == 0){
		$(".goods-nav ul li:eq(2)").click();
		$(".img-error").text("最少需要一张图片作为商品主图").show();
		return false;
	}else{
		$(".img-error").hide();
	}

	// 商品描述
	var description = UE.getEditor('editor').getContent();

	description = description.replace(/(\n)/g, "");
	description = description.replace(/(\t)/g, "");
	description = description.replace(/(\r)/g, "");
	description = description.replace(/\s*/g, "");
	if (description == "") {
		$(".goods-nav ul li:eq(3)").click();
		showTip("商品描述不能为空","warning");
		$("#tareaProductDiscrip").nextAll("span:last").text("商品描述不能为空").show();
		$("body").scrollTop($("#discripContainer").offset().top-100);
		return false;
	} else if (description.length < 5 || description.length > 25000) {
		$(".goods-nav ul li:eq(3)").click();
		showTip("商品描述字符数应在5～25000之间","warning");
		$("#tareaProductDiscrip").nextAll("span:last").text("商品描述字符数应在5～25000之间").show();
		$("body").scrollTop($("#discripContainer").offset().top-100);
		return false;
	} else {
		$("#tareaProductDiscrip").nextAll("span:last").hide();
	}
	
	if(is_error){
		$(".goods-nav ul li:eq(0)").click();
		return false;
	}
	return true;
}

var flag = false;//防止重复提交
//保存商品
function SubmitProductInfo(type, ADMIN_MAIN,SHOP_MAIN) {
	img_id_arr = "";// 商品主图
	var img_obj = $(".upload_img_id");
	for( var $i=0; $i<img_obj.length;$i++){
		var $checkObj=$(img_obj[$i]);
		if(img_id_arr == ""){
			img_id_arr = $checkObj.val();
		}else{
			img_id_arr +=","+ $checkObj.val();
		}
	}
	
	// 禁用按钮
	var validateResult = ValidateUserInput(); // 验证用户输
	var goodsstate = $("#goodsstate").val();
	if (validateResult) {
//		$("#btnSave,#btnSavePreview").attr("disabled", "disabled");
		var productViewObj = PackageProductInfo();
		var $qrcode = $("#hidQRcode").val();
		if(flag) return;
		flag = true;
//		var asd = JSON.stringify(productViewObj);
//		console.log(asd);
//		return;
		
		$.ajax({
			url : __URL(ADMINMAIN + "/goods/GoodsCreateOrUpdate"),
			type : "post",
			async : false,
			data : { "product" : JSON.stringify(productViewObj) , "is_qrcode" : $qrcode },
			dateType : "json",
			success : function(res) {
//				console.log(res);
//				return;
				var url = __URL(ADMIN_MAIN + "/goods/goodslist");
				var goodsId = parseInt($("#goodsId").val());
				var text = "";
				if (res != null) {
					if (type == 1) {
						var parameter_goodsid = goodsId;
						if(parameter_goodsid==0 || typeof(parameter_goodsid) == 'undefined'){
							parameter_goodsid = res;
						}
						url = __URL(SHOP_MAIN + "/goods/detail?goods_id="+parameter_goodsid);// 跳转到前台
						window.open(url);
					}
					if(goodsstate == 0 && goodsstate != ""){
						showMessage('success', "商品修改成功",__URL(ADMIN_MAIN +'/goods/goodslist?state_type=2'));
					}else{
						showMessage('success', "商品发布成功",__URL(ADMIN_MAIN +'/goods/goodslist'));
					}
				} else {
					showMessage('error', "商品发布失败",url);
					flag = false;
					$("#btnSave,#btnSavePreview").removeAttr("disabled")
				}
			}
		});
	}
}

/**
 * 创建时间：2015年6月11日18:07:10 创建人：高伟 功能说明：获取数据已对象方式存储
 */
function PackageProductInfo() {
	// 初始化一个实体 将页面所需的数据存放到对象中
	var shop_type = $("#shop_type").val();
	var productViewObj = new Object();
	productViewObj.goodsId = $("#goodsId").val();// 商品id 11号目前为死值 0
	productViewObj.title = $("#txtProductTitle").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 商品标题
	productViewObj.goods_type = $(".goods_type_select .goods_type_item.selected").attr("data-goods-type");//商品分类
	productViewObj.introduction = $("#txtIntroduction").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 商品简介，促销语
	productViewObj.goods_unit = $("#goodsUnit").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 商品单位
	
	productViewObj.categoryId = $("#tbcNameCategory").attr("cid");// 商品类目 
	var category_extend_id ="";
	$(".extend-name-category").each(function() {
		if(category_extend_id == ""){
			category_extend_id = $(this).attr("cid");
		}else{
			category_extend_id += "," + $(this).attr("cid");
		}
	})
	productViewObj.categoryExtendId = category_extend_id;// 商品扩展类目
	// 12号 商品类目；
	productViewObj.market_price = $("#txtProductMarketPrice").val().replace(/^\s*/g, "").replace(/\s*$/g, "") == "" ? 0 : $("#txtProductMarketPrice").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 市场价
	productViewObj.price = $("#txtProductSalePrice").val().replace(/^\s*/g, "").replace(/\s*$/g, "") == "" ? 0 : $("#txtProductSalePrice").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 销售价
	productViewObj.cost_price = $("#txtProductCostPrice").val().replace(/^\s*/g, "").replace(/\s*$/g, "") == "" ? 0 : $("#txtProductCostPrice").val().replace(/^\s*/g, "").replace(/\s*$/g,"");// 成本价
	productViewObj.libiary_goodsid = $("#libiary_goodsid").val(); // 商品库id
	productViewObj.base_sales = $("#BasicSales").val() == '' ? 0 : $("#BasicSales").val();// 基础销量
	productViewObj.base_good = $("#BasicPraise").val() == '' ? 0 : $("#BasicPraise").val();// 基础点赞数
	productViewObj.base_share = $("#BasicShare").val() == '' ? 0 : $("#BasicShare").val();// 基础分享数
	productViewObj.code = $("#txtProductCodeA").val();// 商品编码
	productViewObj.is_sale = $("input[name='shelves']:checked").val();// 上下架标记
	productViewObj.display_stock = $('.controls input[name="stock"]:checked ').val();// 是否显示库存
	productViewObj.stock = $("#txtProductCount").val();// 总库存
	productViewObj.minstock = $("#txtMinStockLaram").val();// 库存预警数
	productViewObj.max_buy = $("#PurchaseSum").val().replace(/^\s*/g, "").replace(/\s*$/g, "") == "" ? 0 : $("#PurchaseSum").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 每人限购
	productViewObj.min_buy = $("#minBuy").val().replace(/^\s*/g, "").replace(/\s*$/g, "") == "" ? 0 : $("#minBuy").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 最少购买数
	productViewObj.key_words = $("#txtKeyWords").val().replace(/^\s*/g, "").replace(/\s*$/g, "");//商品关键词
	productViewObj.description = UE.getEditor('editor').getContent().replace(/\n*/g, "").replace(/\r*/g, "");// 商品详情描述
	productViewObj.shipping_fee = $("input[name='fare']:checked").val();// 运费方式
	productViewObj.shipping_fee_id = $("#expressCompany").val();
	productViewObj.pc_custom_template = $("#pc_custom_template").val();
	productViewObj.wap_custom_template = $("#wap_custom_template").val();
	//alert(JSON.stringify(productViewObj));
	// var shopCategoryText = "";
	// $(".goods-group-line .goods-gruop-select").each(function() {
	// 	if($(this).val() > 0){
	// 		shopCategoryText += $(this).val() + ",";
	// 	}
	// })
	// if (shopCategoryText != "") {
	// 	shopCategoryText = shopCategoryText.substring(0,shopCategoryText.length - 1);
	// 	var goodsgroup_array = shopCategoryText.split(",");
	// 	var goodsgroup_array = undulpicate(goodsgroup_array);
	// 	shopCategoryText = goodsgroup_array.join(",");
	// }
	productViewObj.groupArray = $("#goods_group").val() == null ? 0 : $("#goods_group").val().toString();
	productViewObj.supplierId = $("#supplierSelect").val();//供货商
	productViewObj.brandId = $("#brand_id").val();//品牌id
	productViewObj.picture = img_id_arr.split(",")[0];
	var imageVals = img_id_arr;// 在页面中获取的
	productViewObj.imageArray = imageVals;// 商品图片分组
	//sku规格图片
	var sku_img_obj = $(".sku_upload_img_id");	
	var sku_picture_obj = new Array();
	for( var $i=0; $i<sku_img_obj.length;$i++){		
		var $checkObj = $(sku_img_obj[$i]);
		var spec_id = $checkObj.attr("spec_id");
		var spec_value_id = $checkObj.attr("spec_value_id");
		var img_id = $checkObj.val();
		var is_have = 0;	
		for(var i = 0; i < sku_picture_obj.length ; i ++ ){
			if(sku_picture_obj[i].spec_id == spec_id && sku_picture_obj[i].spec_value_id == spec_value_id){
				sku_picture_obj[i]["img_ids"] = sku_picture_obj[i]["img_ids"]+","+img_id;
				is_have = 1;
			}
		}
		if(is_have == 0){
			//给此规格添加对象内部空间 并添加此属性
			var obj_length = sku_picture_obj.length;
			sku_picture_obj[obj_length] = new Object();
			sku_picture_obj[obj_length].spec_id = spec_id;
			sku_picture_obj[obj_length].spec_value_id = spec_value_id;
			sku_picture_obj[obj_length]["img_ids"] = img_id;

		}
	}
	console.log(JSON.stringify(sku_picture_obj));
	productViewObj.sku_picture_vlaues = JSON.stringify(sku_picture_obj);
	
	var sku_type = $('[name="sku_type"]:checked').val();
	if(sku_type == 1){
		productViewObj.skuArray = sku_table_data();
		productViewObj.goods_spec_format = JSON.stringify(sku_spec_list());
	}else{
		productViewObj.skuArray = {}
		productViewObj.goods_spec_format = JSON.stringify([]);
	}

	
	productViewObj.goods_attribute_id= $("#goodsType").val();
	productViewObj.sort = $("#hidden_sort").val();
	var goods_attribute_arr = new Array();
	$(".js-attribute-text").each(function(){
		var goods_attribute = {
			attr_value_id :$(this).attr("data-attribute-value-id"),
			attr_value : $(this).attr("data-attribute-value"),
			attr_value_name : $(this).val(),
			sort : $(this).attr("data-attribute-sort")
		};
		goods_attribute_arr.push(goods_attribute);
	});

	$(".js-attribute-radio").each(function(){
		if($(this).is(":checked")){
			var goods_attribute = {
				attr_value_id :$(this).attr("data-attribute-value-id"),
				attr_value : $(this).attr("data-attribute-value"),
				attr_value_name : $(this).val(),
				sort : $(this).attr("data-attribute-sort")
			};
			goods_attribute_arr.push(goods_attribute);
		}
	});

	$(".js-attribute-checkbox").each(function(){

		if($(this).is(":checked")){
			var goods_attribute = {
				attr_value_id :$(this).attr("data-attribute-value-id"),
				attr_value : $(this).attr("data-attribute-value"),
				attr_value_name : $(this).val(),
				sort : $(this).attr("data-attribute-sort")
			};
			goods_attribute_arr.push(goods_attribute);
		}
	});
	
	productViewObj.goods_attribute = "";
	if(goods_attribute_arr.length>0){
		productViewObj.goods_attribute = JSON.stringify(goods_attribute_arr);
	}
	
	productViewObj.goods_class = $("#class_tbname").attr("cid") == '' ? 0 : $("#class_tbname").attr("cid");
	productViewObj.goods_returnRate = $("#txtGoodsReturnRate").val() == '' ? 0 : $("#txtGoodsReturnRate").val();
	if (shop_type == 1) {
		productViewObj.sup_shopid = $("#sup_shopidselect").val();
		productViewObj.sale_area = $("#txtGoodsAreasid").val();
		productViewObj.sup_price = $("#txtProductSupplyPrice").val();
		productViewObj.cb_cost_price = $("#txtProductCBCostPrice").val();
	} else {
		productViewObj.sup_shopid = 0;
		productViewObj.sale_area = "";
		productViewObj.sup_price = 0;
		productViewObj.cb_cost_price = 0;
	}
	// 积分购买设置 
		//兑换积分
	productViewObj.integration_available_use = $("#integration_available_use").val() == '' ? 0 : $("#integration_available_use").val();
	//购买赠送积分 赠送类型 0固定值 1按比率
	productViewObj.integral_give_type = $("input[name='integral_give_type']:checked").val();
	if(productViewObj.integral_give_type == 0){
		productViewObj.integration_available_give = $("#integration_available_give").val() == '' ? 0 : $("#integration_available_give").val();
	}else{
		productViewObj.integration_available_give = $("#integration_available_give_ratio").val() == '' ? 0 : $("#integration_available_give_ratio").val();
	}	
		//积分兑换设置
	productViewObj.point_exchange_type = $("input[name='integralSelect']:checked").val();
		//最大可使用积分
	productViewObj.max_use_point = $("#max_use_point").val();	

	productViewObj.province_id = $("#provinceSelect").val();// 商品所在地：省
	productViewObj.city_id = $("#citySelect").val();// 商品所在地：市
	productViewObj.qrcode  = $("#hidden_qrcode").val();
	
	//物流信息
	productViewObj.goods_weight = $("#goods_weight").val();
	productViewObj.goods_volume = $("#goods_volume").val();
	productViewObj.shipping_fee_type = $("input[name='shipping_fee_type']:checked").val();;
	
	productViewObj.production_date = $("#production_date").val(); //生产日期
	productViewObj.shelf_life = $("#shelf_life").val(); // 保质期
	productViewObj.goods_video_address = $("#video_url").val();
	var ladder_preference_arr = new Array();
	$(".ladder_preference").each(function(){
		var ladder_preference = $(this).find(".ladder").val() + ':' + $(this).find(".preference").val();
		ladder_preference_arr.push(ladder_preference);
	})
	productViewObj.ladder_preference = ladder_preference_arr.toString();
	
	//虚拟商品类型数据
	var virtual_goods_type_data = {};//虚拟商品类型数据
	virtual_goods_type_data.virtual_goods_type_id = $("#virtual_goods_type_id").val();
	virtual_goods_type_data.virtual_goods_group_id = parseInt($("input[name='virtual_goods_type']:checked").val());
	virtual_goods_type_data.validity_period = $("#validity_period").val() == "" ? 0 : parseInt($("#validity_period").val());
	
	//限制用户非法输入限制使用次数
	switch(parseInt($("input[name='virtual_goods_type']:checked").val())){
//	case 1:
//		//网上服务限制次数自定义
//		virtual_goods_type_data.confine_use_number = $("#confine_use_number").val() == "" ? 0 : parseInt($("#confine_use_number").val());
//		break;
	case 1:
	case 3:
		//网上服务，点卡，限制使用1次
		virtual_goods_type_data.confine_use_number = 1;
		break;
	case 2:
	case 4:
		//网盘下载，网站直接下载不限制次数
		virtual_goods_type_data.confine_use_number = 0;
		break;
	}
	switch(parseInt($("input[name='virtual_goods_type']:checked").val())){
		case 1:
			//网上服务
			virtual_goods_type_data.value_info = new Array();
			break;
		case 2:
			//网盘下载
			virtual_goods_type_data.value_info = new Array();
			virtual_goods_type_data.value_info.push({
				cloud_address : $("#cloud_address").val(),
				cloud_password : $("#cloud_password").val()
			});
			break;
		case 3:
			//点卡
			var point_card = new Array();
			var card_password = $("#card_password").val();
			if(typeof(card_password) == "undefined"){
				virtual_goods_type_data.value_info = "";
				break;
			}
			
			var cp_array = card_password.split("\n");
			for(var i=0;i<cp_array.length;i++){
				
				if(cp_array[i]!=""){
					point_card.push({
						remark : cp_array[i]
					});
				}
			}
			virtual_goods_type_data.value_info = point_card;
			break;
		case 4:
			//网站直接下载
			virtual_goods_type_data.value_info = new Array();
			virtual_goods_type_data.value_info.push({
				download_resources : $("#download_resources").val(),
				unzip_password : $("#unzip_password").val()
			});
			break;
	}
	//console.log(virtual_goods_type_data);
	productViewObj.virtual_goods_type_data = JSON.stringify(virtual_goods_type_data);
	
	//预售设置
	productViewObj.is_open_presell = $('[name="open_presell"]:checked').val();
	productViewObj.presell_price = $('#presell_price').val();
	productViewObj.presell_delivery_type = $('input[name="presell_delivery_type"]:checked').val() != null ? $('input[name="presell_delivery_type"]:checked').val() : 1;
	productViewObj.presell_day = $('#presell_day').val();
	productViewObj.presell_time = $('#presell_time').val();
	// 会员折扣
	var member_discount_arr = new Array();
	$("input[name='member_discount']").each(function(){
		var discount = parseInt($(this).val());
		if(discount != NaN && discount > 0 && discount <= 100){
			var member_discount = new Object();
				member_discount.level_id = $(this).attr("data-level-id");
				member_discount.discount = discount;
			member_discount_arr.push(member_discount);
		}
	})
	productViewObj.member_discount_arr = JSON.stringify(member_discount_arr);
	var decimal_reservation_number = $("input[name='decimal-reservation-number']:checked").val();
	productViewObj.decimal_reservation_number = decimal_reservation_number == undefined ? -1 : decimal_reservation_number;
	return productViewObj;
}

//处理积分非法输入
function integrationChange(event) {
	$integration_val = parseInt($(event).val());
	if ($integration_val < 0) {
		$(event).val(0);
	}
	$(event).val($integration_val);
}

//非空判断
function IsEmpty(obj) {
	var val = $.trim($(obj).val());
	if (val == "") {
		$(obj).focus();
		return false;
	}
	return true;
}

/**
 * 获取当前时间随机数
 * @returns
 */
function getDate(){
	var date = new Date();
	var time = date.getSeconds().toString()+date.getMilliseconds().toString();
	return time;
}

/**
 * 循环价格
 */
function eachPrice() {
	var $price = 0;
	$.each($('input[name="sku_price"]'), function(i, item) {
		var $this = $(item);
		var num = $this.val();
		var numint = parseFloat(num);
		var priceint = parseFloat($price);
	
		if ($price == 0 || numint < priceint) $price = num;
	});
	$("#txtProductSalePrice").val($price);
}

/**
 * 循环市场价 2016年12月2日 11:55:30
 */
function eachMarketPrice() {
	var $price = 0;
	$.each($('input[name="market_price"]'), function(i, item) {
		var $this = $(item);
		var num = $this.val();
		var numint = parseFloat(num);
		var priceint = parseFloat($price);
		if ($price == 0 || numint < priceint) $price = num;
	});
	$("#txtProductMarketPrice").val($price);
}

/**
 * 循环成本价 2016年12月2日 12:14:27
 */
function eachCostPrice() {
	var $price = 0;
	$.each($('input[name="cost_price"]'), function(i, item) {
		var $this = $(item);
		var num = $this.val();
		var numint = parseFloat(num);
		var priceint = parseFloat($price);
		if ($price == 0 || numint < priceint) $price = num;
	});
	$("#txtProductCostPrice").val($price);
}

/**
 * 循环商家编码，取第一个
 * 创建时间：2017年9月29日 11:44:05
 */
function eachMerchantCode(){
//	if($('input[name="code"]:last').val() != undefined && $('input[name="code"]:last').val() != ""){
//		$("#txtProductCodeA").val($('input[name="code"]:last').val());
//	}
}

/**
 * 循环库存
 */
function eachInput() {
	var $stockTotal = 0;
	$.each($('input[name="stock_num"]'), function(i, item) {
		var $this = $(item);
		var num = 0;
		num = parseInt($this.val());
		$stockTotal = $stockTotal + num;
	});
	$("#txtProductCount").val($stockTotal);
}

//选择商品类目后回到函数
function addGoodsCallBack(goods_category_id ,goods_category_name ,goods_attr_id , goodsid, dialog_flag, box_id){
	switch(dialog_flag){
	case "category":

		$("#tbcNameCategory .category-text").html(goods_category_name);
		$("#tbcNameCategory").attr("cid",goods_category_id);
		$("#tbcNameCategory").attr("data-attr-id",goods_attr_id);
		$("#tbcNameCategory").attr("cname",goods_category_name);
//		if(goodsid == 0){
//			$("#goodsType").val(goods_attr_id);
//			goodsTypeChangeData();
//			getGoodsSpecListByAttrId($("#goodsType").val());
//			removeSpecPictureBox();
//			if(parseInt($("#goodsType").val()) == 0){
//				//如果没有选择商品类型，则清空属性信息
//				$(".js-goods-attribute-block").hide();
//				$(".js-goods-sku-attribute").html("");
//			}
//		}
		break;
	case "extend_category":
		$("#"+box_id+" .category-text").html($.trim(goods_category_name));
		$("#"+box_id).attr("cid",goods_category_id);
		$("#"+box_id).attr("data-attr-id",goods_attr_id);
		$("#"+box_id).attr("cname",goods_category_name);
		break;
	}
}

/**
 * 添加扩展分类
 */
function addExtentCategoryBox(){
	var html = '<div class="extend-name-category" id="extend_name_category'+extent_sort+'" data-flag="extend_category" data-goods-id="0" cid="" data-attr-id="" cname="">';
	html += '<span class="category-text"onclick="editCategory(this);"></span>';
	html += '&nbsp;&nbsp;<span class="do-style" onclick="editCategory(this);"><i class="fa fa-edit"></i>&nbsp;编辑</span>&nbsp;&nbsp;';
	html += '<span class="do-style" onclick="removeParentBox(this);"><i class="fa fa-trash-o"></i>&nbsp;删除</span>';
	html += '<span class="help-inline" style="vertical-align: top;">已添加的商品扩展分类不能为空</span>';
	$(".extend-name-category-box").append(html);
	extent_sort++;
}

/**
 * 编辑扩展分类
 */
function editCategory(obj){
	var goodsid = $(obj).parent().attr("data-goods-id");
	var category_id = $(obj).parent().attr("cid");
	var flag = $(obj).parent().attr("data-flag");
	var box_id = $(obj).parent().attr("id");
	var category_extend_id = "";
	$(".extend-name-category").each(function() {
		if(category_extend_id == ""){
			category_extend_id = $(this).attr("cid");
		}else{
			category_extend_id += "," + $(this).attr("cid");
		}
	});
	OpenCategoryDialog(ADMINMAIN,category_id,goodsid,flag, box_id, category_extend_id);
}

/**
 * 删除本条扩展分类
 * @param obj
 */
function removeParentBox(obj){
	$(obj).parent().remove();
}

//导航分组
function changeGoodsGroup(obj){
	if($(obj).val() > 0){
		var exist_num = 0;
		$(".goods-group-div .goods-group-line .goods-gruop-select").each(function() {
			if($(this).val() == 0) exist_num++;
		})
		if(exist_num < 1){
			if($.trim(group_str) != "" && $.trim(group_str) != undefined){
				var html = "<div class='goods-group-line'><select class='goods-gruop-select select-common' onchange='changeGoodsGroup(this);'>";
				html +="<option value='0'></option>"
				var group_array = eval(group_str);
				
				for(var i = 0; i < group_array.length ; i++ ){
					html +="<option value='"+ group_array[i]["group_id"] +"'>"+ group_array[i]["group_name"] +"</option>"
				}
				html +="</select></div>";
				$(".goods-group-div").append(html);
			}else{
				$(".span-error").show();
			}
		}
	}else{
		if($(".goods-group-div .goods-group-line .goods-gruop-select").length > 1) $(obj).parent().remove();
	}
}

//数组去重
function undulpicate(array){
	for(var i=0;i<array.length;i++) {
		for(var j=i+1;j<array.length;j++) {
		//注意 ===
			if(array[i]===array[j]) {
				array.splice(j,1);
				j--;
			}
		}
	}
	return array;
}

/**
 * 根据商品类型id，查询商品规格信息
 * @param attr_id 规格属性id
 */ 
function getGoodsAttributeListByAttrId(attr_id, callBack){
	if(!isNaN(attr_id) && attr_id > 0){
		$.ajax({
			url : __URL(ADMINMAIN+"/goods/getGoodsSpecListByAttrId"),
			type : "post",
			data : { "attr_id" : parseInt(attr_id)},
			success : function(res){
				if(res !=-1){
					var attribute_length = res.attribute_list.length;
				
					//商品属性集合
					if(attribute_length>0){
						var html ="";
						for(var i=0;i<attribute_length;i++){
							var curr = res.attribute_list[i];
							if($.trim(curr.value_items) == "" && parseInt(curr.type) !=1) continue;
							if($.trim(curr.attr_value_name) != ""){
							
							html += '<tr style="padding-top:15px;padding-bottom:15px;">';
								html += '<td width="10%" style="border:1px solid #E9E9E9;"align="right" class="txt12" data-value="'+curr.attr_value_name+'">'+curr.attr_value_name+'</td>';
								html += '<td width="80%" style="border:1px solid #E9E9E9;">';
									switch(parseInt(curr.type)){
										case 1:
											//输入框
											html += '<input type="text" class="js-attribute-text input-common" id="input-text-'+curr.attr_value_id+'-'+curr.attr_value_id+'"data-attribute-value-id="'+curr.attr_value_id+'" data-attribute-value="'+curr.attr_value_name+'" />';
											break;
										case 2:
											//单选框
											for(var j=0;j<curr.value_items.length;j++){
												var value = curr.value_items[j];
												if($.trim(value) != ""){
													html += '<div class="goods-sku-attribute-item-radio">';
														html += '<i class="radio-common"><input type="radio" value="'+value+'" class="js-attribute-radio" id="radio_value_item'+curr.attr_value_id+'-'+j+'" data-attribute-value-id="'+curr.attr_value_id+'" data-attribute-value="'+curr.attr_value_name+'"  name="radio_value'+i+'" /></i>&nbsp;';
														html += '<label for="radio_value_item'+curr.attr_value_id+'-'+j+'">'+value+'</label>';
													html += '</div>';
												}
											}
											break;
										case 3:
											//复选框
											for(var j=0;j<curr.value_items.length;j++){
												var value = curr.value_items[j];
												if($.trim(value) != ""){
													html += '<div class="goods-sku-attribute-item-checkbox">';
														html += '<i class="checkbox-common"><input type="checkbox" value="'+value+'" class="js-attribute-checkbox" id="checkbox_value_item'+curr.attr_value_id+'-'+j+'" data-attribute-value-id="'+curr.attr_value_id+'" data-attribute-value="'+curr.attr_value_name+'"  name="checkbox_value_item'+i+'" /></i>&nbsp;';
														html += '<label for="checkbox_value_item'+curr.attr_value_id+'-'+j+'">'+value+'</label>';
													html += '</div>';
												}
											}
											break;
									}
								html += '</td>';
							html += '</tr>';
							}
							
						}
						$(".js-goods-sku-attribute").html(html);
						if(callBack != undefined) callBack();
					}
					
					$(".js-goods-attribute-block").show();

				}
			}
		});
	}else{
		//商品类型为0时，清楚左侧规格列表
		$(".sku-value-list ul  li:not(.add-sku").remove();
	}
}

/**
 * 文件上传（视频、音频）
 */
function fileUpload_video(event) {
	var fileid = $(event).attr("id");
	var dom = document.getElementById(fileid);
	var file =  dom.files[0];//File对象;
	var fileTypeArr = ['video/mp4'];
	var flag = false;
	if(file != null){
		for(var i=0;i<fileTypeArr.length;i++){
			if(file.type == fileTypeArr[i]){
				flag = true;
				break;
			}
		}
	}
	if(!flag){
		showTip("文件类型不合法，请上传.mp4文件","warning");
	}else{
		var data = { 'file_path' : "goods_video" };
		uploadFile({
			url: __URL(ADMINMAIN + '/goods/uploadvideo'),
			fileId: fileid,
			data : data,
			callBack: function (res) {
				if(res.code){
					$("#video_url").val(res.data.path);
//				$("#my-video").attr('poster','');
//				$("#my-video").attr('src',__IMG(res['data']));
					$(".del-video").show();
					var video = "my-video";
					var myPlayer = videojs(video);
					var videoUrl = __IMG(res.data.path);
					
					videojs(video).ready(function(){
						
						var myPlayer = this;
						myPlayer.src(videoUrl);
						myPlayer.load(videoUrl);
						myPlayer.play();
						
					});
					
					showTip(res.message,"success");
				}else{
					showTip(res.message,"error");
				}
			}
		});
	}
}

//根据浏览器变化事件，调整底部按钮宽度
function resizeBtnSubmit(){
	var width = $(".ncsc-form-goods").width();
	$(".btn-submit").css("width",width+"px");
	$(".goods-nav").css("max-width",(width - 5)+'px');
}

function CheckAll(event){
	var checked = event.checked;
	$(".point-card-inventory-list table input[type = 'checkbox']").prop("checked",checked);
}

function fileUpload(event) {
	var data = { 'file_path' : "goods_file" };
	uploadFile({
		url: __URL(ADMINMAIN + '/goods/uploadimage'),
		fileId: "uploadDownloadResources",
		data : data,
		callBack: function (res) {
			if(res.code){
				$("#download_resources").val(res.data.path);
				showTip(res.message,"success");
			}else{
				showTip(res.message,"error");
			}
		}
	});
}

// 选择原始规格 显示对应规格值
$(".original-sku-list li").live("click",function(){
	
	//判断如果是筛选框则不进行本动作
	if($(this).attr("class") == "goods-type-search-block") return;
	
	var index = $(this).index();
	$('.original-sku-list li:not([data-spec-value-id-array])').removeClass("selected");
	$(this).addClass("selected curr");
	$(this).siblings().removeClass("curr");

	//查看当前选择规格的规格值列表
	$(".edit-sku-popup-body article>div.sku-value .sku-value-list").show().siblings().hide();
	var html = '';
	var spec_value = eval($(this).attr("data-spec-value-json"));
	var spec_name = $(this).attr("data-spec-name");
	var show_type=  $(this).attr("data-show-type");
	var spec_id = $(this).attr("data-spec-id");
	if(spec_value!=null){
		
		var spec_value_id_array = eval($(".sku-value-list ul  li[data-spec-id='" + spec_id + "']").attr("data-spec-value-id-array"));
		$(".edit-sku-popup-body article>div.sku-display-mode>ul li[data-spec-value-id]").hide();
		$(".edit-sku-popup-body article>div.sku-display-mode .empty-info").show().nextAll().hide();
		for(var i=0; i<spec_value.length; i++){
			var curr = spec_value[i];
			if($.inArray(parseInt(curr.spec_value_id),spec_value_id_array)>-1){
				//规格值的展示方式
				$(".edit-sku-popup-body article>div.sku-display-mode .empty-info").hide().nextAll().show();
				$(".edit-sku-popup-body article>div.sku-display-mode>ul li[data-spec-value-id='" + curr.spec_value_id + "']").show();
				$(".edit-sku-popup-body article>div.sku-display-mode nav ul li label .radio-common input[value='" + show_type + "']").attr("checked","checked").parent().click();
				
				html += '<li class="selected" data-spec-value-id="' + curr.spec_value_id + '" data-spec-id="' + curr.spec_id + '" data-spec-name="' + spec_name + '" data-spec-value-name="' + curr.spec_value_name + '" data-show-type="' + show_type + '" data-spec-value-data="' + curr.spec_value_data + '">' + curr.spec_value_name + '<i class="shut">×</i></li>';
			}else{
				html += '<li data-spec-value-id="' + curr.spec_value_id + '" data-spec-id="' + curr.spec_id + '" data-spec-name="' + spec_name + '" data-spec-value-name="' + curr.spec_value_name + '" data-show-type="' + show_type + '" data-spec-value-data="' + curr.spec_value_data + '">' + curr.spec_value_name + '<i class="shut">×</i></li>';
			}
		}
	}

	if($(this).attr("data-is-update-img") == undefined){
		$(this).attr("data-is-update-img", 0);
	}
	
	 html += '<li class="add-sku-value"><i class="fa fa-plus"></i>添加规格值</li>';
	$(".edit-sku-popup-body article>div.sku-value ul").html(html);

	$(".edit-sku-popup-body article>div.sku-value ul li:not(.add-sku-value)").arrangeable("", againGenerateSkuJson);
	$(".sku-value-list ul  li.add-sku").css({ "border-color" : "" }).find("input").hide().siblings().show();

	//还原添加规格值，隐藏规格值输入框
	$(".edit-sku-popup-body article .add-sku-value-input").hide().val("");
	$(".edit-sku-popup-body article>div.sku-value label").show();
	//修改全选按钮是否选择
//	if(spec_value_id_array != null && spec_value_id_array.length == ($(".edit-sku-popup-body article>div.sku-value ul li").length-1)){
//		$(".edit-sku-popup-body article>div.sku-value label input").attr("checked","checked").parent().addClass("selected");
//	}else{
//		$(".edit-sku-popup-body article>div.sku-value label input").removeAttr("checked").parent().removeClass("selected");
//	}
});




//选择商品规格弹出框中的规格值
$(".edit-sku-popup-body article>div.sku-value ul li:not(.add-sku-value)").live("click",function(){
	
	var spec_id = $(this).attr("data-spec-id");
	var spec_value_id = $(this).attr("data-spec-value-id");
	var spec_value_name = $(this).attr("data-spec-value-name");
	var spec_value_data = $(this).attr("data-spec-value-data");
	var spec_name = $(this).attr("data-spec-name");
	var spec_show_type = parseInt($(this).attr("data-show-type"));
	
	var count = $(".edit-sku-popup-body article>div.sku-value ul li:not(.add-sku-value)").length;//规格值总数量
	var spec_id_array = $(".sku-value-list ul  li[data-spec-id='" + $(this).attr("data-spec-id") + "']").attr("data-spec-value-id-array");

	// 判断该规格是否存在于当前规格区中
	var spec = $(".sku-value-list ul  li[data-spec-id='" + spec_id + "']");
	var original_spec = $(".original-sku-list li[data-spec-id='" + spec_id + "']");
	
	if(spec_id_array == null) spec_id_array = new Array();
	else spec_id_array = eval(spec_id_array);
	
	if(!$(this).hasClass("selected")){
		//选中
		$(this).addClass("selected");

		var curr_selected_num = $(this).parent("ul").find(".selected").length;
		if(spec.length == 0 && curr_selected_num > 0 && spec_id > 0){
			$(".sku-value-list ul  li[data-spec-id]").removeClass("curr");
			var spec_html = '<li title="'+original_spec.attr("title")+'" data-spec-id="' + spec_id + '" data-spec-value-json="' + StringTransference(original_spec.attr("data-spec-value-json"), {" " : "&nbsp;", "\"" : "&quot;"}) + '" data-spec-name="' + original_spec.attr('data-spec-name') + '" data-show-type="' + original_spec.attr('data-show-type') + '" data-spec-value-length="' + curr_selected_num + '" class="selected curr"><span>' + original_spec.attr('data-spec-name') + '</span><span>[' + curr_selected_num + '/' + curr_selected_num + ']</span></li>';
			$(".sku-value-list ul  .add-sku").before(spec_html);
//			var html = '<li class="add-sku-value"><i class="fa fa-plus"></i>添加规格值</li>';
//			$(".edit-sku-popup-body article>div.sku-value ul li:last-child").after(html);
			$('.sku-value-list ul  li:not(.add-sku)').arrangeable();
		}

		//防止重复
		if($.inArray(parseInt(spec_value_id),spec_id_array) == -1) spec_id_array.push(parseInt(spec_value_id));

		//第一次添加
		if($(".edit-sku-popup-body article>div.sku-display-mode>ul li[data-spec-value-id='" + spec_value_id +"']").length == 0){
			var display_mode_html = '';
			
			display_mode_html = '<li data-spec-value-id="' + spec_value_id + '" data-spec-show-type="1">';
			display_mode_html += '<span>' + spec_value_name + '</span><strong>编辑</strong>';
			display_mode_html += '</li>';
			
			if(spec_show_type == 2){
				display_mode_html += '<li data-spec-value-id="' + spec_value_id + '" data-spec-show-type="2" class="selected">';
			}else{
				display_mode_html += '<li data-spec-value-id="' + spec_value_id + '" data-spec-show-type="2">';
			}
				display_mode_html += '<input type="color" class="input-common-color" data-spec-value-id="' + spec_value_id + '" value="' + (spec_value_data == "" ? "#000000" : spec_value_data) + '">';
			display_mode_html += '</li>';
			
			if(spec_show_type == 3){
				display_mode_html += '<li data-spec-value-id="' + spec_value_id + '" data-spec-show-type="3" class="selected">';
			}else{
				display_mode_html += '<li data-spec-value-id="' + spec_value_id + '" data-spec-show-type="3">';
			}
				display_mode_html += '<div class="upload-btn-common">';
					display_mode_html += '<div>';
						display_mode_html += '<input type="hidden" id="sku_value_' + spec_value_id + '">';
					display_mode_html += '</div>';
					display_mode_html += '<em>添加图片</em>';
					display_mode_html += '<img id="preview_sku_value_' + spec_value_id + '" src="' + STATIC + '/blue/img/upload-common-select.png" data-html="true" data-container="body" data-placement="top" data-trigger="manual" data-original-title="" title="">';
				display_mode_html += '</div>';
			display_mode_html += '</li>';
			
			$(".edit-sku-popup-body article>div.sku-display-mode nav ul li label .radio-common input[value='" + spec_show_type + "']").attr("checked","checked").parent().click();
			$(".edit-sku-popup-body article>div.sku-display-mode>ul").append(display_mode_html);
		}else{
			//显示已存在的规格值 
			$(".edit-sku-popup-body article>div.sku-display-mode>ul li[data-spec-value-id='" + spec_value_id +"']").show();
			//更新图片修改值
			var spec_value_obj = $(".edit-sku-popup-body article>div.sku-value ul li[data-spec-value-id='"+spec_value_id+"']");
			if(spec_value_obj.attr("data-show-type") == 3 && spec_value_obj.attr("data-spec-value-data") != ""){
				var update_img_num = parseInt($(".sku-value-list ul  li[data-spec-id='" + $(this).attr("data-spec-id") + "']").attr("data-is-update-img")) + 1; 
				$(".sku-value-list ul  li[data-spec-id='" + $(this).attr("data-spec-id") + "']").attr("data-is-update-img", update_img_num);
			}
		}
	}else{
		//取消
		for(var i=0;i<spec_id_array.length;i++){
			if(spec_id_array[i] == spec_value_id){
				spec_id_array.splice(i,1);
				i--;
			}
		}
		$(this).removeClass("selected");

		$(".edit-sku-popup-body article>div.sku-display-mode>ul li[data-spec-value-id='" + spec_value_id +"']").hide();
		//更新图片修改值
		if($(this).attr("data-show-type") == 3 && $(this).attr("data-spec-value-data") != ""){
			var update_img_num = $(".sku-value-list ul  li[data-spec-id='" + $(this).attr("data-spec-id") + "']").attr("data-is-update-img"); 
				update_img_num = update_img_num - 1 <= 0 ? 0 : update_img_num - 1;
				$(".sku-value-list ul  li[data-spec-id='" + $(this).attr("data-spec-id") + "']").attr("data-is-update-img", update_img_num);
		}
	}

	if(spec_id_array.length){
		if(spec_id_array.length == count) $(".edit-sku-popup-body article>div.sku-value label input").attr("checked","checked").parent().addClass("selected");
		else $(".edit-sku-popup-body article>div.sku-value label input").removeAttr("checked").parent().removeClass('selected');
		
		$(".sku-value-list ul  li[data-spec-id='" + $(this).attr("data-spec-id") + "']").attr("data-spec-value-id-array",JSON.stringify(spec_id_array)).find("span:last").text("[" + spec_id_array.length + "/" + count + "]");
		$(".edit-sku-popup-body article>div.sku-display-mode .empty-info").hide().nextAll().show();
	}else{
		$(".sku-value-list ul  li[data-spec-id='" + $(this).attr("data-spec-id") + "']").removeAttr("data-spec-value-id-array").find("span:last").text("[" + spec_id_array.length + "/" + count + "]");;
		$(".edit-sku-popup-body article>div.sku-display-mode .empty-info").show().nextAll().hide();
	}
});

/**
 * 添加规格值->打开输入框
 * 创建时间：2018年4月14日11:33:55
 */
$(".edit-sku-popup-body article>div.sku-value ul li.add-sku-value").live("click",function(){
	var html = '<i class="fa fa-plus"></i>';
	if($(".edit-sku-popup-body article .add-sku-value-input").is(":visible")){
		$(".edit-sku-popup-body article .add-sku-value-input").hide();
		$(".edit-sku-popup-body article>div.sku-value label").show();
		html += '添加规格值';
	}else{
		$(".edit-sku-popup-body article .add-sku-value-input").show().find("input").focus();
		$(".edit-sku-popup-body article>div.sku-value label").hide();
		html += '取消';
	}
	$(this).html(html);
});

/**
 * 添加规格->输入要添加的规格名称
 * 修改时间：2018年4月14日11:33:23
 */
$(".sku-value-list ul  li.add-sku input").live("keyup",function(event){
	if(event.keyCode == 13){
		
		var $this = $(this);
		var spec_name = $this.val();

		if(spec_name.length==0){
			$(this).focus();
			showTip("请输入新规格名称","warning");
			return;
		}

		if(!vertifyStr(spec_name)){
			showTip("规格名称不能包含特殊字符", "error");
			return;
		}

		var space = new RegExp(" ","g");
			spec_name = spec_name.replace(space, "&nbsp;");
		
		var is_exist = false;
		$(".sku-value-list ul  li").each(function(){
			if($(this).attr("data-spec-name") == spec_name){
				is_exist = true;
				return false;
			}
		});
		
		if(is_exist){
			showTip("规格已存在，请勿重复添加","warning");
			return;
		}
		
		var predefined_id = 0;
		//判断是否重复
		do{
			predefined_id = -(($(".sku-value-list ul  li").length-1) + Math.floor(new Date().getSeconds()) + Math.floor(new Date().getMilliseconds()));
			if($(".sku-value-list ul  li[data-spec-id='" + predefined_id + "']").length==0) break;
			
		}while(true);
		
		var html = "<li data-spec-id="+ predefined_id +" data-spec-value-json='[]' data-spec-name='" + spec_name + "' data-show-type='" + 1 + "'><span>" + spec_name + "</span><span>[" + 0 + "/" + 0 + "]</span></li>";
		$(".sku-value-list ul  .add-sku").before(html);
		$('.sku-value-list ul  li:not(.add-sku)').arrangeable();
		$(".sku-value-list ul  li.add-sku").css({ "border-color" : "" }).find("input").hide().siblings().show();
		$(".sku-value-list ul ").animate({ "scrollTop" : $(".sku-value-list ul ")[0].scrollHeight },600);
		$this.val("");
		$(".sku-value-list ul  li:last").prev().click();
		showTip("添加规格成功","success");
	}
	// 按下esc
	if(event.keyCode == 27){
		$(".sku-value-list ul  li.add-sku").css({ "border-color" : "" }).removeClass('curr').find("input").hide().siblings().show();
		$(".sku-value-list ul  li:last").prev().click();
	}
});

//批量选择规格值
$(".edit-sku-popup-body article>div.sku-value label input").live("click",function(){

	if(!$(this).prop("checked")){
		$(".edit-sku-popup-body article>div.sku-value ul li:not(.add-sku-value)").addClass("selected").click();
	}else{
		$(".edit-sku-popup-body article>div.sku-value ul li:not(.add-sku-value)").removeClass("selected").click();
	}
});

/**
 * 添加规格值
 */
$(".edit-sku-popup-body article .add-sku-value-input input").live("keyup",function(event){
	if(event.keyCode == 13){
		var selected_spec = $(".edit-sku-popup-body aside .original-sku ul.original-sku-list li.selected.curr");
		var v = $(".edit-sku-popup-body article .add-sku-value-input input").val();
		
		if(v.length==0){
			showTip("请输入规格值名称","warning");
			return;
		}

		if(!vertifyStr(v)){
			showTip("规格值不能包含特殊字符", "error");
			return;
		}

		var space = new RegExp(" ","g");
		v = v.replace(space, "&nbsp;");

		var is_exist = false;
		$(".edit-sku-popup-body article>div.sku-value ul li[data-spec-value-id]").each(function(){
			if($(this).attr("data-spec-value-name") == v){
				is_exist = true;
				return false;
			}
		});
		
		if(is_exist){
			showTip("规格值已存在，请勿重复添加","warning");
			return;
		}
		
		var spec_value = {
			spec_id : selected_spec.attr("data-spec-id"), //规格id
			spec_name : selected_spec.attr("data-spec-name"),//规格名称
			show_type : selected_spec.attr("data-show-type"),//展示方式
			spec_value_name : v, //规格值 
			spec_value_data : "" //附加值,spec_value_data
		};
		
		var predefined_spec_value_id = 0;
		
		//判断是否重复
		do{
			var predefined_spec_value_id = spec_value.spec_id + Math.floor(new Date().getSeconds()) + Math.floor(new Date().getMilliseconds());
			//如果规格已经添到数据库中了，但是规格值还没有进库，需要改成负数
			if(predefined_spec_value_id>0) predefined_spec_value_id = -predefined_spec_value_id;
			if($(".edit-sku-popup-body article>div.sku-value ul li[data-spec-value-id='" + predefined_spec_value_id + "']").length==0)break;
			
		}while(true);

		var html = '<li data-spec-value-id="' + predefined_spec_value_id + '" data-spec-id="' + spec_value.spec_id + '" data-spec-name="' + spec_value.spec_name + '" data-spec-value-name="' + spec_value.spec_value_name + '" data-show-type="' + spec_value.show_type + '" data-spec-value-data="">' + spec_value.spec_value_name + '<i class="shut">×</i></li>';
		$(".edit-sku-popup-body article>div.sku-value ul .add-sku-value").before(html);
		$(".edit-sku-popup-body article>div.sku-value ul li:not(.add-sku-value)").arrangeable("", againGenerateSkuJson);
		
		//更新左侧对应的规格，需要修改总数量和data-spec-value-json对象
		var spec_value_json = eval(selected_spec.attr("data-spec-value-json"));
		var spec_value_id_array = eval(selected_spec.attr("data-spec-value-id-array"));
		var selected_count = 0;
		if(spec_value_id_array!=null) selected_count = spec_value_id_array.length;
		spec_value_json.push({
//			create_time : Math.floor(new Date().getTime()/1000),
//			sort : ($(".edit-sku-popup-body article>div.sku-value ul li").length-1),
			spec_id : spec_value.spec_id,
			spec_value_data : "",
			spec_value_id : predefined_spec_value_id,
			spec_value_name : spec_value.spec_value_name
		});
//		selected_spec.children("span:last").text("[" + selected_count + "/" + spec_value_json.length + "]");
		selected_spec.attr("data-spec-value-json",JSON.stringify(spec_value_json));
		selected_spec.attr("data-spec-value-length",spec_value_json.length);
		
		//还原输入框，防止重复添加
		$(".edit-sku-popup-body article .add-sku-value-input input").val("");
	}
});

function imgUpload(event) {
	var fileid = $(event).attr("id");
	var data = { 'file_path' : "goods_file" };
	uploadFile({
		url: __URL(ADMINMAIN + '/goods/uploadcompressedfile'),
		fileId: fileid,
		data : data,
		callBack: function (res) {
			if(res.code){
				$("#download_resources").val(res.data.path);
				$("#text_download_resources").val(res.data.path);
				showTip(res.message,"success");
			}else{
				showTip(res.message,"error");
			}
		}
	});
}

function StringTransference(str, ruleJson){
	$.each(ruleJson, function(rule, replace){
		var $rule = new RegExp(rule,"g");
		str = str.replace($rule, replace);
	});
	return str;
}

function againGenerateSkuJson(event){	
	var spec_id = $(event).attr("data-spec-id");
	var spec_value_arr = new Array();
	$(".sku-value-list ul li[data-spec-id='" + spec_id + "'].selected").each(function(){
		var spec_value_id = $(this).attr("data-spec-value-id"),
			spec_value_name = $(this).attr("data-spec-value-name"),
			spec_value_data = $(this).attr("data-spec-value-data");
		spec_value_arr.push({"spec_id" : spec_id,"spec_value_data" :spec_value_data, "spec_value_data" : spec_value_data, "spec_value_name" : spec_value_name, "spec_value_id" : spec_value_id});	
	})
	$(".sku-value-list ul  li[data-spec-id='" + spec_id + "']").attr("data-spec-value-json", JSON.stringify(spec_value_arr));
}

// 验证字符串不能含有特殊字符
function vertifyStr(str){
	var regEn = /[`"'[\]\\]/im;
    
	if(regEn.test(str)) {
	    return false;
	}else{
		return true;
	}
}

/**
 * 商品类型，SKU缩略图
 */
$('.sku-img-check').live('click',function(e){
	var js_img = $(this).attr("js-img");
	var spec_id = $(this).prev().prev().attr("data-spec-id");
	var spec_value_id = $(this).prev().prev().attr("data-spec-value-id");
	shopImageFlag = js_img;//所点击的商品图片标识
	speciFicationsFlag = 0;
	OpenPricureDialog("PopPicure", ADMINMAIN, 1, 3,spec_id, spec_value_id, "goods_sku");
});

$(".value-item-affiliate.upload-btn-common").live('click', function(){
	
	var spec_id = 0;
	var spec_value_id = $(this).parents('.spec-value-item').find('[name="spec_value"]').attr('spec_value_id');
	OpenPricureDialog("PopPicure", ADMINMAIN, 1, 3, 0 , spec_value_id, "goods_sku");
})


