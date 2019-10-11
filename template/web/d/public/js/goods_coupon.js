var is_have = true;

function coupon_receive(event, coupon_type_id) {
	if (is_have) {
		is_have = false;
		api('System.Member.getCoupon', {'coupon_type_id': coupon_type_id, 'scenario_type': 3}, function (res) {
			var data = res.data;
			if (data > 0) {
				show("恭喜您，领取成功！");
			} else if (data == -2009) {
				$('#mask-layer-login').show();
				$('#layui-layer').show();
			} else if (data == -2010) {
				show("您已领取最大上限！");
				$(event).text('您已领取');
				$(event).attr("onclick", "");
			} else if (data == -2011) {
				$(event).text('已领完');
				$(event).attr("onclick", "").css("line-height", "40px");
			} else if (data == -2019) {
				show("您已领取最大上限！");
				$(event).text('已达上限');
				$(event).attr("onclick", "").css("line-height", "30px");
			} else {
				show(res['message']);
			}
			is_have = true;
		})
	}
}

$('#myPager').pager({
	linkCreator: function (page, pager) {
		return __URL(SHOPMAIN + "goods/coupon?page=" + page);
	}
});