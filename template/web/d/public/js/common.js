// 公共验证规则
var regex = {
	mobile: /^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[0135678]|9[89])\d{8}$/,
	email: /^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/,
	chinese_characters: /.*[\u4e00-\u9fa5]+.*$/
};

var zui_message = null;

$(function () {
	
	//右侧导航
	$('.right-sidebar .menu > a').hover(function () {
		$(this).find('.text').addClass('text-hover');
		$(this).find('.item-icon-box').addClass('ns-bg-color');
		$(this).find('.sidebar-num').addClass('ns-text-color').removeClass('ns-bg-color');
	}, function () {
		$(this).find('.text').removeClass('text-hover');
		$(this).find('.item-icon-box').removeClass('ns-bg-color');
		$(this).find('.sidebar-num').removeClass('ns-text-color').addClass('ns-bg-color');
	});
	
	$(".ns-header>nav .category>ul>li").hover(function () {
		$(".ns-header > nav .category > ul li .item-left a").removeClass("ns-text-color");
		$(this).find(".item-left a").addClass("ns-text-color");
		$(this).addClass("active").siblings().removeClass("active");
		if ($(this).find("ul").length) {
			$(this).css("width", "211px");
		}
	}, function () {
		$(".ns-header>nav .category ul li").removeClass("active");
		if ($(this).find("ul").length) {
			$(this).css("width", "");
		}
		$(".ns-header > nav .category > ul li .item-left a").removeClass("ns-text-color");
	});
	
	$(".ns-header .middle .ns-search button").click(function () {
		var obj = $(".ns-header .middle .ns-search input");
		var keywords = obj.val();
		if ($.trim(keywords).length == 0 || $.trim(keywords) == "请输入关键词") {
			keywords = obj.attr("data-search-words");
		}
		keywords = keywords.replace(/</g, "&lt;").replace(/>/g, "&gt;");
		$(obj).val(keywords);
		if (keywords == null) {
			keywords = "";
		}
		
		location.href = __URL(SHOPMAIN + '/goods/lists?keyword=' + keywords);
	});
	
	$(".right-sidebar .menu.back-top").click(function () {
		$('body,html').animate({
			scrollTop: 0
		}, 500);
	});
	
	//检测当前是PC还是WAP
	if ($("#hidden_default_client").val() != "") {
		$("body").resize(function () {
			checkTerminal();
		});
		checkTerminal();
	}
	
	//选中导航
	var matching_count = 0;//匹配导航次数
	$(".ns-header > nav .menu li a").each(function () {
		var href = $(this).attr("href");
		if (location.href == href) {
			$(this).addClass("ns-border-color ns-text-color").removeClass("ns-border-color-hover ns-text-color-hover");
			matching_count++;
		}
	});
	
	//如果一次也没有匹配到，默认选中首页
	if (matching_count == 0) {
		if ($(".ns-header > nav .menu li a[href^='" + SHOPMAIN + "/index" + "']").length == 1) {
			$(".ns-header > nav .menu li a[href^='" + SHOPMAIN + "/index" + "']").addClass("ns-border-color ns-text-color").removeClass("ns-border-color-hover ns-text-color-hover");
		}
	}
	
	refreshCart();
});

function logout() {
	api("System.Member.logout", {}, function (res) {
		show(lang_base.quit_successfully + "！");
		location.reload();
	});
}

//顶部刷新购物车
function refreshCart() {
	if (uid == "") {
		$(".ns-header .middle .ns-cart .list").html('<div class="empty"><span class="ns-text-color-gray">亲，购物车中没有商品哟~</span></div>');
		return;
	}
	api('System.Goods.cartList', {}, function (res) {
		var data = res.data;
		if (res.code == 0) {
			if (!data) return;
			var total = 0;
			var cart_list = data.cart_list;
			if (cart_list != [] && cart_list.length > 0) {
				var h = '';
				h += '<div class="mn-c-box">';
				for (var i = 0; i < cart_list.length; i++) {
					h += '<div class="item ns-border-color-gray">';
					h += '<a href="javascript:deleteCart(' + cart_list[i].cart_id + ');" class="del ns-bg-color-gray">×</a>';
					h += '<a href="' + __URL(SHOPMAIN + '/goods/detail?goods_id=' + cart_list[i].goods_id) + '" target="_blank" class="goods-pic pull-left">';
					
					if (cart_list[i]["picture_info"] != null) {
						h += '<img src="' + __IMG(cart_list[i]["picture_info"]["pic_cover_big"]) + '" alt="' + cart_list[i].goods_name + '">';
					} else {
						h += '<img src="' + DEFAULT_GOODS_IMG + '"  alt="' + cart_list[i].goods_name + '">';
					}
					
					h += '</a>';
					h += '<div class="pull-left">';
					h += '<a href="' + __URL(SHOPMAIN + '/goods/detail?goods_id=' + cart_list[i].goods_id) + '" target="_blank" class="goods-name">' + cart_list[i].goods_name + '</a>';
					h += '<p class="ns-text-color">￥' + cart_list[i].price + 'x' + cart_list[i].num + '</p>';
					h += '</div>';
					h += '</div>';
					total += cart_list[i].price * cart_list[i].num;
				}
				total = total.toFixed(2);
				h += '<div class="total">';
				h += '<span>共' + cart_list.length + '种商品，总金额' + total + '元</span>';
				h += '<a href="' + __URL(SHOPMAIN + "/goods/cart") + '" target="_blank" class="ns-bg-color">去购物车结算</a>';
				h += '</div>';
				h += '</div>';
				
				$(".js-sidebar-cart-trigger .sidebar-num").text((cart_list.length > 99) ? 99 : cart_list.length);
			} else {
				h = '<div class="empty"><span class="ns-text-color-gray">亲，购物车中没有商品哟~</span></div>';
			}
			$(".ns-header .middle .ns-cart .list").html(h);
		} else {
			$(".ns-header .middle .ns-cart .list").html('<div class="empty"><span class="ns-text-color-gray">亲，购物车中没有商品哟~</span></div>');
		}
	});
}

function api(method, param, callback, async) {
	// async true为异步请求 false为同步请求
	var async = async != undefined ? async : true;
	$.ajax({
		type: 'post',
		url: __URL(SHOPMAIN + "/index/ajaxapi"),
		dataType: "JSON",
		async: async,
		data: {method: method, param: JSON.stringify(param)},
		success: function (res) {
			if (callback) callback(res);
		}
	});
}

/**
 * 外部js获取语言包接口
 * 创建时间：2018年12月28日09:17:31
 */
function lang(data, callback) {
	$.ajax({
		type: 'post',
		url: __URL(SHOPMAIN + "/index/langapi"),
		dataType: "JSON",
		async: false,
		data: {data: data.toString()},
		success: function (res) {
			if (callback) callback(res);
		}
	});
}

function __URL(url) {
	url = url.replace(SHOPMAIN, '');
	url = url.replace(APPMAIN, 'wap');
	if (url == '' || url == null) {
		return SHOPMAIN;
	} else {
		var str = url.substring(0, 1);
		if (str == '/' || str == "\\") {
			url = url.substring(1, url.length);
		}
		if ($("#niushop_rewrite_model").val() == 1 || $("#niushop_rewrite_model").val() == true) {
			
			if (url.indexOf('goods/detail?goods_id=') != -1) {
				url = url.replace('goods/detail?goods_id=', 'goods-');
				return SHOPMAIN + '/' + url + '.html';
			}
			
			if (url.indexOf('goods/sku?sku_id=') != -1) {
				url = url.replace('goods/sku?sku_id=', 'sku-');
				return SHOPMAIN + '/' + url + '.html';
			}
		}
		var action_array = url.split('?');
		//检测是否是pathinfo模式
		url_model = $("#niushop_url_model").val();
		if (url_model == 1 || url_model == true) {
			var base_url = SHOPMAIN + '/' + action_array[0];
			var tag = '?';
		} else {
			var base_url = SHOPMAIN + '?s=/' + action_array[0];
			var tag = '&';
		}
		if (action_array[1] != '' && action_array[1] != null) {
			if ($("#niushop_rewrite_model").val() == 1 || $("#niushop_rewrite_model").val() == true) {
				return base_url + '.html' + tag + action_array[1];
			} else {
				return base_url + tag + action_array[1];
			}
			
		} else {
			return base_url;
		}
	}
}

/**
 * 处理图片路径
 */
function __IMG(img_path) {
	var path = "";
	if (img_path != undefined && img_path != "") {
		if (img_path.indexOf("http://") == -1 && img_path.indexOf("https://") == -1) {
			path = UPLOAD + "\/" + img_path;
		} else {
			path = img_path;
		}
	}
	return path;
}

//时间戳转时间类型
function timeStampTurnTime(timeStamp) {
	if (timeStamp > 0) {
		var date = new Date();
		date.setTime(timeStamp * 1000);
		var y = date.getFullYear();
		var m = date.getMonth() + 1;
		m = m < 10 ? ('0' + m) : m;
		var d = date.getDate();
		d = d < 10 ? ('0' + d) : d;
		var h = date.getHours();
		h = h < 10 ? ('0' + h) : h;
		var minute = date.getMinutes();
		var second = date.getSeconds();
		minute = minute < 10 ? ('0' + minute) : minute;
		second = second < 10 ? ('0' + second) : second;
		return y + '-' + m + '-' + d + ' ' + h + ':' + minute + ':' + second;
	} else {
		return "";
	}
	
	//return new Date(parseInt(time_stamp) * 1000).toLocaleString().replace(/年|月/g, "/").replace(/日/g, " ");
}

//消息弹出框，因为每次都是重新new的，所以会弹出多次
function show(m, o) {
	if (o == null) o = {placement: 'center'};
	if (zui_message == null) {
		zui_message = new $.zui.Messager(o);
	}
	zui_message.show(m);
}

//通过广告位关键字获取广告代码
function getAdv(ap_keyword) {
	var result = '';
	api("System.Shop.shopAdvCodeQuery", {'ap_keyword': ap_keyword}, function (res) {
		result = res.data;
	}, false);
	return result;
}

//删除购物车中的商品  flag:是否刷新当前页面，
function deleteCart(id, flag) {
	if (confirm("您确实要把该商品移出购物车吗？")) {
		api('System.Goods.deleteCart', {"cart_id_array": id}, function (res) {
			var data = res.data;
			if (data > 0) {
				show("操作成功");
				refreshCart();//刷新购物车
				if (flag) location.reload();
			}
		});
	}
}

//检测PC还是WAP端
function checkTerminal() {
	if ((navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i)) && window.screen.availWidth < 768) {
		//跳到手机端
		$("#go_mobile").show();
	} else {
		$("#go_mobile").hide();
	}
}

//跳转到wap端
function locationWap() {
	$.ajax({
		type: 'post',
		url: __URL(SHOPMAIN + "/index/deleteClientCookie"),
		dataType: "JSON",
		success: function (res) {
			location.href = __URL(SHOPMAIN);
		}
	});
	
}

//检测商品限购，是否允许购买
function getGoodsPurchaseRestrictionForCurrentUser(goods_id, num, callBack) {
	api('System.Goods.goodsPurchaseRestriction', {"goods_id": goods_id, "num": num}, function (res) {
		if (res.code == 0) callBack(res.data);
	}, false);
}

// 跳转错误页
function jumpError(title, message) {
	if (window.sessionStorage) {
		sessionStorage.setItem('errorMsg', JSON.stringify({title: title, message: message}));
	}
	location.href = __URL(SHOPMAIN + '/index/errorTemplate');
}

// 获取省
function getProvince(ele, id) {
	api('System.Address.province', {}, function (res) {
		var data = res.data;
		var html = '<option value="0">请选择省</option>';
		if (data.length > 0) {
			for (i = 0; i < data.length; i++) {
				var item = data[i];
				if (id != undefined && id == item.province_id) {
					html += '<option value="' + item.province_id + '" selected>' + item.province_name + '</option>';
				} else {
					html += '<option value="' + item.province_id + '">' + item.province_name + '</option>';
				}
			}
		}
		$(ele).html(html);
	})
}

// 获取市
function getCity(ele, pid, id) {
	api('System.Address.city', {"province_id": pid}, function (res) {
		var data = res.data;
		var html = '<option value="0">请选择市</option>';
		if (data.length > 0) {
			for (i = 0; i < data.length; i++) {
				var item = data[i];
				if (id != undefined && id == item.city_id) {
					html += '<option value="' + item.city_id + '" selected>' + item.city_name + '</option>';
				} else {
					html += '<option value="' + item.city_id + '">' + item.city_name + '</option>';
				}
			}
		}
		$(ele).html(html);
	})
}

// 获取区县
function getDistrict(ele, pid, id) {
	api('System.Address.district', {"city_id": pid}, function (res) {
		var data = res.data;
		var html = '<option value="0">请选择区/县</option>';
		if (data.length > 0) {
			for (i = 0; i < data.length; i++) {
				var item = data[i];
				if (id != undefined && id == item.district_id) {
					html += '<option value="' + item.district_id + '" selected>' + item.district_name + '</option>';
				} else {
					html += '<option value="' + item.district_id + '">' + item.district_name + '</option>';
				}
			}
		}
		$(ele).html(html);
	})
}