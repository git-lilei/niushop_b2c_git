function cancelDetail(order_id, order_goods_id) {
	api("System.Order.cancelOrderRefund", {"order_id": order_id, "order_goods_id": order_goods_id}, function (res) {
		if (res.data > 0) {
			show("取消退款成功");
			location.href = __URL(SHOPMAIN + "/member/refund");
		}
	});
}