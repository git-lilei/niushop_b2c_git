$(function () {
	
	//默认进入打开第一个分类下的第一个文章
	if ($("#hidden_class_id").val() != '') {
		if ($(".help .category > ul li ul li[data-class-id='" + $("#hidden_class_id").val() + "']").parent().hasClass("dis-no")) {
			$(".help .category > ul li ul li[data-class-id='" + $("#hidden_class_id").val() + "']").parent().removeClass("dis-no").prev().find("i").addClass("icon-sort-up").removeClass("icon-sort-down");
		}
	} else {
		$(".help .category > ul li:first div").addClass("ns-text-color");
	}
	
	$(".help .category > ul li > div a").click(function () {
		var class_id = $(this).attr("data-class-id");
		location.href = __URL(SHOPMAIN + "/article/lists?class_id=" + class_id);
		return false;
	});
	
	$(".help .category>ul li div").click(function () {
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

$('#myPager').pager({
	linkCreator: function (page, pager) {
		var params = "page=" + page;
		if ($("#hidden_class_id").val() != "") {
			params += "&class_id=" + $("#hidden_class_id").val();
		}
		return __URL(SHOPMAIN + "/article/lists?" + params);
	}
});