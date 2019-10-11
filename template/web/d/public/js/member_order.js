$(function () {
	
	// 高级搜索
	$('.search-more-btn,.senior-search .close-btn').click(function () {
		if ($('.senior-search').hasClass('hide')) $('.senior-search').removeClass('hide');
		else $('.senior-search').addClass('hide');
	});
	
	// 订单状态
	$('.order-top .order-status a').click(function () {
		var newParams = {
			status: $(this).data('status')
		};
		var url = urlBindParams(paramsUnique(newParams, oldParams));
		location.href = url;
	});
	
	// 订单类型
	$('.order-type-list li').click(function (event) {
		var newParams = {
			order_type: $(this).data('type')
		};
		if (oldParams.status != undefined) delete oldParams.status;
		var url = urlBindParams(paramsUnique(newParams, oldParams));
		location.href = url;
	});
	
	// 搜索
	$('.order-search .search-btn').click(function () {
		var searchCont = $(this).prev('.search-input').val();
//		if(!searchCont.search(/[\S]+/)){
		var url = urlBindParams({order_no: searchCont});
		location.href = url;
//		}
	})
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
	var url = __URL(SHOPMAIN + '/member/order'),
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