$(function () {
	getLickHistory("guessYouLike");
	getLickHistory("history");
	$(".like-history-container .wrap-head .tab li").mouseover(function (event) {
		$('.like-history-container>.carousel').hide();
		var type = $(this).attr('data-type');
		getLickHistory(type);
		$("#" + type).show();
	});
});

//查询猜你喜欢、浏览历史
function getLickHistory(type) {
	if (type == "guessYouLike") {
		api('System.Goods.guessMemberLikes', {}, function (res) {
			var data = res['data'];
			if (data['data'].length > 0) {
				var html = "";
				for (var i = 0; i < data['data'].length; i++) {
					if (i % 5 == 0 && i == 0) {
						if (i == 0) {
							html += "<div class='item active'>";
						} else {
							html += "<div class='item'>";
						}
						html += "<div class='box'>";
					}
					html += "<div class='product-item ns-border-color-gray-shade-20'>";
					html += "<div class='p-img'>";
					html += "<a target='_blank' href='" + __URL(SHOPMAIN + '/goods/detail?goods_id=' + data['data'][i]['goods_id']) + "'>";
					html += "<img width='160' height='160' src='" + __IMG(data['data'][i]['pic_cover_mid']) + "' alt='" + data['data'][i]['goods_name'] + "'>";
					html += "</a>";
					html += "</div>";
					html += "<div class='p-name'>";
					html += "<a target='_blank' href='" + __URL(SHOPMAIN + '/goods/detail?goods_id=' + data['data'][i]['goods_id']) + "'>" + data['data'][i]['goods_name'] + "</a>";
					html += "</div>";
					html += "<div class='p-price'>";
					html += "<strong class='ns-text-color'>";
					html += "<em>￥</em><i>" + data['data'][i]['price'] + "</i>";
					html += "</strong>";
					html += "</div>";
					html += "<div class='p-btn'>";
					html += "<a href='" + __URL(SHOPMAIN + '/goods/detail?goods_id=' + data['data'][i]['goods_id']) + "' class='add-cart ns-border-color ns-text-color'>查看详情</a>";
					html += "</div>";
					html += "</div>";
					if (i % 5 == 4 && i != 0) {
						html += "</div>";
						html += "</div>";
						if (i != (data['data'].length - 1)) {
							html += "<div class='item'>";
							html += "<div class='box'>";
						}
					}
					if (i == (data['data'].length - 1)) {
						html += "</div>";
						html += "</div>";
					}
				}
				if (data['data'].length > 5) {
					var html_w = "";
					html_w += "<a class='left carousel-control' href='#guessYouLike' data-slide='prev'>";
					html_w += "<span class='icon icon-chevron-left'></span>";
					html_w += "</a>";
					html_w += "<a class='right carousel-control' href='#guessYouLike' data-slide='next'>";
					html_w += "<span class='icon icon-chevron-right'></span>";
					html_w += "</a>";
					$("#guessYouLike .carousel-container .switch-box").html(html_w);
				}
			}
			$("#guessYouLike .carousel-container .carousel-inner").html(html);
			
		});
	} else {
		api('System.Member.memberHistory', {}, function (data) {
			
			if (data['data'].length > 0) {
				var html = "";
				for (var i = 0; i < data['data'].length; i++) {
					if (i % 5 == 0 && i == 0) {
						if (i == 0) {
							html += "<div class='item active'>";
						} else {
							html += "<div class='item'>";
						}
						html += "<div class='box'>";
					}
					html += "<div class='product-item ns-border-color-gray-shade-20'>";
					html += "<div class='p-img'>";
					html += "<a target='_blank' href='" + __URL(SHOPMAIN + '/goods/detail?goods_id=' + data['data'][i]['goods_id']) + "'>";
					html += "<img width='160' height='160' src='" + __IMG(data['data'][i]['pic_cover_mid']) + "' alt='" + data['data'][i]['goods_name'] + "'>";
					html += "</a>";
					html += "</div>";
					html += "<div class='p-name'>";
					html += "<a target='_blank' href='" + __URL(SHOPMAIN + '/goods/detail?goods_id=' + data['data'][i]['goods_id']) + "'>" + data['data'][i]['goods_name'] + "</a>";
					html += "</div>";
					html += "<div class='p-price'>";
					html += "<strong class='ns-text-color'>";
					html += "<em>￥</em><i>" + data['data'][i]['price'] + "</i>";
					html += "</strong>";
					html += "</div>";
					html += "<div class='p-btn'>";
					html += "<a href='" + __URL(SHOPMAIN + '/goods/detail?goods_id=' + data['data'][i]['goods_id']) + "' class='add-cart ns-border-color ns-text-color'>查看详情</a>";
					html += "</div>";
					html += "</div>";
					if (i % 5 == 4 && i != 0) {
						html += "</div>";
						html += "</div>";
						if (i != (data['data'].length - 1)) {
							html += "<div class='item'>";
							html += "<div class='box'>";
						}
					}
					if (i == (data['data'].length - 1)) {
						html += "</div>";
						html += "</div>";
					}
				}
				if (data['data'].length > 5) {
					var html_w = "";
					html_w += "<a class='left carousel-control' href='#history' data-slide='prev'>";
					html_w += "<span class='icon icon-chevron-left'></span>";
					html_w += "</a>";
					html_w += "<a class='right carousel-control' href='#history' data-slide='next'>";
					html_w += "<span class='icon icon-chevron-right'></span>";
					html_w += "</a>";
					
					$("#history .carousel-container .switch-box").html(html_w);
				}
				$("#history .empty-wai").html("");
				$("#history.history .carousel-container .carousel-inner").html(html);
			} else {
				var html = "";
				html += "<div class='empty'>您今日还没有浏览过任何商品！</div>"
				
				$("#history .empty-wai").html(html);
			}
		});
	}
}

function guessMemberLikes(page_index) {
	var params = {
		"page_index": page_index,
		"page_size": 3
	};
	$("#see_page").val(page_index);
	api("System.Goods.guessMemberLikes", params, function (res) {
		var data = res['data'];
		if (data['data'].length > 0) {
			$("#see_count").val(data['page_count']);
			var html = "";
			for (var i = 0; i < data['data'].length; i++) {
				html += "<li>";
				html += "<div>";
				html += "<a href='" + __URL(SHOPMAIN + '/goods/detail?goods_id=' + data['data'][i]['goods_id']) + "'>";
				html += "<img src='" + __IMG(data['data'][i]['pic_cover_mid']) + "'/>";
				html += "</a>";
				html += "</div>";
				html += "<p class='goods-name'>" + data['data'][i]['goods_name'] + "</p>";
				html += "<p class='goods-price ns-text-color'>￥" + data['data'][i]['price'] + "</p>";
				html += "</li>";
			}
			$(".new-right .see").html(html);
		}
	});
}

function refresh() {
	var page = $("#see_page").val();
	var page_count = $("#see_count").val();
	
	if (page + 1 > page_count) {
		page_index = 1;
	} else {
		page_index = parseInt(page) + 1;
	}
	guessMemberLikes(page_index);
}