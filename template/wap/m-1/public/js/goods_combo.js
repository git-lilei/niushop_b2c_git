var buy_num = 1;
var current_goods_id = 0;
var current_combo_id = 0;
var maskProductBottomBar = new MaskLayer(".combo-package .widgets-cover", function () {
	$(".combo-package .widgets-cover").removeClass("show");
});
$(function () {
	
	$(".combo-package-content:eq(0) .combo-package-name input").change();
	
	$(".combo-package .sku-wrap .footer").click(function () {
		
		if ($(this).hasClass("disabled")) return;
		
		maskProductBottomBar.hide();
		$(".combo-package .widgets-cover").removeClass("show");
		
	});
	
	$("body").on("click", ".combo-package .sku-list-wrap li .items span", function () {
		$(this).addClass('selected ns-text-color').siblings().removeClass('selected ns-text-color');
		changeGoodsSku();
	});
	
});

function selectSku(goods_id, combo_id) {
	if (uid == undefined || uid == '') {
		location.href = __URL(APPMAIN + "/login/index");
		return;
	}
	maskProductBottomBar.show();
	$(".combo-package .widgets-cover").addClass("show");
	
	api("NsCombopackage.ComboPackage.comboPackageSelectSku", {'goods_id': goods_id}, function (res) {
		var data = res.data;
		if (data) {
			current_goods_id = goods_id;
			current_combo_id = combo_id;
			// if (data.sku_picture_list.length > 0) {
			// 	var html = "";
			// 	for (var i = 0; i < data.sku_picture_list.length; i++) {
			// 		var img = data.sku_picture_list[i];
			// 		for (var j = 0; j < img.length; j++) {
			// 			html += '<input type="hidden" id="spec_picture_id' + img[j].pic_id + '" value="' + __IMG(img[j].pic_cover_mid) + '" />';
			// 		}
			// 	}
			// }
			
			$(".js-thumbnail").attr("src", __IMG(data.img_list[0].pic_cover_mid));
			
			var price = 0;
			if (parseFloat(data.promotion_price) < parseFloat(data.member_price)) {
				price = data.promotion_price;
			} else {
				price = data.member_price;
			}
			
			var integral_html = "";
			if (data.point_exchange_type == 1 && data.point_exchange > 0) {
				integral_html = 'span style="font-size:16px;display: inline-block;vertical-align: middle;">+' + data.point_exchange + combo_lang.goods_integral + '</span>';
			}
			$(".combo-package .sku-wrap .header .main .price").text("NT$" + price + integral_html);
			
			var spec_html = "";
			for (var i = 0; i < data.spec_list.length; i++) {
				var pro_prop = data.spec_list[i];
				spec_html += '<li class="ns-border-color-gray">';
				spec_html += '<h2>' + pro_prop.spec_name + '</h2>';
				spec_html += '<div class="items">';
				for (var j = 0; j < pro_prop.value.length; j++) {
					var value = pro_prop.value[j];
					var class_str = "";
					if (value.selected) class_str += " selected";
					if (value.disabled) class_str += " disabled";
					spec_html += '<span class="ns-border-color-gray ' + class_str + '" data-spec-value-name="' + value.spec_value_name + '" data-id="' + value.spec_id + ':' + value.spec_value_id + '">' + value.spec_value_name + '</span>';
				}
				spec_html += '</div>';
				spec_html += '</li>';
			}
			
			$(".combo-package .sku-list-wrap").html(spec_html);
			
			var sku_html = '';
			for (var i = 0; i < data.sku_list.length; i++) {
				var sku = data.sku_list[i];
				var picture_list = sku.picture_list[0];
				var picture = "";
				if(picture_list){
					picture = picture_list.pic_cover_mid;
				}
				sku_html += '<input name="goods_sku" type="hidden" value="' + sku.attr_value_items + '" data-sku-id="' + sku.sku_id + '" data-stock="' + sku.stock + '" data-sku-name="' + sku.sku_name + '" data-price="' + sku.price + '" data-picture="' + picture + '"/>';
			}
			
			$("#hidden_point_exchange_type").val(data.point_exchange_type);
			$("#hidden_point_exchange").val(data.point_exchange);
			
			$(".js-sku-list").html(sku_html);
			$(".combo-package .sku-wrap .header .main .stock").text(combo_lang.goods_stock + data.stock + combo_lang.goods_piece);
			if (data.stock == 0) $(".combo-package .sku-wrap .footer").addClass("disabled ns-text-color-gray");
			changeGoodsSku();
		}
	}, false);
}

function changeGoodsSku() {
	
	//匹配当前选中的产品规格，找到sku_id
	var sku_length = $(".combo-package .sku-list-wrap li").length;//应选中规格数量
	var current_sku_length = $('.combo-package .sku-list-wrap li span.selected').length;//实际选中规格数量
	
	var current_goods_sku_name = [];
	var sku_id = 0;
	
	if ($("input[name='goods_sku']").length > 1) {
		$("input[name='goods_sku']").each(function () {
			
			var value = $(this).val();
			var match_sku_count = 0;//匹配规格数量
			current_goods_sku_name = [];//每次匹配时都要清空
			
			$('.widgets-cover .sku-list-wrap span.selected:not(.disabled)').each(function () {
				if (value.indexOf($(this).data("id")) > -1) {
					match_sku_count++;
					current_goods_sku_name.push($(this).parent().prev().text() + ":" + $(this).text());
				}
			});
			
			if (sku_length == match_sku_count && current_sku_length == match_sku_count) {
				sku_id = $(this).attr("data-sku-id");
				return false;
			}
		});
	} else {
		sku_id = $("input[name='goods_sku']").attr("data-sku-id");
	}
	
	var current_sku = $("input[name='goods_sku'][data-sku-id=" + sku_id + "]");
	
	if (current_goods_sku_name.length > 0) {
		var html = '已選擇 ';
		for (i in current_goods_sku_name) {
			html += '<span>' + current_goods_sku_name[i] + '</span>';
		}
		$(".combo-package .sku-wrap .header .main .sku-info").html(html);
	} else {
		$(".combo-package .sku-wrap .header .main .sku-info").html("");
	}
	
	$(".combo-package .sku-wrap .header .main .stock").text(combo_lang.goods_stock + current_sku.data("stock") + combo_lang.goods_piece);
	
	var point_text = "";
	// if ($("#hidden_point_exchange_type").val() == 1 && $("#hidden_point_exchange").val() > 0) {
	// 	point_text = "+" + $("#hidden_point_exchange").val() + combo_lang.goods_integral;
	// }
	$(".combo-package .sku-wrap .header .main .price").text("NT$" + current_sku.data("price") + point_text);
	
	//切换商品规格，更新数据
	if (current_combo_id > 0 && current_goods_id > 0) {
		$(".combo-package-content[data-id='" + current_combo_id + "'] .goods-info input[data-goods-id='" + current_goods_id + "']")
		.attr("data-stock", current_sku.data("stock"))
		.attr("data-price", current_sku.data("price"))
		.attr("data-sku-name", current_goods_sku_name.toString())
		.val(sku_id);
		$(".combo-package-content[data-id='" + current_combo_id + "'] .goods-info[data-goods-id='" + current_goods_id + "'] .select-sku span").text("已選規格：" + current_goods_sku_name.toString());
		$(".combo-package-content[data-id='" + current_combo_id + "'] .goods-info[data-goods-id='" + current_goods_id + "'] .price").text("NT$" + current_sku.data("price"));
	}
	
	//判断是否有SKU主图
	if (current_sku.data("picture")) {
		$(".js-thumbnail").attr("src", __IMG(current_sku.data("picture")));
	}
	
}

$(".combo-package-content .combo-package-name input").change(function () {
	
	var id = $(this).val();
	var package_price = parseFloat($(".combo-package-content[data-id='" + id + "']").data("package-price").toString()); //套餐价
	var is_disabled = false;//是否禁用
	var original_price = 0;//商品原价
	
	$(".combo-package-content[data-id='" + id + "'] .goods-info").each(function () {
		if ($(this).find("input").data("price")) {
			original_price += parseFloat($(this).find("input").data("price").toString());
			$(this).find(".price").text("NT$" + $(this).find("input").data("price"));
		}
		if ($(this).find("input").data("stock") == 0) is_disabled = true;
		$(this).find(".select-sku span").text("已選規格：" + $(this).find("input").data("sku-name"));
	});
	
	var save_the_price = parseFloat(original_price - package_price); //节省价
	save_the_price = save_the_price < 0 ? 0 : save_the_price;
	$("#package_price").text("NT$" + package_price.toFixed(2));
	$("#original_price").text("NT$" + original_price.toFixed(2));
	$("#save_the_price").text(combo_lang.save_the_price + "NT$" + save_the_price);
	
	if (is_disabled) $(".combo-package footer button").addClass("disabled ns-text-color-gray");
	else $(".combo-package footer button").removeClass("disabled ns-text-color-gray");
	
});

$("#js-immediate-purchase").click(function () {
	if ($(this).attr("class") == "btn-jiesuan-disabled") {
		return false;
	}
	if (uid == undefined || uid == '') {
		location.href = __URL(APPMAIN + "/login/index");
	}
	var combo_id = $(".combo-package-content .combo-package-name input[type='radio']:checked").val();
	var goods_sku_list = new Array();
	$(".combo-package-content[data-id='" + combo_id + "'] .goods-info input").each(function () {
		goods_sku_list.push($(this).val() + ":" + buy_num);
	});
	
	var order_type = 1;// 1 普通订单	4 拼团订单	6 预售订单	7 砍价订单
	var promotion_type = 1;//1 组合套餐	2 团购	3 砍价
	var data = JSON.stringify({
		order_type: order_type,
		goods_sku_list: goods_sku_list.toString(),
		promotion_type: promotion_type,
		promotion_info: {
			combo_package_info: {
				combo_package_id: combo_id,
				buy_num: buy_num
			}
		}
	});
	
	$.ajax({
		type: 'post',
		url: __URL(APPMAIN + "/order/addOrderCreateData"),
		dataType: "JSON",
		data: {data: data},
		success: function (res) {
			location.href = __URL(APPMAIN + "/order/payment");
		}
	});
});