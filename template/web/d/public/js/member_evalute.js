$(function () {
	
	//搜索
	$('.search .btn').click(function () {
		var searchCont = $(this).prev('.search-input').val();
		if (!searchCont.search(/[\S]+/)) {
			var url = urlBindParams({order_no: searchCont});
			location.href = url;
		} else {
			location.href = SHOPMAIN + '/member/evaluate';
		}
	});
	
	// 链接绑定参数
	function urlBindParams(params) {
		var url = __URL(SHOPMAIN + '/member/evaluate'),
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
});