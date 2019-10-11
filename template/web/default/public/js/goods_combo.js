$(function () {
	getData();
	$("#buy_num").change();
});

const real_price = $("#combo_package_price").text();
$("#js-immediate-purchase").click(function () {
	if ($(this).attr("class") == "btn-jiesuan-disabled") return false;
	
	var order_type = 1;// 1 普通订单	4 拼团订单	6 预售订单	7 砍价订单
	var promotion_type = 1;//1 组合套餐	2 团购	3 砍价
	var data = JSON.stringify({
		order_type: order_type,
		goods_sku_list: getData(),
		promotion_type: promotion_type,
		promotion_info: {
			combo_package_info: {
				combo_package_id: $("#hidden_combo_id").val(),
				buy_num: $("#buy_num").val()
			}
		}
	});
	$.ajax({
		type: 'post',
		url: __URL(SHOPMAIN + "/order/addOrderCreateData"),
		dataType: "JSON",
		data: {data: data},
		success: function (res) {
			location.href = __URL(SHOPMAIN + "/member/payment");
		}
	});
});

//选择规格
$(".goods-spec-item").click(function () {
	$(this).siblings(".selected").removeClass("selected ns-border-color").addClass("ns-border-color-gray");
	$(this).addClass("selected ns-border-color").removeClass("ns-border-color-gray");
	getData();
	showSkuPicture($(this));
	CalculatePrice();
});

function showSkuPicture(event) {
	var sku_str = $(event).find(".value-label").attr("id");
	var sku_info = sku_str.split(":");
	var src = $(event).parents("li[data-goods-id]").find("input[data-spec-value-id='sku-pic-" + sku_info[1] + "']").attr("data-big-img");
	if (src != undefined) {
		$(event).parents("li[data-goods-id]").find("#goods_img").attr("src", src);
	}
}

var stock_arr = ""; //库存
var price_arr = ""; //价格
function getData() {
	stock_arr = new Array();
	price_arr = new Array();
	var num = $("#buy_num").val();
	var res = new Array();
	//SKUID:NUM,SKUID:NUM
	$(".container>ul>li[data-goods-id]").each(function () {
		
		var li = this;
		
		var goods_id = $(li).attr("data-goods-id");
		
		if ($(li).find(".goods-spec-item").length > 0) {
			
			var temp = new Array();
			
			$(this).find(".goods-spec-item").each(function () {
				if ($(this).hasClass("selected ns-border-color")) temp.push($(this).find("span").attr("id"));
			});
			
			//比较
			$(".container li[data-goods-id='" + goods_id + "'] input[type='hidden'][id^='goods_sku']").each(function () {
				
				var goods_sku_array = $(this).val().split(";");
				var sku_count = 0;
				var curr_sku_count = 0;
				
				for (var i = 0; i < temp.length; i++) {
					sku_count++;
					if ($.inArray(temp[i], goods_sku_array) != -1) curr_sku_count++;
				}
				
				if (sku_count == curr_sku_count) {
					res.push($(this).attr("skuid") + ":" + num);
					var stock = $(this).attr("stock");
					var price = $(this).attr("price");
					stock_arr.push(stock);//库存
					price_arr.push(price);
					$(".container li[data-goods-id='" + goods_id + "']").find("#goods_stock").text(stock + "件");
				}
			});
		} else {
			stock_arr.push($(li).find("#goods_sku0").attr("stock")); //库存
			res.push($(li).find("#goods_sku0").attr("skuid") + ":" + num);
			price_arr.push($(li).find("#goods_sku0").attr("price"));
		}
	});
	judgeStock();
	return res.toString();
}

$("#buy_num").change(function () {
	judgeStock();
});

function judgeStock(){
	var num = parseInt($("#buy_num").val());
		if(isNaN(num) || num <= 0) num = 1;
		$("#buy_num").val(num);
	
	for (var i = 0; i < stock_arr.length; i++) {
		if (num > stock_arr[i]) {
			$("#js-immediate-purchase").removeClass("btn-settlement ns-bg-color").addClass("btn-settlement-disabled ns-bg-color-gray").attr("disabled", true).css("pointer-events", "none");			break;
		} else {
			$("#js-immediate-purchase").removeClass("btn-settlement-disabled ns-bg-color-gray").addClass("btn-settlement ns-bg-color").removeAttr('disabled').css("pointer-events", "unset");
		}
	}
	CalculatePrice();
}

//计算价格
function CalculatePrice() {
	var num = $("#buy_num").val();
	var page_real_price;
	var original_price = parseFloat(eval(price_arr.join("+"))).toFixed(2);
	original_price = parseFloat(eval(num * original_price)).toFixed(2);
	page_real_price = parseFloat(eval(num * real_price)).toFixed(2);
	var save_the_price = parseFloat(original_price - page_real_price).toFixed(2);
	save_the_price = save_the_price < 0 ? 0 : save_the_price;
	$("#original_price").text(original_price);
	$("#save_the_price").text(save_the_price);
	$("#combo_package_price").text(page_real_price);
}