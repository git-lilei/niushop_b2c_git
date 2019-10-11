$(function () {
	
	$(".package-info .tit-wrap ul").click(function () {
		if ($(this).find("a").hasClass('open')) {
			$(this).find("a").removeClass('open');
			$(this).parents(".package-info").find(".logistics-trace").slideUp();
		} else {
			$(this).find("a").addClass('open');
			$(this).parents(".package-info").find(".logistics-trace").slideDown();
		}
	});
	
	$(".package-info").each(function () {
		var curr = $(this),
			express_id = $(this).attr("express-id");
		api('System.Order.orderExpressMessageList', {"express_id": express_id}, function (res) {
			if (res.code == 0) {
				var html = "",
					data = res.data;
				if (data["Success"]) {
					for (var i = 0; i < data["Traces"].length; i++) {
						if (i == 0) {
							html += '<li class="first ns-border-color-gray">';
						} else {
							html += '<li class="ns-border-color-gray">';
						}
						html += '<i class="node-icon"></i>';
						html += '<a href="javascript:;" target="_blank">' + data["Traces"][i]["AcceptStation"] + '</a>';
						html += '<div class="ftx-13">' + data["Traces"][i]["AcceptTime"] + '</div>';
						html += '</li>';
					}
				} else {
					html += '<div class="logistics-tip"><div class="content">' + data["Reason"] + '</div></div>';
				}
				curr.find('.logistics-trace ul').html(html);
			}
		})
	});
	
});