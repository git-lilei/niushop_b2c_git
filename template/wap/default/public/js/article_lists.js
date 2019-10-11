$(document).ready(function () {
	getArticleList(1);
	//计算最大偏移量 10是边距
	var max_offset_left = $(".article-class li:last-child").offset().left + $(".article-class li:last-child").innerWidth() - $(".article-class").innerWidth() - 10;
	var offset_left = 0;
	$(".article-class li").click(function () {
		if (!$(this).hasClass("active")) {
			var _index = $(this).index();
			var _active_index = $(".article-class li.active").index();
			var center_width = $(".article-class").innerWidth() / 2;
			var _offset_left = $(this).offset().left;
			var _oneself_width = $(this).innerWidth() / 2;
			if (_index > _active_index) {
				//向左偏移
				offset_left += (_offset_left - center_width + _oneself_width);
			} else {
				//向右偏移
				offset_left -= (center_width - _offset_left - _oneself_width);
			}
			offset_left = offset_left < 0 ? 0 : offset_left; // 最小偏移量
			offset_left = offset_left > max_offset_left ? max_offset_left : offset_left; //最大偏移量
			$(".article-class").animate({"scrollLeft": offset_left}, 500) //设置偏移量;
		}
		$(".article-class li").removeClass("active");
		$(this).addClass("active");
		var article_class_id = $(this).data("class-id");
		$("#class_id").val(article_class_id);
		getArticleList(1);
	});
	
	$(".article-class li a").click(function () {
		$(".article-class li a").removeClass("ns-text-color ns-border-color");
		$(this).addClass("ns-text-color ns-border-color");
	});
});

var is_load = true;
var article_img = $("#article_img").val();
function getArticleList(page_index) {
	$("#page").val(page_index);
	if (is_load) {
		is_load = false;
		var condition = {};
		if ($("#class_id").val() > 0) {
			condition = {"nca.class_id": $("#class_id").val()};
		}
		api("System.Article.articleList", {
			page_index: page_index,
			condition: condition,
			order: "article_id desc"
		}, function (res) {
			var data = res.data;
			$("#page_count").val(data['page_count']);
			if (page_index == 1) {
				var article_list_html = '';
			} else if (page_index > 1) {
				var article_list_html = $('.article-list-container').html();
			}
			if (data['data'].length > 0) {
				for (var i = 0; i < data['data'].length; i++) {
					var item = data['data'][i];
					article_list_html += '<a href="' + __URL(APPMAIN + '/article/detail?article_id=' + item['article_id']) + '">';
					article_list_html += '<li class="article-item">';
					article_list_html += '<div class="item-thumbnail">';
					if(item['image']){
						article_list_html += '<img src="' + __IMG(item['image']) + '" class="lazy_load">';
					}else{
						article_list_html += '<img src="' + __IMG(article_img) + '" class="lazy_load">';
					}
					
					article_list_html += '</div>';
					article_list_html += '<div class="item-container">';
					article_list_html += '<h3 class="item-title">' + item['title'] + '</h3>';
					article_list_html += '<p class="item-respondent">' + item['author'] + '</p>';
					article_list_html += '<div>';
					article_list_html += '<span class="speech-item-tag ns-text-color-gray ns-border-color-gray">' + item['name'] + '</span>';
					article_list_html += '<span class="participation ns-text-color-gray">' + item['click'] + '人(阅)</span>';
					article_list_html += '</div>';
					article_list_html += '</div>';
					article_list_html += '</li>';
					article_list_html += '</a>';
				}
			} else {
				article_list_html += '<p class="no-article ns-text-color-gray"><img src="' + WAPIMG + '/wap_nodata.png" height="60"><br>该分类下暂时没有文章！</p>';
			}
			$('ul.article-list-container').html(article_list_html);
			is_load = true;
		});
	}
}

//滑动到底部加载
$(".article-list-content").scroll(function () {
	var totalheight = parseFloat($(".article-list-content").height()) + parseFloat($(".article-list-content").scrollTop());
	var content_box_height = parseFloat($(".article-list-container").height());
	if (content_box_height - totalheight <= 20) {
		if (is_load) {
			var page = parseInt($("#page").val()) + 1;//页数
			var total_page_count = $("#page_count").val(); // 总页数
			if (page > total_page_count) {
				return false;
			} else {
				getArticleList(page);
			}
		}
	}
});