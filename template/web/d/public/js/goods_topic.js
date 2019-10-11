/* 浏览历史与猜你喜欢 */
function clear_history() {
	api('System.Member.deleteMemberHistory', {}, function (res) {
		if (res.data > 0) {
			show('浏览记录已清除!');
			document.getElementById('history_list').innerHTML = '您已清空最近浏览过的商品';
		}
	})
}

function change_like() {
	var p_count = 6;
	var li_len = $('#user_like>li').length;
	if (li_len > p_count) {
		if (!$('#user_like>li:eq(' + (p_count - 1) + ')').is(':hidden') && li_len >= p_count) {
			$('#user_like>li:gt(' + (p_count - 1) + '):lt(' + (p_count * 2) + ')').show();
			$('#user_like>li:lt(' + (p_count) + ')').hide();
		} else if (!$('#user_like>li:eq(' + (p_count * 2 - 1) + ')').is(':hidden') && li_len >= (p_count * 2)) {
			$('#user_like>li:gt(' + (p_count * 2 - 1) + ')').show();
			$('#user_like>li:lt(' + (p_count * 2) + ')').hide();
		} else if (!$('#user_like>li:eq(' + (p_count * 3 - 1) + ')').is(':hidden') || li_len >= p_count) {
			$('#user_like>li:lt(' + (p_count) + ')').show();
			$('#user_like>li:gt(' + (p_count - 1) + ')').hide();
		}
	}
}

//鼠标经过浏览历史与猜你喜欢切换js
$('.browse-history-tab .tab-span').mouseover(function () {
	$(this).addClass('ns-text-color').siblings('.tab-span').removeClass('ns-text-color');
	$('.browse-history-line').stop().animate({
		'left': $(this).position().left,
		'width': $(this).outerWidth()
	}, 500);
	$('.browse-history-other').find('a').eq($(this).index()).removeClass('none').siblings('a').addClass('none');
	$('.browse-history-inner ul').eq($(this).index()).removeClass('none').siblings('ul').addClass('none');
	$(".browse-history-inner img.lazy_load").lazyload({
		effect: "fadeIn", //淡入效果
		skip_invisible: false
	})
});