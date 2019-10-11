$(function () {
	loadingInfo(1, 0);
	
	//滑动到底部加载
	$(window).scroll(function () {
		var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
		var content_box_height = parseFloat($(".collection-goods-list").height());
		if (totalheight - content_box_height >= 80) {
			if (!is_load) {
				var page = parseInt($("#page").val()) + 1;//页数
				var total_page_count = $("#page_count").val(); // 总页数
				var type = $('#type').val();
				if (page > total_page_count) {
					return false;
				} else {
					loadingInfo(page, type);
				}
			}
		}
	});
	
	$(".cf-content li").click(function () {
		$(".cf-content li a").removeClass("selected ns-text-color-hover ns-border-color-hover");
		$(this).find("a").addClass("selected ns-text-color-hover ns-border-color-hover");
		var type = $(this).attr("data-type");
		loadingInfo(1, type);
	});
	
});

var is_load = false;//防止重复加载
function loadingInfo(page_index, type) {
	$("#page").val(page_index);//设置当前页
	$("#type").val(type);//保存当前状态
	if (is_load) return;
	is_load = true;
	api('System.Member.collection', {'page_index': page_index, 'type': type}, function (res) {
		var data = res.data;
		$("#page_count").val(data['page_count']);//总页数
		if (page_index == 1) {
			var html = "";
		} else if (page_index > 1) {
			var html = $('.collection-goods-list').html();
		}
		$(".collection-goods-list").empty();
		if (data['data'].length > 0) {
			for (var i = 0; i < data['data'].length; i++) {
				var item = data['data'][i];
				if (item['goods_id'] != "" && item['goods_id'] != null) {
					var empty = "this.src='" + WAPIMG + "/member/goods_img_empty.png'";
					html += '<div class="goods-info ns-border-color-gray fav_id_' + item['fav_id'] + '">';
					html += '<div class="collection-time">' + item['fav_time'] + '</div>';
					html += '<div class="goods-img" onclick="location.href=\'' + __URL(APPMAIN + '/goods/detail?goods_id=' + item.goods_id) + '\'">';
					html += '<img src="' + __IMG(item.pic_cover_mid) + '" class="pic" onerror=' + empty + '>';
					html += '</div>';
					html += '<div class="data-info">';
					html += '<p class="goods-name" onclick="location.href=\'' + __URL(APPMAIN + '/goods/detail?goods_id=' + item.goods_id) + '\'">' + item['goods_name'] + '</p>';
					html += '<div class="price-share">';
					html += '<span class="price ns-text-color">' + item['display_price'] + '</span>';
					html += '<span class="cancel-collection" onclick="cancelFavorites(' + item['fav_id'] + ',\'goods\',this);">取消收藏</span>';
					html += '</div>';
					html += '</div>';
					html += '</div>';
				}
			}
			$(".collection-goods-list").append(html);
		} else {
			$(".collection-goods-list").html(html);
		}
		var goods_info_width = parseInt($(".goods-info").width());
		var goods_img_width = parseInt($('.goods-img').width());
		var data_info_width = goods_info_width - goods_img_width - 15;
		$(".data-info,.price-share").width(data_info_width);
		favoritesGoodsIsEmpty();
		is_load = false;
	})
}

function cancelFavorites(fav_id, fav_type, obj) {
	api('System.Member.cancelCollection', {'fav_id': fav_id, 'fav_type': fav_type}, function (res) {
		$(".fav_id_" + fav_id).fadeOut();
		toast(lang_member_collection.member_abolish_successful);
		favoritesGoodsIsEmpty();
	});
}

// 判断收藏的商品是否为空
function favoritesGoodsIsEmpty() {
	var favoritesGoodsNum = $('.goods-info').not(":hidden").length;
	if (favoritesGoodsNum == 0) {
		var html = '<div class="collection-goods-empty ns-text-color-gray">您還沒有收藏記錄</div>';
		$(".collection-goods-list").html(html);
	}
}