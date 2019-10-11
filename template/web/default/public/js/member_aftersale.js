function submitApply() {
	var refund_require = $("#refund_require").val(); // 退款类型
	var refund_money = 0;
	if ($("#refund_money").length > 0) {
		refund_money = parseFloat($("#refund_money").val()); // 退款金额
	}
	var refund_reason = $("#refund_reason").val(), // 退款原因
		max_refund_money = parseFloat($("#max_refund_money").val());
	
	if (refund_money < 0 || isNaN(refund_money)) {
		show('输入正确的退款金额');
		return;
	}
	if (refund_money > max_refund_money) {
		show('退款金额不能超出最大退款金额');
		return;
	}
	
	api('System.Order.applyOrderCustomer', {
		"order_goods_id": order_goods_id,
		"refund_type": refund_require,
		"refund_require_money": refund_money,
		"refund_reason": refund_reason
	}, function (res) {
		if (res.code == 0) {
			window.location.reload();
		} else {
			show(res.message);
		}
	}, false)
}

// 买家发货
function expressSave() {
	var express_company = $('#express_company').val(),
		shipping_no = $('#shipping_no').val();
	
	if (express_company.search(/[\S]+/)) {
		show('请输入物流公司');
		return;
	}
	if (shipping_no.search(/[\S]+/)) {
		show('请输入运单号');
		return;
	}
	
	api('System.Order.orderCustomerRefund', {
		"id": aftersale_id,
		"order_goods_id": order_goods_id,
		"refund_express_company": express_company,
		"refund_shipping_no": shipping_no
	}, function (res) {
		if (res.code == 0) {
			window.location.reload();
		} else {
			show(res.message);
		}
	}, false)
}