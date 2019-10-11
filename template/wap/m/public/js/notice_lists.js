$(function () {
	getArticleList(1);
	
	//滑动到底部加载
	$(window).scroll(function () {
		var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
		var content_box_height = parseFloat($("#article_list").height());
		if (totalheight - content_box_height >= 40) {
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
});

var is_load = true;

function getArticleList(page) {
	if (is_load) {
		is_load = false;
		api("System.Shop.shopNoticeList", {page_index: page, order: "sort desc,create_time desc"}, function (res) {
			var data = res.data;
			$("#page_count").val(data['page_count']);
			if (page == 1) {
				var article_list_html = '';
			} else if (page > 1) {
				var article_list_html = $('#notice_list').html();
			}
			if (data['data'].length > 0) {
				article_list_html += '<ul class="notice-list">';
				for (var i = 0; i < data['data'].length; i++) {
					article_list_html += '<li class="notice-item ns-border-color-gray">';
					article_list_html += '<p class="box-title"><a href="' + __URL(APPMAIN + '/notice/detail?id=' + data['data'][i]['id']) + '">' + data['data'][i]['notice_title'] + '</a></p>';
					article_list_html += '<p class="box-time ns-text-color-gray">' + timeStampTurnTime(data['data'][i]['create_time']) + '</p>';
					article_list_html += '</li>';
				}
				article_list_html += '</ul>';
			} else {
				article_list_html += '<p class="no-notice-info ns-text-color-gray"><img src="' + WAPIMG + '/wap_nodata.png"><br>暂时没有公告！</p>';
			}
			$('#notice_list').html(article_list_html);
			is_load = true;
		});
	}
}