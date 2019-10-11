$(function () {
	
	//默认选择第一个
	if ($("#hidden_id").val() == '') {
		$(".help .category > ul li:first div").addClass("ns-text-color");
	}
	
	// $(".help .category > ul li > div a").click(function () {
	// 	var class_id = $(this).attr("data-class-id");
	// 	location.href = __URL(SHOPMAIN + "/help/index?class_id=" + class_id);
	// 	return false;
	// });
	
	$(".help .category>ul li div").click(function () {
		if ($(this).next('.dis-no').find('li').length == 0) return;
		$(".help .category>ul li ul").slideUp('fast');
		var children = $(this).next();
		if (children.is(":visible")) {
			$(this).parent('li').parent('ul').find('i').removeClass('icon-sort-down').addClass('icon-sort-down');
			$(this).find('i').addClass('icon-sort-down').removeClass('icon-sort-up');
			children.slideUp('fast');
		} else {
			$(this).parent('li').parent('ul').find('i').removeClass('icon-sort-up').addClass('icon-sort-down');
			$(this).find('i').addClass('icon-sort-up').removeClass('icon-sort-down');
			children.slideDown('fast');
		}
	});
});

//分页
$('#myPager').pager({
	linkCreator: function (page, pager) {
		var params = "page_index=" + page;
		if ($("#hidden_class_id").val() != "") {
			params += "&class_id=" + $("#hidden_class_id").val();
		}
		return __URL(SHOPMAIN + "/help/index?" + params);
	}
});