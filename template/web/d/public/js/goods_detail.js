$(function () {
	
	init();
	
	//左侧销量排行榜
	getGoodsRankList("ng.sales desc", function (res) {
		var h = '';
		if (res.data.length > 0) {
			for (var i = 0; i < res.data.length; i++) {
				var goods = res.data[i];
				h += '<li>';
				h += '<div class="p-img">';
				h += '<a href="' + __URL(SHOPMAIN + '/goods/detail?goods_id=' + goods.goods_id) + '" title="' + goods.goods_name + '" target="_blank">';
				h += '<img src="' + __IMG(goods.pic_cover_mid) + '" />';
				h += '</a>';
				h += '</div>';
				
				h += '<div class="p-name">';
				h += '<a href="' + __URL(SHOPMAIN + '/goods/detail?goods_id=' + goods.goods_id) + '" target="_blank" title=" ' + goods.goods_name + '">' + goods.goods_name + '</a>';
				h += '</div>';
				
				h += '<div class="p-price ns-text-color">￥' + goods.promotion_price + '</div>';
				h += "</li>";
			}
		} else {
			h += '<li align="center">暂无商品</li>';
		}
		
		$("#tab_sale_ranking ul").html(h);
		
	});
	
	//左侧收藏量排行榜
	getGoodsRankList("ng.collects desc", function (res) {
		var h = '';
		if (res.data.length > 0) {
			for (var i = 0; i < res.data.length; i++) {
				var goods = res.data[i];
				h += '<li>';
				h += '<div class="p-img">';
				h += '<a href="' + __URL(SHOPMAIN + '/goods/detail?goods_id=' + goods.goods_id) + '" title="' + goods.goods_name + '" target="_blank">';
				h += '<img src="' + __IMG(goods.pic_cover_mid) + '" />';
				h += '</a>';
				h += '</div>';
				
				h += '<div class="p-name">';
				h += '<a href="' + __URL(SHOPMAIN + '/goods/detail?goods_id=' + goods.goods_id) + '" target="_blank" title=" ' + goods.goods_name + '">' + goods.goods_name + '</a>';
				h += '</div>';
				
				h += '<div class="p-price ns-text-color">￥' + goods.promotion_price + '</div>';
				h += "</li>";
			}
		} else {
			h += '<li align="center">暂无商品</li>';
		}
		
		$("#tab_collect_ranking ul").html(h);
		
	});
	
	//左侧精品
	api("System.Goods.recommendGoodsList", {page_size: 2}, function (res) {
		var list = res.data;
		if (list) {
			var h = "";
			if (list.length > 0) {
				for (var i = 0; i < list.length; i++) {
					var goods = list[i];
					h += '<li>';
					h += '<a target="_blank" href="' + __URL(SHOPMAIN + '/goods/detail?goods_id=' + goods.goods_id) + '" class="p-img">';
					h += '<img src="' + __IMG(goods.pic_cover_mid) + '" />';
					h += '</a>';
					h += '<a target="_blank" href="' + __URL(SHOPMAIN + '/goods/detail?goods_id=' + goods.goods_id) + '" class="p-name">' + goods.goods_name + '</a>';
					h += '<div class="p-price ns-text-color">￥' + goods.promotion_price + '</div>';
					h += '</li>';
				}
				$(".hot-product ul").html(h);
			} else {
				h += '<li align="center">暂无商品</li>';
				$(".hot-product").hide();
			}
		}
		
		$(".hot-product ul").html(h);
		
	});
	
	//切换不同类型的商品咨询列表
	$(".consult-nav li").click(function () {
		$(this).addClass("selected ns-text-color-hover").siblings().removeClass("selected ns-text-color-hover");
		goodsConsultList($(this).data("type"));
	});
	
	$("#buy_number").change(function () {
		var numObj = $(this),
			num = parseInt(numObj.val()),
			max_buy = numObj.data('max-buy'),
			min_buy = numObj.data('min-buy');
		
		if (num <= min_buy && min_buy > 0) {
			num = min_buy
		} else if (num <= 0 && min_buy == 0) {
			num = 1;
		} else if (num > max_buy) {
			num = max_buy;
		}
		numObj.val(num);
		calculatePrice(num, "#price");
		calculatePrice(num, "#member_price");
	});
	
	$(".increase,.decrease").click(function () {
		var numObj = $("#buy_number"),
			num = parseInt(numObj.val()),
			max_buy = numObj.data('max-buy'),
			min_buy = numObj.data('min-buy'),
			_this = $(this);
		
		if (_this.attr('data-operator') == '+') {
			// 加
			if (num < max_buy) {
				num += 1;
			} else {
				return;
			}
			
		} else if (_this.attr('data-operator') == '-') {
			// 减
			if ((num > min_buy && min_buy > 0) || (num > 1 && min_buy == 0)) {
				num -= 1;
			} else {
				return;
			}
		}
		numObj.val(num);
		calculatePrice(num, "#price");
		calculatePrice(num, "#member_price");
	});
	
	$(".combo-package-promotion-buy").click(function () {
		var combo_id = $(this).attr("data-combo-id");
		var curr_id = $(this).attr("data-curr-id");
		if (uid == '') {
			location.href = __URL(SHOPMAIN + "/login/index");
		} else {
			location.href = __URL(SHOPMAIN + "/goods/combo?combo_id=" + combo_id + "&curr_id=" + curr_id);
		}
	});
	
	//切换组合套餐
	$(".combo-package-promotion nav ul li").click(function () {
		$(".combo-package-promotion nav ul li").removeClass("selected");
		$(this).addClass("selected");
		var data_combo_id = $(this).attr("data-combo-id");
		$(".combo-package-promotion div.tab-content").hide();
		$(".combo-package-promotion div.tab-content[data-combo-id='" + data_combo_id + "']").show();
	});
	
	//商品评价
	$('.rating-type ul li').click(function () {
		$('.rating-type ul li a').removeClass("selected ns-text-color-hover");
		$(this).find('a').addClass('selected ns-text-color-hover');
		getGoodsComments(1);
	});
	
	//选择配送地址
	$("body").on("click", ".delivery .region-list .tab-content > .tab-pane li a", function () {
		var parent = $(this).parent().parent();
		parent.find("a").removeClass("selected ns-text-color-hover");
		$(this).addClass("selected ns-text-color-hover");
		
		var v = $(this).text();
		var province_id = $(this).data("province-id");
		var city_id = $(this).data("city-id");
		var district_id = $(this).data("district-id");
		if (province_id) {
			$(".delivery .region-list .nav-tabs > li a[href='#tab_provinces'] span").text(v).attr("data-province-id", province_id);
			getCity(province_id);
		}
		if (city_id) {
			$(".delivery .region-list .nav-tabs > li a[href='#tab_city'] span").text(v).attr("data-city-id", city_id);
			getDistrict(city_id);
		}
		if (district_id) {
			
			$(".delivery .region-list .nav-tabs > li a[href='#tab_district'] span").text(v).attr("data-district-id", district_id);
			
			var current_province_id = 0;
			var current_city_id = 0;
			var current_district_id = 0;
			
			var region = "";
			$(".delivery .region-list .nav-tabs > li a[href^='#tab_'] span").each(function () {
				region += $(this).text();
				if ($(this).attr("data-province-id")) {
					current_province_id = $(this).attr("data-province-id");
				}
				if ($(this).attr("data-city-id")) {
					current_city_id = $(this).attr("data-city-id");
				}
				if ($(this).attr("data-district-id")) {
					current_district_id = $(this).attr("data-district-id");
				}
			});
			
			$(".region-selected span").text(region);
			
			var goods_sku_list = sku_id + ":" + $("#hidden_min_buy").val();
			$('.region-list').hide();
			
			api('System.Goods.shippingFeeByIp', {
				"goods_id": goods_id,
				"goods_sku_list": goods_sku_list,
				"province_id": current_province_id,
				"city_id": current_city_id,
				"district_id": current_district_id
			}, function (res) {
				var data = res.data;
				var html = "";
				if (data.express != null && data.express != "") {
					if (typeof data.express == "string") {
						html = '快递：' + data.express;
					} else if (typeof data.express == "object") {
						html = "<select>";
						for (var i = 0; i < data.express.length; i++) {
							html += '<option value="' + data.express[i].co_id + '">' + data.express[i].company_name + '&nbsp;&nbsp;&nbsp;¥' + data.express[i].express_fee + '</option>';
						}
						html += "</select>";
					}
				} else {
					html = "本地区暂不支持配送";
				}
				$(".js-shipping-name").html(html);
			});
		}
	});
	
	var region_time = null;
	$('.region-selected,.region-list').mouseover(function () {
		clearTimeout(region_time);
		$('.region-list').show();
	});
	$('.region-selected,.region-list').mouseout(function () {
		region_time = setTimeout(function () {
			$('.region-list').hide();
		}, 200);
	});
	
	/**
	 * 立即购买
	 */
	$(".js-buy-now").click(function () {
		if (uid == "") {
			location.href = __URL(SHOPMAIN + "/login/index");
			return;
		}
		
		if ($(this).hasClass("disabled")) return;
		$(this).addClass("disabled");
		
		if (sku_id == null || sku_id == "") return;
		
		//检测商品限购，是否允许购买
		getGoodsPurchaseRestrictionForCurrentUser(goods_id, $("#buy_number").val(), function (purchase) {
			if (purchase.code > 0) {
				var order_type = $("#hidden_order_type").val() ? $("#hidden_order_type").val() : 1;// 1 普通订单	4 拼团订单	6 预售订单	7 砍价订单
				var promotion_type = $("#hidden_promotion_type").val();//1 组合套餐	2 团购	3 砍价	4 积分兑换
				var data = JSON.stringify({
					order_type: order_type,
					goods_sku_list: sku_id + ":" + $("#buy_number").val(),
					promotion_type: promotion_type
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
				
			} else {
				show(purchase.message);				
			}
			
		});
		
	});
	
	/**
	 * 添加购物车
	 */
	$(".js-add-cart").click(function (event) {
		if (uid == "") {
			location.href = __URL(SHOPMAIN + "/login/index");
			return;
		}
		
		if (sku_id == null || sku_id == "") return;
		
		if ($(this).hasClass("disabled")) return;
		$(this).addClass("disabled");
		
		var cart_detail = {
			goods_id: goods_id,
			count: $("#buy_number").val(),
			goods_name: goods_name,
			sku_id: sku_id,
			sku_name: sku_name,
			price: price,
			picture_id: $("#hidden_picture_id").val(),
			shop_name: $("#hidden_shop_name").val()
		};
		
		api('System.Goods.addCart', {"cart_detail": JSON.stringify(cart_detail)}, function (res) {
			var data = res.data;
			if (data.code > 0) {
				var image_url = $(".magnifier-main img").attr("src");
				if (image_url && event && $(".js-sidebar-cart-trigger").size() > 0) {
					// 结束的地方的元素
					var offset = $(".js-sidebar-cart-trigger").offset();
					var flyer = $('<img class="fly-img" src="' + image_url + '">');
					flyer.fly({
						start: {
							left: event.pageX - 20,
							top: event.pageY - $(window).scrollTop()
						},
						end: {
							left: offset.left + 20,
							top: offset.top - $(window).scrollTop() + 50,
							width: 0,
							height: 0
						},
						onEnd: function () {
							this.destory();
						}
					});
				}
				show("加入购物车成功");
				$(".js-add-cart").removeClass("disabled");
				refreshCart();
			} else {
				show(data.message);
			}
			
		});
	});
	
	// 添加收藏
	$(".js-collect-goods").click(function (event) {
		if (uid == '') {
			location.href = __URL(SHOPMAIN + "/login/index");
			return;
		}
		var span = $(this).find("span");
		var num = span.data("collects");
		if (whether_collection == 0) {
			//点击收藏
			api('System.Member.addCollection', {
				"fav_id": goods_id,
				"fav_type": "goods",
				"log_msg": goods_name
			}, function (res) {
				var data = res.data;
				if (data > 0) {
					whether_collection = 1;
					num++;
					span.data("collects", num).text(lang_goods_detail.member_cancel + "（" + num + lang_goods_detail.goods_popularity + "）").addClass("ns-text-color");
				} else {
					show(lang_goods_detail.goods_already_collected);
				}
				$(".js-collect-goods").find("i").addClass("ns-text-color");
			});
		} else if (whether_collection == 1) {
			//取消收藏
			api('System.Member.cancelCollection', {"fav_id": goods_id, "fav_type": "goods"}, function (res) {
				var data = res.data;
				if (data > 0) {
					num--;
					span.data("collects", num).text(lang_goods_detail.goods_collection_goods + "（" + num + lang_goods_detail.goods_popularity + "）").removeClass("ns-text-color");
					$(".js-collect-goods").find("i").removeClass("ns-text-color");
					whether_collection = 0;
				} else {
					show(lang_goods_detail.goods_cancelled_collected);
				}
			});
		}
	});
	
	$(".sku-list .item-line li:not(.disabled)").click(function () {
		var index = $(this).parent().parent().parent().index();
		$(".sku-list .item-line:eq(" + index + ") li a").removeClass("selected");
		$(this).find("a").addClass('selected');
		
		//匹配当前选中的产品规格，找到sku_id
		var sku_length = $(".sku-list .item-line").length;//应选中规格数量
		var current_sku_length = $(".sku-list li a.selected").length;//实际选中规格数量
		var sku_id = 0;
		
		$("input[name='goods_sku']").each(function () {
			var value = $(this).val();
			var match_sku_count = 0;//匹配规格数量
			
			$(".sku-list li a.selected").each(function () {
				if (value.indexOf($(this).parent().data("id")) > -1) match_sku_count++;
			});
			
			if (sku_length == match_sku_count && current_sku_length == match_sku_count) {
				sku_id = $(this).attr("data-sku-id");
				return false;
			}
		});
		
		if(sku_id){
			location.href = __URL(SHOPMAIN + "/goods/sku?sku_id=" + sku_id);
		}else{
			location.href = __URL(SHOPMAIN + "/goods/detail?goods_id=" + goods_id);
		}
	});
	
});

//商品咨询列表
function goodsConsultList(ct_id) {
	api("System.Goods.goodsConsultList", {page_size: 5, goods_id: goods_id, ct_id: ct_id}, function (res) {
		var data = res.data;
		if (data) {
			var h = '';
			if (data.total_count) {
				var list = data.data;
				for (var i = 0; i < list.length; i++) {
					
					var item = list[i];
					var member_name = lang_goods_detail.goods_tourist;
					var goods_consulting_type = '';
					
					if (item.member_name) {
						member_name = item.member_name.replace(item.member_name.substring(1, item.member_name.length), '***');
					}
					
					if (item.ct_id == 1) {
						goods_consulting_type = lang_goods_detail.goods_commodity_consultation;
					} else if (item.ct_id == 2) {
						goods_consulting_type = lang_goods_detail.goods_payment_problem;
					} else {
						goods_consulting_type = lang_goods_detail.goods_invoice_and_warranty;
					}
					
					h += '<li>';
					h += '<dl class="ns-text-color-gray">';
					h += '<dt>' + lang_goods_detail.goods_consulting_user + '：</dt>';
					h += '<dd>';
					h += '<span>' + member_name + '</span>';
					h += '<span>' + lang_goods_detail.goods_consulting_type + '：' + goods_consulting_type + '</span>';
					h += '<time>[' + timeStampTurnTime(item.consult_addtime) + ']';
					h += '</dd>';
					h += '</dl>';
					h += '<dl class="ask-con">';
					h += '<dt>' + lang_goods_detail.goods_consultation_content + '：</dt>';
					h += '<dd>';
					h += '<p>' + item.consult_content + '</p>';
					h += '</dd>';
					h += '</dl>';
					
					<!-- 回复内容s -->
					if (item.consult_reply != "") {
						
						h += '<dl class="reply">';
						h += '<dt>' + lang_goods_detail.goods_merchant_reply + '：</dt>';
						h += '<dd>';
						h += '<p>' + item.consult_reply + '</p>';
						h += '<time>[' + timeStampTurnTime(item.consult_reply_time) + ']</time>';
						h += '</dd>';
						h += '</dl>';
					}
					
					h += '</li>';
				}
				
				$(".js-consult ul").html(h).parent().show();
			} else {
				h += '<div class="empty ns-text-color-gray">' + lang_goods_detail.goods_no_consultation_yet + '</div>';
				$(".js-consult ul").html(h).parent().show();
			}
			
		}
	});
}

// 加载省市县
function getProvince() {
	api('System.Address.province', {}, function (res) {
		var data = res.data;
		if (data) {
			
			var h = '';
			var current_province_id = 0;
			var current_province_name = "";
			for (var i = 0; i < data.length; i++) {
				var selected = "";
				if ($("#hidden_province").val() == data[i].province_name) {
					selected = "selected ns-text-color-hover";
					current_province_id = data[i].province_id;
					current_province_name = data[i].province_name;
				}
				h += '<li><a href="javascript:;" class="' + selected + '" data-province-id="' + data[i].province_id + '">' + data[i].province_name + '</a></li>';
			}
			
			$("#tab_provinces ul").html(h);
			if ($("#hidden_province").val()) {
				getCity(current_province_id);
				$(".delivery .region-list .nav-tabs > li a[href='#tab_provinces'] span").attr("data-province-id", current_province_id);
			}
		}
		
	});
}

//获取城市
function getCity(province_id) {
	api("System.Address.city", {province_id: province_id}, function (res) {
		var data = res.data;
		if (data) {
			var h = '';
			var current_city_id = 0;
			var current_city_name = "";
			for (var i = 0; i < data.length; i++) {
				var selected = "";
				if ($("#hidden_city").val() == data[i].city_name) {
					selected = "selected ns-text-color-hover";
					current_city_id = data[i].city_id;
				}
				current_city_name = data[i].city_name;
				h += '<li><a href="javascript:;" class="' + selected + '" data-city-id="' + data[i].city_id + '">' + data[i].city_name + '</a></li>';
			}
			
			$("#tab_city ul").html(h);
			if ($("#hidden_city").val()) {
				getDistrict(current_city_id);
				$(".delivery .region-list .nav-tabs > li a[href='#tab_city'] span").attr("data-city-id", current_city_id);
			}
			
			$(".delivery .region-list .nav-tabs > li a[href='#tab_city'] span").text(current_city_name);
			
			$("#tab_city").addClass("active").siblings().removeClass("active");
			$(".delivery .region-list .nav-tabs > li:eq(1)").addClass("active").siblings().removeClass("active");
		}
	});
}

function getDistrict(city_id) {
	api("System.Address.district", {city_id: city_id}, function (res) {
		var data = res.data;
		if (data) {
			var h = '';
			for (var i = 0; i < data.length; i++) {
				h += '<li><a href="javascript:;" data-district-id="' + data[i].district_id + '">' + data[i].district_name + '</a></li>';
			}
			$("#tab_district ul").html(h);
			
			$("#tab_district").addClass("active").siblings().removeClass("active");
			$(".delivery .region-list .nav-tabs > li:eq(2)").addClass("active").siblings().removeClass("active");
			$("#hidden_city").val("");
			$("#hidden_province").val("");
		}
	});
}

//领取优惠劵
function coupon_receive(event, coupon_type_id) {
	if (uid == '') {
		location.href = __URL(SHOPMAIN + "/login/index");
	} else {
		api('System.Goods.receiveGoodsCoupon', {"coupon_type_id": coupon_type_id, 'scenario_type': 3}, function (res) {
			var data = res.data;
			if (data > 0) {
				show(lang_goods_detail.congratulations_on_your_success);
			} else if (data == -2010) {
				show("您已领取最大上限！");
			} else if (data == -2011) {
				show(lang_goods_detail.has_brought_over);
			} else if (data == -2019) {
				show("您已领取最大上限！");
			} else {
				show(res['message']);
			}
		})
	}
}

//计算阶梯优惠后的价格
function calculatePrice(num, obj) {
	if ($(obj) == undefined) return;
	var goods_ladder_preferential_list = $("#goods_ladder_preferential_list").val();
	var arr = JSON.parse(goods_ladder_preferential_list);
	var price = parseFloat($(obj).attr("data-price"));
	if (arr.length > 0) {
		for (var i = 0; i < arr.length; i++) {
			if (num >= arr[i]['quantity']) {
				price -= arr[i]['price'];
				break;
			}
		}
	}
	$(obj).text("￥" + price.toFixed(2));
}

function getGoodsEvaluateCount() {
	api("System.Goods.goodsEvaluateCount", {goods_id: goods_id}, function (res) {
		var data = res.data;
		if (data) {
			$(".js-evaluate-count").text(data.evaluate_count);
			$(".js-evaluate-imgs-count").text(data.imgs_count);
			$(".js-evaluate-praise-count").text(data.praise_count);
			$(".js-evaluate-center-count").text(data.center_count);
			$(".js-evaluate-bad-count").text(data.bad_count);
		}
	});
}

function getGoodsRankList(order, callback) {
	api("System.Goods.goodsRankList", {page_size: 5, category_id: category_id, order: order}, function (res) {
		if (res.data) {
			if (callback) callback(res.data);
		}
	});
}

/**
 * 分页显示
 * @param {Object} page_index
 */
function getGoodsComments(page_index) {
	var comments_type = $('.rating-type .selected').parent().attr('data-type');
	api("System.Goods.goodsComments", {
		page_index: page_index,
		goods_id: goods_id,
		comments_type: comments_type
	}, function (res) {
		var data = res.data;
		var html = "";
		if (data.data.length > 0) {
			$.each(data.data, function (i, e) {
				var member_name = e.member_name;
				member_name = e.is_anonymous == 1 ? member_name.replace(member_name.substring(1, member_name.length), '***') + lang_goods_detail.anonymous : member_name;
				
				html += '<li class="ns-border-color-gray">';
				html += '<div class="user-info">';
				if (e.user_img != "" && e.user_img != undefined && e.user_img != 0) {
					html += '<img src="' + __IMG(e.user_img) + '" width="25" height="25">';
				} else {
					html += '<img src="' + $("#hidden_default_headimg").val() + '" width="25" height="25">';
				}
				html += '<span>' + member_name + '</span>';
				html += '</div>';
				html += '<div class="evaluate-content">';
				html += '<div class="comment-star star' + e.scores + '"></div>';
				html += '<p class="content">' + e.content + '</p>';
				html += '<div class="pic-list">';
				if (e.image != '') {
					var img = e.image.split(",");
					$.each(img, function (index, vo) {
						html += '<a href="javascript:void(0)" onclick="picInfo(this)" data-toggle="lightbox" target=_black data-group="image-group-1"><img src="' + __IMG(vo) + '" class="ns-border-color-gray ns-border-color-hover" width="48" height="48"></a>';
					});
				}
				
				html += '</div>';
				
				html += '<div class="pic-info ns-border-color-gray">';
				html += '<img onclick="picHide()">';
				html += '</div>';
				
				html += '<div class="clearfix"></div>';
				
				html += '<div class="comment-message">';
				html += '<div class="order-info ns-text-color-gray">';
				
				// if(e.product_sku_name != '') {
				// 	html += '<span>'+e.product_sku_name+'</span>';
				// }
				
				html += '<span>' + timeStampTurnTime(e.addtime) + '</span>';
				html += '</div>';
				html += '</div>';
				
				if (e.explain_first != '') {
					html += '<p class="content common-text-color">' + lang_goods_detail.goods_shopkeeper_replies + "：" + e.explain_first + '</p>';
				}
				
				if (e.again_content != '') {
					html += '<p class="content">' + lang_goods_detail.goods_additional_evaluation + "：" + e.again_content + '</p>';
					
					html += '<div class="pic-list">';
					if (e.again_image != '') {
						var img = e.again_image.split(",");
						$.each(img, function (index, vo) {
							html += '<a href="javascript:void(0)" onclick="picInfo(this)" data-toggle="lightbox" target=_black data-group="image-group-1"><img src="' + __IMG(vo) + '" class="ns-border-color-gray ns-border-color-hover" width="48" height="48"></a>';
						});
					}
					html += '</div>';
					html += '<div class="pic-info">';
					html += '<img onclick="picHide()">';
					html += '</div>';
					html += '<div class="clearfix"></div>';
					
					html += '<div class="comment-message">';
					html += '<div class="order-info">';
					html += '<span>' + timeStampTurnTime(e.again_addtime) + '</span>';
					html += '</div>';
					html += '</div>';
					
					if (e.again_explain != '') {
						html += '<p class="content common-text-color">' + lang_goods_detail.goods_shopkeeper_replies + "：" + e.again_explain + '</p>';
					}
				}
				
				html += '</div>';
				
				html += '</div>';
				html += '</li>';
			});
			html += ' <ul id="evaluate_page" class="pager" data-elements="prev,nav,next,total_page_text" <ul data-rec-per-page="' + PAGE_SIZE + '" data-ride="pager" data-page="' + page_index + '" data-rec-total="' + data.total_count + '"></ul>';
		} else {
			html += '<div class="empty">' + lang_goods_detail.no_comment_yet + '</div>'
		}
		$('.evaluate-list ul').html(html);
		$('#evaluate_page').pager({
			onPageChange: function (state, oldState) {
				if (state.page !== oldState.page && oldState.page != undefined) {
					getGoodsComments(state.page);
				}
			}
		});
	});
}

/**
 * 图片放大
 */
function picInfo(obj) {
	$('.pic-info').hide();
	$('.evaluate-list img.ns-border-color').removeClass('ns-border-color');
	var img = $(obj).find('img').attr('src');
	$(obj).parent().next().show().find('img').attr('src', img);
	$(obj).find('img').addClass('ns-border-color');
}
function picHide() {
	$('.pic-info').hide();
	$('.evaluate-list img.ns-border-color').removeClass('ns-border-color');
}

/**
 * 限时折扣、团购等活动的倒计时
 */
function countDown() {
	var end_time = $(".surplus-time").data('value');
	if (end_time) {
		var _date = ($("#current_time").val() / 1000),
			f = function () {
				if (surplus_time > 0) {
					surplus_time -= 1;
					var day = Math.floor((surplus_time / 3600) / 24);
					var hour = Math.floor((surplus_time / 3600) % 24);
					var min = Math.floor((surplus_time / 60) % 60);
					var second = Math.floor(surplus_time % 60);
					
					if (day > 0) $('#day i').text(day < 10 ? "0" + day : day).show();
					else $('#day').hide();
					
					$('#hour i').text(hour < 10 ? "0" + hour : hour);
					$('#min i').text(min < 10 ? "0" + min : min);
					$('#second i').text(second < 10 ? "0" + second : second);
				} else {
					clearInterval(timer);
					// location.reload();
				}
			},
			surplus_time = end_time - _date,
			timer = setInterval(f, 1000);
		f();
	}
}

function init() {
	
	getGoodsComments(1);
	
	getGoodsEvaluateCount();
	
	goodsConsultList(0);
	
	//限时折扣
	countDown();
	
	if ($("#hidden_is_virtual").val() == 0) {
		//实物商品才会查询运费
		var goods_sku_list = sku_id + ":" + $("#hidden_min_buy").val();
		//定位查询运费
		api('System.Goods.shippingFeeByIp', {
			"goods_id": goods_id,
			"goods_sku_list": goods_sku_list
		}, function (res) {
			var data = res.data;
			if (data.user_location != null) {
				$(".region-selected span").text(data.user_location.province + data.user_location.city);
				$(".delivery .region-list .nav-tabs > li a[href='#tab_provinces'] span").text(data.user_location.province);
				$(".delivery .region-list .nav-tabs > li a[href='#tab_city'] span").text(data.user_location.city);
				$("#hidden_province").val(data.user_location.province);
				$("#hidden_city").val(data.user_location.city);
				getProvince();
			}
			
			var html = "";
			if (data.express != null && data.express != "") {
				if (typeof data.express == "string") {
					html = '快递：' + data.express;
				} else if (typeof data.express == "object") {
					html = "<select>";
					for (var i = 0; i < data.express.length; i++) {
						html += '<option value="' + data.express[i].co_id + '">' + data.express[i].company_name + '&nbsp;&nbsp;&nbsp;¥' + data.express[i].express_fee + '</option>';
					}
					html += "</select>";
				}
			} else {
				html = "本地区暂不支持配送";
			}
			$(".js-shipping-name").html(html);
		});
	}
	
	//品牌信息
	api("System.Goods.goodsBrandInfo",{ brand_id : brand_id },function (res) {
		var data = res.data;
		if(data) $("#tab_attr ul").append('<li title="品牌：' + data.brand_name + '">品牌：' + data.brand_name + '</li>');
	});
	
	// 更新商品点击量
	api("System.Goods.modifyGoodsClicks",{ goods_id : goods_id },function (res) {
	});
	
	// 添加足迹
	api("System.Goods.addGoodsBrowse",{ goods_id : goods_id },function (res) {
	});
}