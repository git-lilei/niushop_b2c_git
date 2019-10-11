/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * ========================================================= Copy right
 * 2015-2025 山西牛酷信息科技有限公司, 保留所有权利。
 * ---------------------------------------------- 官方网址:
 * http://www.niushop.com.cn 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 * 
 * @version : v1.0.0.0 商品规格库存表格构建
 * 
 * 修改时间：2018年3月2日14:26:03	Line：593
 */

/**
 *  规格属性选择数组 
 */
var $specObj = new Array();
/**
 *  规格属性组拼sku数组
 */
var $sku_array=new Array();
/**
 * 临时表 用于存储库存值
 */
var $temp_Obj = new Object();

var $sku_goods_picture = new Array();

/**
 * 删除数组中的指定元素
 * @param arr
 * @param val
 */
function SpliceArrayItem(arr, spec_value_id) {
	for(var i=0; i<arr.length; i++) {
		if(arr[i]["spec_value_id"] == spec_value_id){
			arr.splice(i, 1);
			break;
		}
	}
}

//判断对象中的两个值是否相等
cmp = function(x,y) {
	if (x === y) {
		return true;
	}
	if (!(x instanceof Object) || !(y instanceof Object)) {
		return false;
	}
	if (x.constructor !== y.constructor) {
		return false;
	}
	
	for (var p in x) {
		if ( x.hasOwnProperty( p ) ) {
			if (! y.hasOwnProperty( p ) ) {
				return false;
			}
			if ( x[ p ] === y[ p ] ) {
				continue;
			}
			if (typeof( x[ p ] ) !== "object") {
				return false;
			}
			if (! Object.equals( x[ p ], y[ p ] )) {
				return false;
			}
		}
	}
	for ( p in y ) {
		if ( y.hasOwnProperty( p ) && ! x.hasOwnProperty( p ) ) {
			return false;
		}
	}
	return true;
};

/**
 *  添加或删除属性值时更新到规格数组中
 * @param spec_name
 * @param spec_id
 * @param spec_value
 * @param spec_value_id
 * @param is_selected
 */
function addOrDeleteSpecObj(spec_name , spec_id , spec_value_name , spec_value_id ,spec_show_type, spec_value_data , is_selected){
	var is_have= 0;
	for(var i = 0; i < $specObj.length ; i ++ ){
		if($specObj[i].spec_id == spec_id){
			if(is_selected == 1){
				$specObj[i]["value"].push({"spec_value_name":spec_value_name, "spec_name":spec_name, "spec_id":spec_id,"spec_value_id":spec_value_id,"spec_show_type":spec_show_type, "spec_value_data":spec_value_data});
				is_have = 1;
				//如果此规格现在为默认规格,则为其添加此规格值图片列
				if($(".sku-picture-span[spec_id='"+spec_id+"']").hasClass("sku-picture-active")){
					createSkuPictureBox(spec_id, spec_value_id, spec_name, spec_value_name);
				}
			}else{
				SpliceArrayItem($specObj[i].value , spec_value_id);

				//如果此规格现在为默认规格,则为其删除此规格值图片列
				if($(".sku-picture-span[spec_id='"+spec_id+"']").hasClass("sku-picture-active")){
					$("div[spec_id='"+ spec_id +"'][spec_value_id='"+ spec_value_id +"']").remove();
				}
				if($specObj[i].value.length == 0){
					$specObj.splice(i, 1);
					//如果此规格未选中属性,则删掉这个按钮
					$(".sku-picture-span[spec_id='"+spec_id+"']").remove();
				}
			}
		}
	}
	if(is_selected == 1){
		//第一次选此规格
		if(is_have == 0){
			//给此规格添加对象内部空间 并添加此属性
			var obj_length = $specObj.length;
			$specObj[obj_length] = new Object();
			$specObj[obj_length].spec_name = spec_name;
			$specObj[obj_length].spec_id = spec_id;
			$specObj[obj_length]["value"] = new Array();
			$specObj[obj_length]["value"].push({"spec_value_name":spec_value_name, "spec_name":spec_name, "spec_id":spec_id,"spec_value_id":spec_value_id,"spec_show_type":spec_show_type, "spec_value_data":spec_value_data});	
		
			//为此规格添加按钮
			var html ='<span class="sku-picture-span" spec_id = "'+ spec_id +'">'+ spec_name +'</span>';
			$(".sku-picture-div").append(html);
			$(".sku-picture-dl").show();
		}
	}
}

function compare(property){
    return function(a,b){
        var value1 = a[property];
        var value2 = b[property];
        return value1 - value2;
    }
}

//规格属性值修改
function editSpecValueName(event){
	if(event.flag){
		
		var spec_id = event.spec_id;
		var spec_value_id = event.spec_value_id;
		var spec_value_name = event.spec_value_name;
		var spec_name = event.spec_name;
		var spec_value_data = event.spec_value_data;
		var spec_show_type = event.spec_show_type;
		var is_continue = false;
		for(var i = 0; i < $specObj.length ; i ++ ){
			
			if($specObj[i].spec_id == spec_id){
				$.each($specObj[i]["value"],function(t,m){
					if(m["spec_value_id"] == spec_value_id){
						$specObj[i]["value"][t]["spec_value_name"] = spec_value_name;
						is_continue = true;
						return false;
					}
				});
			}
			if(is_continue){
				break;
			}
		}
		createTable();
	}

}

/**
 * 修改属性展示方式值
 * @param spec
 * @returns
 */
function editSpecValueData(spec){
	if(spec.flag){
		var spec_id = spec.spec_id;
		var spec_value_id = spec.spec_value_id;
		var spec_name = spec.spec_name;
		var spec_value_data = spec.spec_value_data;
		var is_continue = false;
		for(var i = 0; i < $specObj.length ; i ++ ){
			if($specObj[i].spec_id == spec_id){
				$.each($specObj[i]["value"],function(t,m){
					if(m["spec_value_id"] == spec_value_id){
						$specObj[i]["value"][t]["spec_value_data"] = spec_value_data;
					
					}
				});
			}
			if(is_continue){
				break;
			}
			
		}
	}
}

$(".sku-picture-span").live("click",function(){
	$(".sku-picture-box>div").remove();
	var $this = $(this);
	var spec_id = $this.attr("spec_id");
	$specObj = sku_spec_list();
	if($this.hasClass("sku-picture-active")){
		$(".sku-picture-span").removeClass("sku-picture-active");
		$this.removeClass("sku-picture-active");
		$(".sku-picture-dl-box").hide();
	}else{
		for(var i = 0; i < $specObj.length ; i ++ ){
			if($specObj[i]["spec_id"] == spec_id){
				$.each($specObj[i]["value"],function(t,m){
					createSkuPictureBox(m["spec_id"], m["spec_value_id"],m["spec_name"], m["spec_value_name"]);
				});
			}
		}	
		$(".sku-picture-span").removeClass("sku-picture-active");
		$this.addClass("sku-picture-active");
		$(".sku-picture-dl-box").show();
	}
})

//将对象处理成表格数据
function createSkuData($specArray){
	var $length=$specArray.length;
	$sku_array=new Array();
	if($length>0){
		var $spec_value_obj=$specArray[0]["value"];
		$.each($spec_value_obj,function(i,v){
			var $spec_id = v.spec_id
			var $spec_value_id=v.spec_value_id;
			var $spec_value=v.spec_value_name;
			var $spec_name = v.spec_name;
			var $spec_show_type = v.spec_show_type;
			var $sku_obj=new Object();
			$sku_obj.id=$spec_id+":"+$spec_value_id;
			$sku_obj.name=$spec_value;
			$sku_obj.text=$spec_name + "：" + $spec_value;//规格：规格值
			$sku_obj.spec_show_type = $spec_show_type;//
			$sku_obj.spec_show_type = $spec_show_type;
			$sku_array.push($sku_obj);
		});
	}
	for($i=1;$i<$length;$i++){
		$spec_val_obj=$specArray[$i]["value"];
		$length_val=$spec_val_obj.length;
		$sku_copy_array=new Array();
		$.each($sku_array,function(i,v){
			$old_id=v.id;
			$old_name=v.name;
			$text = v.text;
			for($y=0;$y<$length_val;$y++){
				var $spec_id=$spec_val_obj[$y].spec_id;
				var $id=$spec_val_obj[$y].spec_value_id;
				var $name=$spec_val_obj[$y].spec_value_name;
				var $spec_name = $spec_val_obj[$y].spec_name;
				var $spec_show_type = v.spec_show_type;
				$copy_obj=new Object();
				$copy_obj.id=$old_id+";"+$spec_id+":"+$id;
				$copy_obj.name=$old_name+";"+$name;//规格：规格值
				$copy_obj.text=$text + "，" + $spec_name + "：" + $name;
				$copy_obj.spec_show_type = $spec_show_type;//显示方式
				$sku_copy_array.push($copy_obj);
			}
			
		});
		$sku_array=$sku_copy_array;
	}
}

function createTable(){
	//创建一个又关于对象各个子类长度的数组
	if($specObj.length == 0){
		$(".goods-sku-list table tbody").empty();
		$("#txtProductCount").val("").removeAttr("readonly");
		$("#txtProductSalePrice").val(0).removeAttr("readonly");
		$("#txtProductMarketPrice").val(0).removeAttr("readonly");
		$("#txtProductCostPrice").val(0).removeAttr("readonly");
	}else{
		if($("#txtProductCount").attr("readonly") != "readonly"){
			$("#txtProductCount").val("").attr("readonly","readonly");
		}
		if($("#txtProductCostPrice").attr("readonly") != "readonly"){
			$("#txtProductCostPrice").val(0).attr("readonly","readonly");
		}
		if($("#txtProductMarketPrice").attr("readonly") != "readonly"){
			$("#txtProductMarketPrice").val(0).attr("readonly","readonly");
		}
		if($("#txtProductSalePrice").attr("readonly") != "readonly"){
			$("#txtProductSalePrice").val(0).attr("readonly","readonly");
		}
	}
	
	var specArray = new Array();
	var each_num = 0;
	
	$.each($specObj,function(i,v){
		var arr_length = v.value.length;
		var each_spec_name = v.spec_name;
		var spec_name_obj = {"each_length":arr_length, "spec_name":each_spec_name,"value":v.value}
		specArray.push(spec_name_obj);
		if(each_num == 0){
			each_num = arr_length;
		}else{
			each_num = each_num * arr_length;
		}
	});
	
	//将规格数据 转化成sku数据
	createSkuData(specArray);
	
	//建立表格
	var html = "";
	var help_inline_style = 'style="font-size: 12px;color:#F44336;display: none;margin-top: 5px;text-align: left;padding: 2px 0 0 11px;"';
	for(var i = 0; i < $sku_array.length; i ++){
		var child_id_string = $sku_array[i]["id"].toString();
		var child_name_string = $sku_array[i]["name"].toString();
		var text = $sku_array[i]['text'];//规格：规则值
		//将规格,规格值处理成 spec_id,spec_value_id;spec_id,spec_value_id 格式
		if($temp_Obj[child_id_string] == undefined){
			$temp_Obj[child_id_string] = new Object();
			$temp_Obj[child_id_string]["sku_price"] ="0.00";
			$temp_Obj[child_id_string]["market_price"] ="0.00";
			$temp_Obj[child_id_string]["cost_price"] ="0.00";
			$temp_Obj[child_id_string]["stock_num"] ="0";
			$temp_Obj[child_id_string]["volume"] ="0.00";
			$temp_Obj[child_id_string]["weight"] ="0.00";
			$temp_Obj[child_id_string]["code"] ="";
		}
		
		html += '<tr skuid="' + child_id_string + '">';
			html += '<td align="left">' + text + '</td>';
			
			//销售价格
			html += '<td>';
				html += '<input type="text" class="input-common middle js-price" maxlength="10" name="sku_price" value="'+$temp_Obj[child_id_string]["sku_price"]+'"  />';
				html += '<span class="help-inline" ' + help_inline_style + '>销售价最小为 0.01</span>';
			html += '</td>';
			
			//市场价格
			html += '<td>';
				html += '<input type="text" class="input-common middle js-market-price" maxlength="10" name="market_price" value="'+$temp_Obj[child_id_string]["market_price"]+'"/>';
				html += '<span class="help-inline" ' + help_inline_style + '>市场价最小为 0.01</span>';
			html += '</td>';
			
			//成本价格
			html += '<td>';
				html += '<input type="text" class="input-common middle js-cost-price" maxlength="10" name="cost_price" value="'+$temp_Obj[child_id_string]["cost_price"]+'"/>';
				html += '<span class="help-inline" ' + help_inline_style + '>成本价最小为 0.01</span>';
			html += '</td>';
			
			//库存
			html += '<td>';
				html += '<input type="text" class="input-common middle js-stock-num" maxlength="9" name="stock_num" value="'+$temp_Obj[child_id_string]["stock_num"]+'" onkeyup="inputKeyUpNumberValue(this);" onafterpaste="inputAfterPasteNumberValue(this);"/>';
				html += '<span class="help-inline" ' + help_inline_style + '>库存不能为空</span>';
			html += '</td>';

			//体积
			html += '<td>';
				html += '<input type="text" class="input-common middle js-volume" maxlength="9" name="volume" value="'+$temp_Obj[child_id_string]["volume"]+'" onkeyup="inputKeyUpNumberValue(this);"/>';
				html += '<span class="help-inline" ' + help_inline_style + '>体积不能为空</span>';
			html += '</td>';

			//重量
			html += '<td>';
				html += '<input type="text" class="input-common middle js-weight" maxlength="9" name="weight" value="'+$temp_Obj[child_id_string]["weight"]+'" onkeyup="inputKeyUpNumberValue(this);"/>';
				html += '<span class="help-inline" ' + help_inline_style + '>重量不能为空</span>';
			html += '</td>';
			
			//商品编码
			html += '<td><input type="text" class="input-common middle js-code" name="code" value="'+$temp_Obj[child_id_string]["code"]+'"/></td>';
			
		html += '</tr>';
	}
	
	var newArray = new Array();
	$.each(specArray,function(z,x){
		newArray = newArray.concat(x.value);
	});

	var tdObj = $(".goods-sku-list tbody").html(html);
	
	//循环处理库存
	eachInput();
	eachPrice();
	eachMarketPrice();
	eachCostPrice();
}




/**
 * input  只能输入数字
 */
function inputKeyUpNumberValue(event){
	if(event.value.length==1){
		event.value=event.value.replace(/[^0-9]/g,'');
	}else{
		event.value=event.value.replace(/\D/g,'');
	}
}

function inputAfterPasteNumberValue(event){
	if(event.value.length==1){
		event.value=event.value.replace(/[^0-9]/g,'');
	}else{
		event.value=event.value.replace(/\D/g,'');
	}
}

function createSkuPictureBox(spec_id, spec_value_id, spec_name, spec_value_name){
	var sku_picture_array = new Array();
	var is_have= 0;
	for(var i = 0; i < $sku_goods_picture.length ; i ++ ){
		if($sku_goods_picture[i].spec_id == spec_id && $sku_goods_picture[i].spec_value_id == spec_value_id){
			sku_picture_array = $sku_goods_picture[i]["sku_picture_query"];
			is_have = 1;
		}
	}
	//第一次选此规格
	if(is_have == 0){
		//给此规格添加对象内部空间 并添加此属性
		var obj_length = $sku_goods_picture.length;
		$sku_goods_picture[obj_length] = new Object();
		$sku_goods_picture[obj_length]["spec_name"] = spec_name;
		$sku_goods_picture[obj_length]["spec_value_name"] = spec_value_name;
		$sku_goods_picture[obj_length]["spec_value_id"] = spec_value_id;
		$sku_goods_picture[obj_length]["spec_id"] = spec_id;
		$sku_goods_picture[obj_length]["sku_picture_query"] = new Array();
	
	}
	var html = '<div spec_id="'+ spec_id +'" spec_value_id="'+ spec_value_id +'">';
			html += '<h3 class="sku-picture-h3">'+ spec_value_name +'</h3>';
			html += '<div class="controls">';
				html += '<div class="ncsc-goods-default-pic">';
					html += '<div class="goodspic-uplaod" style="padding: 15px;">';
						html += '<div class="sku-img-box" style="min-height:160px;" spec_id="'+ spec_id +'" spec_value_id="'+ spec_value_id +'">';
						if(sku_picture_array.length > 0){
							$.each(sku_picture_array,function(k,v){
									html +='<div class="upload-thumb sku-draggable-element'+ spec_id +'-'+ spec_value_id +' sku-draggable-element">';
										html +='<img nstype="goods_image" src="'+ __IMG(v["pic_cover_mid"]) +'">';
										html +='<input type="hidden" class="sku_upload_img_id" nstype="goods_image" spec_id="'+ spec_id +'" spec_value_id="'+ spec_value_id +'" value="'+ v["pic_id"] +'">';
										html +='<div class="black-bg hide">';
											html +='<div class="sku-off-box">&times;</div>';
										html +='</div>';
									html +='</div>';
							});
						}else{
							html += '<div class="upload-thumb" id="sku_default_uploadimg">';
								html += '<img src="'+ADMINIMG+'/album/default_goods_image_240.gif">';
							html += '</div>';
						}
						html += '</div>';
						html += '<div class="clear"></div>';
						html += '<div class="handle">';
							html += '<div class="ncsc-upload-btn">';
								html += '<a href="javascript:void(0);">';
									html += '<span>';
										html += '<input style="cursor:pointer;font-size:0;" file_type="sku" spec_id="'+ spec_id +'" spec_value_id="'+ spec_value_id +'" type="file"  hidefocus="true" class="input-file" name="file_upload"multiple="multiple" onclick="file_upload(this);" />';
									html += '</span>';
									html += '<p>图片上传</p>';
								html += '</a>';
							html += '</div>';
							html += '<a class="ncsc-btn mt5"  id="sku_img_box" href="javascript:void(0);"spec_id="'+ spec_id +'" spec_value_id="'+ spec_value_id +'">从图片空间选择</a>';
						html += '</div>';
					html += '</div>';
				html += '</div>';
			html += '</div>';
		html += '</div>';
	$(".sku-picture-box").append(html);
	//给规格图片拖动事件
	$('.sku-draggable-element'+ spec_id +'-'+ spec_value_id ).arrangeable();
}

//商品类型改变时,删除规格图片框架
function removeSpecPictureBox(){
	$(".sku-picture-dl").hide();
	$(".sku-picture-div > span").remove();
	$(".sku-picture-dl-box").hide();
	$(".sku-picture-box > div").remove();
}

/**
 * 获取规格表头提示
 * 2017年6月14日 09:22:46
 * @returns {String}
 */
function getGoodsSpecHeaderHtml(){
	var html = '<tr>';
		html += '<td colspan="2">';
			html += '<div style="text-align:left;">';
				html += '<h5 style="margin:0;padding:0;font-weight: normal;color: #FF8400;">操作提示</h5>';
				html += '<p style="color:#FF8400;font-size:12px;margin:0;line-height: 25px;">1、双击规格值进行编辑操作(回车按钮保存)。</p>';
				html += '<p style="color:#FF8400;font-size:12px;margin:0;line-height: 25px;">2、鼠标浮上图片时，可以进行预览。</p>';
			html += '</div>';
		html += '</td>';
	html += '</tr>';
	return html;
}

//创建规格预览列表
function createSkuPreview(){
	var html = getGoodsSpecHeaderHtml();
	if($specObj.length>0){
		for(var i=0;i<$specObj.length;i++){
			var curr_spec = $specObj[i];
			html += '<tr class="js-spec-item goods-sku-block-'+curr_spec.spec_id+'">';
				html += '<td width="10%">' + curr_spec.spec_name + "</td>";
				html += '<td width="85%">';
			
				for(var j=0;j<curr_spec.value.length;j++){
					var curr_spec_value = curr_spec.value[j];
					html += '<article class="goods-sku-item">';
						html += '<span class="selected" data-spec-name="'+curr_spec.spec_name+'"';
						html += ' data-spec-id="'+curr_spec.spec_id+'"';
						if(parseInt(curr_spec_value.show_type) == 2 && curr_spec_value.spec_value_data == ""){
							curr_spec_value.spec_value_data = "#000000";
						}
						html += ' data-spec-value-data="' + curr_spec_value.spec_value_data + '"';
						html += ' data-spec-show-type="' + curr_spec_value.spec_show_type + '"';
						html += ' data-spec-value-id="'+curr_spec_value.spec_value_id+'">';
						html += curr_spec_value.spec_value_name + "</span>";
						
						//显示方式
						switch(parseInt(curr_spec_value.spec_show_type)){
							case 1:
								//文字
								break;
							case 2:
								//颜色
								html += '&nbsp;<i></i>&nbsp;';
								html += '<div>';
									html += '<span style="cursor:default;padding: 10px;border-radius: 0;background:' + (curr_spec_value.spec_value_data == "" ? "#000000" : curr_spec_value.spec_value_data) + ';"></span>';
//								html += '<input type="color" class="input-common-color" name="goods_spec_value'+(i+j)+'" value="' + (curr_spec_value.spec_value_data == "" ? "#000000" : curr_spec_value.spec_value_data) + '">';
								html += '</div>';
								break;
							case 3:
								//图片
								var index = curr_spec.spec_id + curr_spec_value.spec_value_id;
								html += '&nbsp;<i></i>&nbsp;';
								html += '<div class="js-goods-spec-value-img sku-img-check" data-html="true" data-container="body" data-placement="top" data-trigger="manual">';
//								html += '<input type="hidden" id="goods_sku'+index+'" value="'+curr_spec_value.spec_value_data+'" >';
								
								//兼容以前的数据结构
								if(curr_spec_value.spec_value_data_src != null) curr_spec_value.spec_value_data = curr_spec_value.spec_value_data_src;
									
								if(curr_spec_value.spec_value_data != "" && curr_spec_value.spec_value_data != undefined){
									html += '<img src="'+__IMG(curr_spec_value.spec_value_data)+'" id="imggoods_sku'+index+'"/>';
								}else{
									html += '<img src="'+ADMINIMG+'/goods/goods_sku_add.png"  id="imggoods_sku'+index+'"/>';
								}
								html += '</div>';
								break;
						}
					
					html += '</article>';
				}
				html += '</td>';
			html += '</tr>';
		}
		$(".js-goods-sku").html(html).parent().show();
	}else{
		$(".js-goods-sku").empty().parent().hide();
	}
}

//修改商品时 更新$specObj,并编辑页面结构
function updateSpecObjData(){
	
	$specObj = sku_spec_list();
	$(".sku-picture-dl").show();
	$(".sku-picture-dl-box").hide();
	var sel_spec_id = $('.sku-picture-span.sku-picture-active').attr('spec_id');
	$(".sku-picture-div").html('');
	for(var i = 0 ; i <$specObj.length; i++ ){
		
		//编辑是显示所选的规格按钮
		var html ='<span class="sku-picture-span" spec_id = "'+ $specObj[i]["spec_id"] +'">'+  $specObj[i]["spec_name"] +'</span>';
		$(".sku-picture-div").append(html);
	}
	
	$('.sku-picture-span[spec_id="'+sel_spec_id+'"]').trigger('click');
}