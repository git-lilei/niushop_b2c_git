$(function () {
	
	$('.cf-container .cf-tab-item').click(function () {
		$('.cf-container .cf-tab-item').removeClass('selected ns-border-color ns-text-color').addClass("ns-text-color-gray");
		$(this).addClass('selected ns-border-color ns-text-color').removeClass("ns-text-color-gray");
	});
	getMemberVirtualList(0);
	
});

function getMemberVirtualList(type) {
	api("System.Order.virtualCodeList", {type: type}, function (res) {
		var data = res.data;
		var listhtml = '<ul id="not_use" class="clearfix">';
		if (data.length > 0) {
			$('.null_default').hide();
			for (var i = 0; i < data.length; i++) {
				if (data[i]['picture_info'] != '') {
					var img = __IMG(data[i]['picture_info']);
				} else {
					var img = DEFAULT_GOODS_IMG;
				}
				var number = parseInt(data[i]['confine_use_number']) - parseInt(data[i]['use_number']);
				
				if (type == 0) {
					listhtml += '<li><a class="not-use" href="' + __URL(APPMAIN + '/verification/detail?vg_id=' + data[i]['virtual_goods_id']) + '">';
				} else {
					listhtml += '<li><a class="already-use" href="javascript:;">';
				}
				if (data[i]['use_status'] == 0) {
					listhtml += '<img src="' + WAPIMG + '/verification/virtual_member.png"/>';
				} else if (data[i]['use_status'] == 1) {
					listhtml += '<img src="' + WAPIMG + '/verification/virtual_member_ing.png"/>';
				} else {
					listhtml += '<img src="' + WAPIMG + '/verification/virtual_member_ed.png"/>';
				}
				listhtml += '<div class="coupon-price"><i><img src="' + img + '" /></i><p>' + data[i]['goods_name'] + '</p></div>';
				listhtml += '<div class="coupon-lose">有效期:<span>' + data[i]['end_time'] + '</span></div>';
				if (data[i]['confine_use_number'] == 0) {
					listhtml += '<div class="coupon-price-right"><p>可用次数</p><p class="font12">不限次数</p></div>';
				} else {
					listhtml += '<div class="coupon-price-right"><p>可用次数</p><p class="font12">' + number + '</p></div>';
				}
				listhtml += '</a></li>';
			}
			listhtml += '</ul>';
		} else {
			listhtml = '<div class="empty"><span class="ns-text-color-gray">' + lang_code.you_do_not_have_any_virtual_code_yet + '</span></div>';
		}
		$('.com-content').html(listhtml);
		
	});
}