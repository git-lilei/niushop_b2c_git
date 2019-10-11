$(function () {
	// 点击多选
	$('.goods-list .multiple-selection-btn').click(function () {
		$('.goods-list .wrap').removeClass('multiple-selection');
		$(this).parents('.wrap').addClass('multiple-selection');
	});
	
	// 取消多选
	$('.goods-list .cancel-multiple-selection').click(function () {
		$(this).parents('.wrap').removeClass('multiple-selection');
	});
	
	// 显示更多
	$('.goods-list .more-btn').click(function () {
		var obj = $(this).parents('.wrap').find('ul');
		if (obj.hasClass('all-show')) {
			obj.removeClass('all-show');
			$(this).text('更多');
		} else {
			obj.addClass('all-show');
			$(this).text('收起');
		}
	});
	
	// 取消已选择的筛选项
	$('.goods-list .selected li').click(function () {
		var data = $(this).data();
		switch (data.type) {
			case 'brand':
				delete oldParams.brand_id;
				delete oldParams.brand_name;
				break;
			case 'price':
				delete oldParams.min_price;
				delete oldParams.max_price;
				break;
			case 'spec':
				var oldSpec = oldParams.spec.split(';'),
					spec = data.spec + ':' + data.specValue;
				oldSpec.forEach(function (el, index) {
					if (el == spec) oldSpec.splice(index, 1);
				});
				if (oldSpec.length > 0) oldParams.spec = oldSpec.join(';');
				else delete oldParams.spec;
				break;
			case 'attr':
				var oldAttr = oldParams.attr.split(';'),
					attr = data.attrValue + ',' + data.attrValueName + ',' + data.attrValueId;
				oldAttr.forEach(function (el, index) {
					if (el == attr) oldAttr.splice(index, 1);
				});
				if (oldAttr.length > 0) oldParams.attr = oldAttr.join(';');
				else delete oldParams.attr;
				break;
		}
		var url = urlBindParams(oldParams);
		location.href = url;
	});
	
	// 选择品牌
	$('.goods-list .brand li').click(function () {
		// 多选
		if ($(this).parents('.wrap').hasClass('multiple-selection')) {
			if ($(this).hasClass('active')) {
				$(this).removeClass('active ns-border-color');
				$(this).find('a').removeClass('ns-border-color');
			} else {
				$(this).addClass('active ns-border-color');
				$(this).find('a').addClass('ns-border-color');
			}
		} else {
			// 单选
			var newParams = {
				brand_id: $(this).attr('brand-id'),
				brand_name: $(this).attr('brand-name')
			}
			var url = urlBindParams(paramsUnique(newParams, oldParams));
			location.href = url;
		}
	});
	
	// 品牌多选
	$('.goods-list .brand .confirm-select .btn').click(function () {
		if ($(this).hasClass('cancel-multiple-selection')) {
			$('.goods-list .brand li,.goods-list .brand li a').removeClass('active ns-border-color');
		} else {
			if ($('.goods-list .brand li.active').length > 0) {
				var brand_id_arr = [],
					brand_name_arr = [];
				$('.goods-list .brand li.active').each(function (index, el) {
					brand_id_arr.push($(el).attr('brand-id'));
					brand_name_arr.push($(el).attr('brand-name'));
				});
				var newParams = {
					brand_id: brand_id_arr.join(','),
					brand_name: brand_name_arr.join(',')
				};
				var url = urlBindParams(paramsUnique(newParams, oldParams));
				location.href = url;
			}
		}
	});
	
	// 选择规格 
	$('.goods-list .spec li').click(function () {
		var spec_id = $(this).attr('data-spec-id'),
			spec_value_id = $(this).attr('data-spec-value-id'),
			spec = spec_id + ':' + spec_value_id;
		
		if (oldParams != {} && oldParams.spec != undefined) {
			var oldSpec = oldParams.spec.split(';'),
				is_exits = false;
			
			oldSpec.forEach(function (el, index) {
				var oldSpecValue = el.split(':');
				if (oldSpecValue[0] == spec_id) {
					oldSpec[index] = spec;
					is_exits = true;
				}
			});
			
			if (!is_exits) oldSpec.push(spec);
			var newParams = {
				spec: oldSpec.join(';')
			}
		} else {
			var newParams = {
				spec: spec
			}
		}
		var url = urlBindParams(paramsUnique(newParams, oldParams));
		location.href = url;
	});
	
	// 选择属性 
	$('.goods-list .attr li').click(function () {
		var attr_value = $(this).attr('data-attr-value'),
			attr_value_name = $(this).attr('data-attr-value-name'),
			attr_value_id = $(this).attr('data-attr-value-id'),
			attr = attr_value + ',' + attr_value_name + ',' + attr_value_id;
		
		if (oldParams != {} && oldParams.attr != undefined) {
			var oldAttr = oldParams.attr.split(';'),
				is_exits = false;
			
			oldAttr.forEach(function (el, index) {
				var oldAttrValue = el.split(':');
				if (oldAttrValue[0] == attr_value) {
					oldAttr[index] = attr;
					is_exits = true;
				}
			});
			
			if (!is_exits) oldAttr.push(attr);
			var newParams = {
				attr: oldAttr.join(';')
			}
		} else {
			var newParams = {
				attr: attr
			}
		}
		var url = urlBindParams(paramsUnique(newParams, oldParams));
		location.href = url;
	});
	
	// 选择价格
	$('.goods-list .price li').click(function () {
		var newParams = {
			min_price: $(this).attr('min-price'),
			max_price: $(this).attr('max-price')
		};
		var url = urlBindParams(paramsUnique(newParams, oldParams));
		location.href = url;
	});
	
	// 点击排序
	$('.filter .f-sort a').click(function () {
		var type = $(this).attr('data-type'),
			sort = $(this).attr('data-sort');
		
		if (type != undefined) {
			var newParams = {
				order: type,
				sort: sort
			};
			var url = urlBindParams(paramsUnique(newParams, oldParams));
		} else {
			if (oldParams.obyzd != undefined) {
				delete oldParams.order;
				delete oldParams.sort;
			}
			var url = urlBindParams(oldParams);
		}
		location.href = url;
	});
	
	// 显示有货 显示包邮
	$('.filter .f-feature li a').click(function () {
		var type = $(this).attr('data-type'),
			status = $(this).attr('data-status'),
			newParams = {};
		newParams[type] = status;
		if(type == 'shipping_fee' && status == 1){
			delete newParams[type];
			delete oldParams[type];
		}
		var url = urlBindParams(paramsUnique(newParams, oldParams));
		location.href = url;
	});
	
	// 点击选择自定义价格区间
	$('.filter .f-price a').click(function (event) {
		if ($(this).hasClass('empty')) {
			$(this).parents('.f-price').find('.form-control').val('');
		} else if ($(this).hasClass('confirm')) {
			var min_price = $(this).parents('.f-price').find('.form-control:eq(0)').val();
			var max_price = $(this).parents('.f-price').find('.form-control:eq(1)').val();
			
			if (min_price == '' || max_price == '') {
				show("请输入价格区间");
				return;
			}
			var priceArr = [
				parseInt(min_price),
				parseInt(max_price)
			];
			if (priceArr[0] != '' && priceArr[1] != '') {
				priceArr = priceArr.sort(sortNumber);
				var newParams = {
					min_price: priceArr[0],
					max_price: priceArr[1]
				};
				var url = urlBindParams(paramsUnique(newParams, oldParams));
				location.href = url;
			}
		}
	});
	
	$(".product-list > ul > li .p-scroll .ps-wrap ul li").mouseover(function () {
		var max_src = $(this).attr("data-max-src");
		var index = $(this).attr("data-index");
		$(".product-list > ul > li > .p-img[data-index='" + index + "'] a img").attr("src", max_src);
		$(this).find("img").addClass("ns-border-color").removeClass("ns-border-color-gray").parent().siblings().find("img").removeClass("ns-border-color").addClass("ns-border-color-gray");
	});
	
	var curr_scroll_index = 0;//当前滚动位置
	$(".product-list > ul > li .p-scroll > span").click(function () {
		var operation = $(this).attr("data-operation");
		var index = $(this).attr("data-index");
		var length = $(".product-list > ul > li[data-index='" + index + "'] .p-scroll .ps-wrap ul li").length;
		var scroll_count = length - 5;//可滚动的次数
		var visible_count = 5;//可见数量
		if (operation == "next") {
			
			if (scroll_count > 0) {
				if (curr_scroll_index < scroll_count) {
					curr_scroll_index++;
					var left = curr_scroll_index * 36;
					var curr_li_index = parseInt(visible_count + curr_scroll_index - 1);
					$(".product-list > ul > li[data-index='" + index + "'] .p-scroll .ps-wrap ul").css("left", "-" + left + "px");
					$(".product-list > ul > li[data-index='" + index + "'] .p-scroll .ps-wrap ul li:eq(" + curr_li_index + ") img").addClass("ns-border-color").removeClass("ns-border-color-gray").parent().mouseover().siblings().find("img").addClass("ns-border-color-gray").removeClass("ns-border-color");
					$(".product-list > ul > li[data-index='" + index + "'] .p-scroll .ps-prev").removeClass("disabled");
				}
				if (curr_scroll_index >= scroll_count) {
					$(this).addClass("disabled");
				}
			}
		} else {
			if (curr_scroll_index > 0) {
				curr_scroll_index--;
				var left = parseInt(curr_scroll_index * 36);
				var curr_li_index = parseInt(visible_count + curr_scroll_index - 1);
				$(".product-list > ul > li[data-index='" + index + "'] .p-scroll .ps-wrap ul").css("left", "-" + left + "px");
				$(".product-list > ul > li[data-index='" + index + "'] .p-scroll .ps-wrap ul li:eq(" + curr_li_index + ") img").addClass("ns-border-color").removeClass("ns-border-color-gray").parent().mouseover().siblings().find("img").addClass("ns-border-color-gray").removeClass("ns-border-color");
				$(".product-list > ul > li[data-index='" + index + "'] .p-scroll .ps-next").removeClass("disabled");
				if (curr_scroll_index == 0) {
					$(this).addClass("disabled");
				}
			}
		}
	});
	
});

// 参数重新赋值
function paramsUnique(newParams, oldParams) {
	if (oldParams.length != {}) {
		return Object.assign({}, oldParams, newParams);
	}
	return newParams;
}

// 链接绑定参数
function urlBindParams(params) {
	var url = __URL(SHOPMAIN + '/goods/lists'),
		url_model = $('#niushop_url_model').val(); // 路由模式 0:兼容模式 1:pathinfo模式
	
	if (params.length != {}) {
		if (url_model == 1) {
			var paramsUrl = '';
			$.each(params, function (index, el) {
				paramsUrl += '&' + index + '=' + el;
			});
			paramsUrl = paramsUrl.replace("&", "?");
			url += paramsUrl;
			
		} else {
			var paramsUrl = '';
			$.each(params, function (index, el) {
				paramsUrl += '&' + index + '=' + el;
			});
			url += paramsUrl;
		}
	}
	return url;
}

// 限制输入的必须为正整数
function positiveInteger(value) {
	if (value.length == 1) {
		value = value.replace(/[^1-9]/g, '')
	} else {
		value = value.replace(/\D/g, '')
	}
	return value;
}

// 数字排序
function sortNumber(a, b) {
	return a - b;
}

// 商品收藏
// function collectionGoods(goods_id, type, goods_name, event) {
// 	var apiUrl = type ? 'System.Member.addCollection' : 'System.Member.cancelCollection';
//
// 	api(apiUrl, {"fav_id": goods_id, "fav_type": 'goods', 'log_msg': goods_name}, function (res) {
// 		if (res.data > 0) {
// 			if (type) {
// 				$(event).find('i').addClass('ns-text-color');
// 				$(event).find('span').text('已收藏');
// 			} else {
// 				$(event).find('i').removeClass('ns-text-color');
// 				$(event).find('span').text('收藏');
// 			}
// 		}
// 	}, false)
// }