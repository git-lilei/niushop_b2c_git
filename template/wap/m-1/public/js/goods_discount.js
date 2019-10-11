$(function () {
	showCategorySecond(0, 0, 1);
	countDown();
	
	//滑动到底部加载
	$(window).scroll(function () {
		var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
		var content_box_height = parseFloat($(".group-list-box").height());
		if (totalheight - content_box_height >= 50) {
			if (is_load) {
				var page = parseInt($("#page").val()) + 1;//页数
				var total_page_count = $("#page_count").val(); // 总页数
				var sear_type = $("#sear_type").val();
				if (page > total_page_count) {
					return false;
				} else {
					showCategorySecond(0, 0, page);
				}
			}
		}
	});
	
	var swiper = new Swiper('.swiper-container', {
		pagination: '.swiper-pagination',
	});
});

var is_load = true;

function showCategorySecond(obj, category_id, page) {
	//设置选中效果
	if ($(obj).length != 0) {
		$(".custom-tag-list-side-menu li a").removeClass("selected");
		$(obj).addClass("selected");
	}
	if (is_load) {
		is_load = false;
		api("System.Goods.goodsDiscountList", {page_index: page, category_id: category_id}, function (res) {
			var data = res.data;
			is_load = true;
			$("#page_count").val(data['page_count']);
			$("#page").val(page);
			if (page == 1) {
				var list_html = '';
			} else if (page > 1) {
				var list_html = $('.group-list').html();
			}
			if (data['data'].length == 0) {
				$('.group-list').html('<p class="empty ns-text-color-gray"><img src="' + WAPIMG + '/wap_nodata.png"><br>Sorry！' + lang_discount.goods_no_goods_you_want + '…</p>');
			} else {
				for (key in data['data']) {
					var item = data['data'][key];
					var img = "";
					if (item.picture != null) {
						img = item.pic_cover_small;
					}
					list_html += '<li class="ns-border-color-gray-fadeout-50">'
						+ '<div class="p-img">'
						+ '<a href="' + __URL(APPMAIN + '/goods/detail?goods_id=' + item.goods_id) + '" title="' + item.goods_name + '">'
						+ '<img src="' + __IMG(img) + '" class="lazy_load">'
						+ '</a>'
						+ '<div class="brand-time ns-bg-color-gray-shade-20">'
						+ '<i></i>'
						+ '<span class="settime" starttime="' + timeStampTurnTime(item.start_time) + '" endtime="' + timeStampTurnTime(item.end_time) + '"></span>'
						+ '</div>'
						+ '</div>'
						+ '<span class="brand-name">' + item.goods_name + '</span>'
						+ '<div class="brand-info">'
						+ '<div class="brand-info-left">'
						+ '<p class="b-price ns-text-color">' + item.display_price + '</p>';
					if (item.market_price > 0) {
						list_html += '<p class="buyer ns-text-color-gray"><s>NT$' + item.market_price + lang_discount.element + '</s></p>';
					}
					list_html += '</div>';
					list_html += '</div></li>';
				}
				$('.group-list').html(list_html);
			}
			countDown();
		});
	}
}

//倒计时函数
function countDown() {
	$(".settime").each(function (i) {
		var self = $(this);
		var end_date = this.getAttribute("endTime"); //结束时间字符串
		if (end_date != undefined && end_date != '') {
			var end_time = new Date(end_date.replace(/-/g, '/')).getTime();//月份是实际月份-1
			var sys_second = (end_time - $("#ms_time").val()) / 1000;
			if (sys_second > 1) {
				sys_second -= 1;
				var day = Math.floor((sys_second / 3600) / 24);
				var hour = Math.floor((sys_second / 3600) % 24);
				var minute = Math.floor((sys_second / 60) % 60);
				var second = Math.floor(sys_second % 60);
				self.html(day + lang_discount.days + (hour < 10 ? "0" + hour : hour) + lang_discount.hours + (minute < 10 ? "0" + minute : minute) + lang_discount.minutes + (second < 10 ? "0" + second : second) + lang_discount.second);
			}
			var timer = setInterval(function () {
				if (sys_second > 1) {
					sys_second -= 1;
					var day = Math.floor((sys_second / 3600) / 24);
					var hour = Math.floor((sys_second / 3600) % 24);
					var minute = Math.floor((sys_second / 60) % 60);
					var second = Math.floor(sys_second % 60);
					self.html(day + lang_discount.days + (hour < 10 ? "0" + hour : hour) + lang_discount.hours + (minute < 10 ? "0" + minute : minute) + lang_discount.minutes + (second < 10 ? "0" + second : second) + lang_discount.second);
				} else {
					self.html(lang_discount.activity_over + "!");
					clearInterval(timer);
				}
			}, 1000);
		}
	});
}