/**
 * 商品js
 */
var c, f, g, w;
var obj;
var goods_id;
var speciFicationsFlag = 0;// 0：商品图的选择，1:商品详情的图片
$(function(){
	
	goods_id = $("#goods_id").val();
	
	//根据浏览器变化事件，调整底部按钮宽度
	w = $(".ncsc-form-goods").width();
	$(".btn-submit").css("width",w+"px");
	$(".goods-nav").css("max-width",(w - 5)+'px');
	
	/**
	 * 选择分类
	 */
	$('[data-flag="category"]').live("click",function(){
		obj = $(this);
		g = obj.attr("data-goods-id");
		c = obj.attr("cid");
		f = obj.attr("data-flag");
		OpenCategoryDialog(ADMINMAIN, c, g, f);
	});

	/**
	 * 查看大图
	 * 标准 name = "img_click" 
	 * 图片链接 对象属性加 img_src
	 */
	$('[name = "img_click"]').live("click",function(){
		
		src = $(this).attr('img_src');
		layer.open({
			  type: 1,
			  title: '',
			  area: ['700px', '400px'],
			  content: `<img src = "${__IMG(src)}"/>`
			  ,yes: function(index, layero){
				  layer.close(index);
			  }
		});
	})
	
	/**
	 * 删除对象
	 */
	$('[name = "img_delete"]').live("click",function(){
		obj = $(this);
		c = obj.attr('del_class');
		obj.parents('.'+c).remove();
	})
	
	/**
	 * 添加阶梯优惠
	 */
	$(".add_ladder_preference").click(function(){
		var laddre_length = $(".ladder_preference_content .ladder_preference").length;
		var html = '<div class="ladder_preference">';
			html += '<input type="number" class="input-common short ladder" value="0">';
			html += '<input type="number" class="input-common short preference" value="0">';
			html += '<a href="javascript:;" class="delete_preference">删除</a></div>';
		var prev_obj = $("#ladder_preference").prev();
		if(laddre_length <= 9){
			if(prev_obj.find(".ladder").val() != undefined &&　prev_obj.find(".preference").val() != undefined){
				if(prev_obj.find(".ladder").val() == 0 || prev_obj.find(".preference").val() == 0){
					showTip("请输入数量和优惠价格","warning");
				}else{
		 			$("#ladder_preference").before(html);
				}
			}else{
				$("#ladder_preference").before(html);
			}
		}
	});
	
	/**
	 * 删除阶梯对象
	 */
	$(".ladder_preference_content .ladder_preference .delete_preference").live("click",function(){
		$(this).parent().remove();
	});
	
	/**
	 * 切换tab
	 */
	$(".goods-nav ul li").click(function(){
		$("."+$(this).attr("data-c")).show().siblings("[class^='block-']").hide();
		$(this).addClass("selected").siblings().removeClass("selected");
	});
	
	/**
	 * 根据选择的商品类型，查询规格属性
	 */
	$("#goods_attribute_id").change(function(){
		getGoodsSpecListByAttrId($(this).val());
		if(parseInt($(this).val()) == 0){
//			//如果没有选择商品类型，则清空属性信息
			$(".js-goods-attribute-block").hide();
			$(".js-goods-sku-attribute").html("");
		}
	});
	
	if(parseInt($("#goods_attribute_id").val()) > 0){
		
		getGoodsSpecListByAttrId($("#goods_attribute_id").val(),function(){
			//现在取消商品属性和商品规格的guanxi
			//加载属性
			$(".js-goods-sku-attribute tr").each(function(){
				
				var value = $(this).children("td:first").attr("data-value");//商品属性名称
				var value_name = $(this).children("td:last");//具体的属性值
				
				if(value != undefined && value != ""){
					for(var i=0;i<goods_attribute_list.length;i++){
						
						var curr = goods_attribute_list[i];
						
						if(curr['attr_value'] == value){
							switch(value_name.find("input").attr("type")){
								case "text":
									value_name.find("input").val(curr['attr_value_name']);
									break;
								case "radio":
									value_name.find("input").each(function(){
										if($.trim($(this).val()) == $.trim(curr['attr_value_name'])){
											$(this).attr("checked","checked").parent().addClass("selected");
											return false;
										}
									})
									break;
								case "checkbox":
									value_name.find("input").each(function(){
										if($.trim($(this).val()) == $.trim(curr['attr_value_name'])){
											$(this).attr("checked","checked").parent().addClass("selected");
										}
									})
									break;
							}
							
							if(value_name.find("input").attr("type") != "checkbox"){
								break;
							}
						}
					}
				}
			});
		});
	}
	
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
	 * 地址
	 */
	if(parseInt(goods_id) > 0){
		initProvince("#provinceSelect",function(){
			
			//编辑商品时，加载数据
			obj = $("#provinceSelect");
			p = $('#province_id').val();
			obj.find("option[value='"+p+"']").attr("selected",true);
			obj.selectric();
			
			obj = $('#citySelect');c = $('#city_id').attr('value');
			getProvince("#provinceSelect",'#citySelect',c);
		});

	}else{
		initProvince("#provinceSelect");
	}
	
	//可搜索的商品品牌下拉选项框
	curr_searchable_select = $('#brand_id').searchableSelect();
	getGoodsBrandList();
	
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
	
	/**
	 * 视频地址输入加载
	 */
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
	
	/**
	 * 预售加载
	 */
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
	
	/**
	 * 赠送积分类型切换
	 */
	$("input[name='integral_give_type']").click(function(){
		if($(this).val() == 0){
			$("#integration_available_give").parent('.controls').show();
			$("#integration_available_give_ratio").parent('.controls').hide();
		}else{
			$("#integration_available_give").parent('.controls').hide();
			$("#integration_available_give_ratio").parent('.controls').show();
		}
	})
	
	/**
	 * 选择运费方式
	 */
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
	
})

/**
 * 查询商品品牌列表
 * @param brand_name
 * @param search_name
 * @returns
 */
function getGoodsBrandList(brand_name,search_name){
	var page_index = 1;
	var page_size = 20;
	brand_id = $("#hidden_brand_id").val();
	$.ajax({
		type : "post",
		url: __URL(ADMINMAIN + '/goods/getGoodsBrandList'),
		data : { "page_index" : page_index, "page_size" : page_size, "brand_name" : brand_name, "search_name" : search_name },
		success : function(res){
			if(brand_id != 0){
				$.ajax({
					type : "post",
					url: __URL(ADMINMAIN + '/goods/getGooodsBrandInfo'),
					async: false,
					data : { "brand_id" : brand_id},
					success : function(data){
						var html = '<option value="'+ data['brand_id'] +'">'+ data['brand_name'] +'</option>';
						if(res.total_count>0){
							for(var i=0;i<res['data'].length;i++){
								html += '<option value="' + res['data'][i].brand_id + '">' + res['data'][i].brand_name + '</option>';
							}
						}
						$("#brand_id").html(html);
					}
				})
			}else{
				var html = '<option value="0">请选择商品品牌</option>';
				if(res.total_count>0){
					for(var i=0;i<res['data'].length;i++){
						html += '<option value="' + res['data'][i].brand_id + '">' + res['data'][i].brand_name + '</option>';
					}
				}
				$("#brand_id").html(html);
			}
			//更新搜索结果
			$(".js-brand-block .searchable-select-items .searchable-select-item").remove();
			curr_searchable_select.buildItems();
		}
	});
}

//选择商品类目后回到函数
function addGoodsCallBack(goods_category_id, goods_category_name, goods_attr_id, goodsid, dialog_flag, box_id){
	switch(dialog_flag){
	case "category":
		$("#tbcNameCategory .category-text").html(goods_category_name);
		$("#tbcNameCategory").attr("cid",goods_category_id);
		$("#tbcNameCategory").attr("data-attr-id",goods_attr_id);
		$("#tbcNameCategory").attr("cname",goods_category_name);
		if(goods_id==0){
			//在添加商品时，选择商品分类后，要自动选择商品类型，提升使用性
			$("#goods_attribute_id").val(goods_attr_id).selectric().change();
		}
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

/**
 * 相册回调函数
 */
function PopUpCallBack(id, src, upload_type, spec_id, spec_value_id) {

	var idArr, srcArr;
	if (id.indexOf(",")) {
		idArr = id.split(',');
		srcArr = src.split(',');
	} else {
		idArr = new Array(id);
		srcArr = new Array(src);
	}

	switch(speciFicationsFlag){
		case 0:
			//商品主图
			if(srcArr.length>=1){
				html = "";
				for(var i=0;i<srcArr.length;i++){
					if(upload_type == 2){
						html +='<div class="upload-thumb sku-draggable-element'+ spec_id +'-'+ spec_value_id +' sku-draggable-element">';
							html +='<img src="'+ __IMG(srcArr[i]) +'">';
							html +='<input type="hidden" class="sku_upload_img_id" spec_id="'+ spec_id +'" spec_value_id="'+ spec_value_id +'" value="'+idArr[i]+'">';
							html +='<div class="black-bg hide">'; 
								html +='<div class="sku-off-box">&times;</div>';
							html +='</div>';
						html +='</div>'; 
						//将规格图片记录存入临时数组
						var pic_id = idArr[i];
						var pic_cover_mid = srcArr[i];
						for(var t = 0; t < $sku_goods_picture.length ; t ++ ){
							if($sku_goods_picture[t].spec_id == spec_id && $sku_goods_picture[t].spec_value_id == spec_value_id){
								$sku_goods_picture[t]["sku_picture_query"].push({"pic_id":pic_id, "pic_cover_mid":pic_cover_mid});
							}
						}
					}else if(upload_type == 1){
						html +='<div class="upload-thumb draggable-element">';
							html +='<img  src="'+__IMG(srcArr[i])+'">';  
							html +='<input type="hidden" class="upload_img_id" value="'+idArr[i]+'">';
							html +='<div class="black-bg hide">'; 
								html +='<div class="off-box">&times;</div>';
							html +='</div>';
						html +='</div>';
					}else if(upload_type == 3){
						
						//规格返回图片信息
						var sel_spe_value_obj = $('.sku_spec_list').find('[name="spec_value"][spec_value_id="'+spec_value_id+'"]');
						sel_spe_value_obj.parents('.spec-value-item').find('.affiliate_img img').attr("src", __IMG(srcArr[i]));
						sel_spe_value_obj.parents('.spec-value-item').find('.affiliate_img img').attr("src_path", srcArr[i]);
					}else if(upload_type == 4){
					
						var h = `<div class="ant-upload-list-item" id="${idArr[i]}" img-src="${srcArr[i]}">
								 	<a href="javascript:void(0)" class="ant-upload-list-item-thumbnail"><img src="${__IMG(srcArr[i])}" ></a>
									<span><a href="javascript:;"><i title="图片预览" class="anticon anticon-eye-o" name = "img_click" img_src="${srcArr[i]}"></i></a>
									<a href="javascript:;"><i title="删除图片" class="anticon anticon-delete" name = "img_delete" del_class="ant-upload-list-item"></i></a></span>
								</div>`;
						$(`[skuid="${spec_value_id}"]`).find('.goods-info-img-upload .upload-btn-common').before(h);
					}
				}
				if(upload_type == 1){	
					$("#default_uploadimg").remove();
					$(html).appendTo('.img-box');
					$('.draggable-element').arrangeable();
				}
			}
		break;
		case 1:
			//商品详情
			for (var i = 0; i < srcArr.length; i++) {
				var description = "<img src='"+__IMG(srcArr[i])+"' />";
				//在光标后添加内容
				UE.getEditor('editor').focus();
				UE.getEditor('editor').execCommand('inserthtml',description);
			}
		break;
	}
}

/**
 * 设置商品详情的图片
 * @returns
 */
function setUeditorImg() {
	
	speciFicationsFlag = 1;
	OpenPricureDialog("PopPicure", ADMINMAIN,30,3,0,0);
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

/**
 * 删除已选择的视频
 */
function del_video(event){
	
	// 通过ajax用php删除文件
	var src = $("#video_url").val();
	if(src!= ""){
		
		var video = 'my-video';
		var myPlayer = videojs(video);

		videojs(video).ready(function(){

			var myPlayer = this;
			myPlayer.pause();
		});

		
		$("#my-video").attr('src', "");
		$("#videoupload").val('');
		$("#video_url").val('');
	}
}


//验证
function ValidateUserInput() {
	
	//提示信息
	var msgError = function(obj, msg, nav){
		
		$(obj).next("span.help-inline").text(msg).show();
		$(obj).focus();
		$(".goods-nav ul li:eq("+nav+")").click();
	}
	
	// 商品标题
	if (!IsEmpty("#txtProductTitle")) {	
		
		msgError("#txtProductTitle", '请填写商品名称', 0);
		return false;
	}else if($("#txtProductTitle").val().length>60){
	
		msgError("#txtProductTitle", '商品标题不能大于60个字', 0);
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
	if($("#shelf_life").length>0 && $("#shelf_life").val().length>0){
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

	//资源文件
	if($('[name="sku_type"]:checked').val() == 0){

		if($('#extend_file').val() == ''){
            $("#extend_file").parents('dd').children("span:last").show();
            $("#extend_file").focus();
            return false;
		}
	}else{
		var is_null = 0;
		$('input[name="extend_1"]').each(function(){
			
			obj = $(this);
			if(obj.val() == ''){
				obj.focus();
				is_null = 1;
				layer.msg('资源文件不能为空！');
				return false; //跳出循环
			}
		})
		
		if(is_null == 1){
			$(".goods-nav ul li:eq(0)").click();
			return false;
		}
	}

	var reg_integral = /^\+?[1-9][0-9]*$/;
	//如果是积分商品，则必须设置积分
	if($("input[name='integralSelect']:checked").val() > 0){
		if($("#integration_available_use").val()=="" || $("#integration_available_use").val()==0){
			$(".goods-nav ul li:eq(0)").click();
			$("#integration_available_use").nextAll("span:last").text("请设置兑换所需积分").show();
			$(".goods-nav ul li:eq(5)").click();
			return false;
		}else if(!reg_integral.test($("#integration_available_use").val())){
			$(".goods-nav ul li:eq(0)").click();
			$("#integration_available_use").nextAll("span:last").text("积分必须为整数").show();
			$(".goods-nav ul li:eq(5)").click();
			return false;
		}else{
			$("#integration_available_use").nextAll("span:last").hide();
		}
		
	}

	if($("input[name='integral_give_type']:checked").val() == 0){
		if($("#integration_available_give_ratio").val() < 0){
			$(".goods-nav ul li:eq(5)").click();
			showTip("赠送积分不可为负数", "warning");
			return false;
		}
	}else{
		if($("#integration_available_give_ratio").val() < 0 || $("#integration_available_give_ratio").val() > 100){
			$(".goods-nav ul li:eq(5)").click();
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
				$(".goods-nav ul li:eq(7)").click();
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
			$(".goods-nav ul li:eq(7)").click();
			return false;
		}

		if(preference >= min_price){
			$(".goods-nav ul li:eq(0)").click();
			showTip("优惠价格不能大于或等于商品最小价格","error");
			$this.find(".preference").addClass("input-error");
			is_error = true;
			$(".goods-nav ul li:eq(7)").click();
			return false;
		}else if(preference < 0){
			$(".goods-nav ul li:eq(0)").click();
			showTip("优惠价格不可为负数","error");
			$this.find(".preference").addClass("input-error");
			is_error = true;
			$(".goods-nav ul li:eq(7)").click();
			return false;
		}else if(preference == 0){
			$(".goods-nav ul li:eq(0)").click();
			showTip("优惠价格不可为0","error");
			$this.find(".preference").addClass("input-error");
			is_error = true;
			$(".goods-nav ul li:eq(7)").click();
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
	
	//商品已经开启预售的同时不能设置为只能积分兑换
	var open_presell = $('[name="open_presell"]:checked').val();//预售
	var integral_select = $("input[name='integralSelect']:checked").val();//积分兑换
	if(open_presell == 1 && integral_select == 3){
		$(".goods-nav ul li:eq(5)").click();
		showTip("商品已经开启预售的同时不能设置为只能积分兑换","warning");
		$("body").scrollTop($("#integral_balance").offset().top-100);
		return false;
	}
	return true;
	
}

/**
 * 保存商品
 */
var flag = false;//防止重复提交
function SubmitProductInfo(type, ADMIN_MAIN,SHOP_MAIN) {
	
	// 禁用按钮
	var validateResult = ValidateUserInput(); // 验证用户输
	if (!validateResult) {return false;}
	
	var productViewObj = PackageProductInfo();
	var goodsstate = $("#goodsstate").val();
	
	if(flag) return;
	flag = true;
	
	$.ajax({
		url : __URL(ADMINMAIN + "/goods/GoodsCreateOrUpdate"),
		type : "post",
		async : false,
		data : { "data" : productViewObj},
		dateType : "json",
		success : function(res) {

			var url = __URL(ADMIN_MAIN + "/goods/goodslist");
			var text = "";
			if (res != null) {
				if (type == 1) {
					var parameter_goodsid = goods_id;
					if(parameter_goodsid==0 || typeof(parameter_goodsid) == 'undefined'){
						parameter_goodsid = res['code'];
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

function PackageProductInfo() {
	
	obj = new Object();
	obj.goods_id = $("#goods_id").val();	// 商品id
	obj.goods_type = $("#goods_type").val();	//商品类型
	obj.goods_name = $("#txtProductTitle").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 商品标题
	
	obj.introduction = $("#txtIntroduction").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 商品简介，促销语
	obj.goods_unit = $("#goodsUnit").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 商品单位
	obj.category_id = $("#tbcNameCategory").attr("cid");// 商品类目
	
	obj.market_price = $("#txtProductMarketPrice").val().replace(/^\s*/g, "").replace(/\s*$/g, "") == "" ? 0 : $("#txtProductMarketPrice").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 市场价
	obj.price = $("#txtProductSalePrice").val().replace(/^\s*/g, "").replace(/\s*$/g, "") == "" ? 0 : $("#txtProductSalePrice").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 销售价
	obj.cost_price = $("#txtProductCostPrice").val().replace(/^\s*/g, "").replace(/\s*$/g, "") == "" ? 0 : $("#txtProductCostPrice").val().replace(/^\s*/g, "").replace(/\s*$/g,"");// 成本价
	
	obj.sales = $("#BasicSales").val() == '' ? 0 : $("#BasicSales").val();// 基础销量
	obj.clicks = $("#BasicPraise").val() == '' ? 0 : $("#BasicPraise").val();// 基础点击数
	obj.shares = $("#BasicShare").val() == '' ? 0 : $("#BasicShare").val();// 基础分享数
	obj.code = $("#txtProductCodeA").val();// 商品编码
	obj.state = $("input[name='state']:checked").val();// 上下架标记
	obj.is_stock_visible = $('.controls input[name="stock"]:checked ').val();// 是否显示库存
	obj.stock = $("#txtProductCount").val();// 总库存
	obj.min_stock_alarm = $("#txtMinStockLaram").val();// 库存预警数
	
	obj.max_buy = $("#PurchaseSum").val().replace(/^\s*/g, "").replace(/\s*$/g, "") == "" ? 0 : $("#PurchaseSum").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 每人限购
	obj.min_buy = $("#minBuy").val().replace(/^\s*/g, "").replace(/\s*$/g, "") == "" ? 0 : $("#minBuy").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 最少购买数
	obj.keywords = $("#txtKeyWords").val().replace(/^\s*/g, "").replace(/\s*$/g, "");//商品关键词
	obj.description = UE.getEditor('editor').getContent().replace(/\n*/g, "").replace(/\r*/g, "");// 商品详情描述
	obj.shipping_fee = $("input[name='fare']:checked").val();// 运费方式
	obj.shipping_fee_id = $("#expressCompany").val();
	obj.pc_custom_template = $("#pc_custom_template").val();
	obj.wap_custom_template = $("#wap_custom_template").val();
	
	obj.group_id_array = $("#goods_group").val() == null ? 0 : $("#goods_group").val().toString();
	obj.supplier_id = $("#supplierSelect").val();//供货商
	obj.brand_id = $("#brand_id").val();//品牌id
	
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

	obj.picture = img_id_arr.split(",")[0];
	obj.img_id_array = img_id_arr;// 商品图片分组
	obj.sku_img_array = JSON.stringify([]); //字段废除
	
	var sku_type = $('[name="sku_type"]:checked').val();
	if(sku_type == 1){
		obj.skuArray = sku_table_data(1);
		obj.goods_spec_format = JSON.stringify(sku_spec_list());
	}else{
		obj.skuArray = {}
		obj.goods_spec_format = JSON.stringify([]);
	}
	
	obj.goods_attribute_id= $("#goods_attribute_id").val();
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
	
	obj.goods_attribute = "";
	if(goods_attribute_arr.length>0){
		obj.goods_attribute = JSON.stringify(goods_attribute_arr);
	}
	
	// 积分购买设置 
	obj.point_exchange = $("#integration_available_use").val() == '' ? 0 : $("#integration_available_use").val();
	//购买赠送积分 赠送类型 0固定值 1按比率
	obj.integral_give_type = $("input[name='integral_give_type']:checked").val();
	if(obj.integral_give_type == 0){
		obj.give_point = $("#integration_available_give").val() == '' ? 0 : $("#integration_available_give").val();
	}else{
		obj.give_point = $("#integration_available_give_ratio").val() == '' ? 0 : $("#integration_available_give_ratio").val();
	}	
		//积分兑换设置
	obj.point_exchange_type = $("input[name='integralSelect']:checked").val();
		//最大可使用积分
	obj.max_use_point = $("#max_use_point").val();	
	
	obj.province_id = $("#provinceSelect").val();// 商品所在地：省
	obj.city_id = $("#citySelect").val();// 商品所在地：市
	
	//物流信息
	obj.goods_weight = $("#goods_weight").val();
	obj.goods_volume = $("#goods_volume").val();
	obj.shipping_fee_type = $("input[name='shipping_fee_type']:checked").val();;
	
	obj.production_date = $("#production_date").val(); //生产日期
	obj.shelf_life = $("#shelf_life").val(); // 保质期
	obj.goods_video_address = $("#video_url").val();
	
	var ladder_preference_arr = new Array();
	$(".ladder_preference").each(function(){
		var ladder_preference = $(this).find(".ladder").val() + ':' + $(this).find(".preference").val();
		ladder_preference_arr.push(ladder_preference);
	})
	obj.ladder_preference = ladder_preference_arr.toString();
	
	//预售设置
	obj.is_open_presell = $('[name="open_presell"]:checked').val();
	obj.presell_price = $('#presell_price').val();
	obj.presell_delivery_type = $('input[name="presell_delivery_type"]:checked').val() != null ? $('input[name="presell_delivery_type"]:checked').val() : 1;
	obj.presell_day = $('#presell_day').val();
	obj.presell_time = $('#presell_time').val();
	
	var sku_type = $('[name="sku_type"]:checked').val();
	if(sku_type == 1){
		obj.skuArray = sku_table_data();
		obj.goods_spec_format = JSON.stringify(sku_spec_list());
	}else{
		obj.skuArray = {}
		obj.goods_spec_format = JSON.stringify([]);
	}
	
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
	obj.member_discount_arr = JSON.stringify(member_discount_arr);
	var decimal_reservation_number = $("input[name='decimal-reservation-number']:checked").val();
	obj.decimal_reservation_number = decimal_reservation_number == undefined ? -1 : decimal_reservation_number;

	//资源文件
    var extend_obj = new Object();
    extend_obj['extend_1'] = $('#extend_file').val();
	obj.extend_json = extend_obj;
	return obj;
}

function StringTransference(str, ruleJson){
	$.each(ruleJson, function(rule, replace){
		var $rule = new RegExp(rule,"g");
		str = str.replace($rule, replace);
	});
	return str;
}

//处理积分非法输入
function integrationChange(event) {
	$integration_val = parseInt($(event).val());
	if ($integration_val < 0) {
		$(event).val(0);
	}
	$(event).val($integration_val);
}