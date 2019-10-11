$(function () {
	var member_balance = $("#hidden_member_balance").val(), //可用余额
		need_pay_money = $("#hidden_need_pay_money").val(), //还需支付金额
		pay_money = $("#hidden_pay_money").val(), //总金额
		out_trade_no = $("#hidden_out_trade_no").val(); //交易流水号
	
	$("#is_use_balance").click(function () {
		if ($(this).is(":checked")) {
			$(".pay_balance_show").show();
			$(".surplus_need_pay_money").text(need_pay_money);
		} else {
			$(".pay_balance_show").hide();
			$(".surplus_need_pay_money").text(pay_money);
		}
	});
	
	$(".payment-btn.pay").click(function () {
		var is_use_balance = $("#is_use_balance").is(":checked") ? 1 : 0;
		$.ajax({
			url: __URL(APPMAIN + '/pay/pay'),
			type: "post",
			data: {
				"out_trade_no": out_trade_no,
				"is_use_balance": is_use_balance
			},
			success: function (data) {
				if (data['code'] == 0) {
					window.location.href = __URL(APPMAIN + '/pay/getPayValue?out_trade_no=' + out_trade_no);
				} else if (data['code'] == 1) {
					location.href = __URL(APPMAIN + '/pay/payReturn?msg=1&out_trade_no=' + out_trade_no);
				} else if (data['code'] < 0) {
					alert(data['message']);
				}
			}
		})
	})
	
});