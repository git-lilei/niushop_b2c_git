/**
 * 订单操作中转,订单流程相关操作
 * @param no
 * @param order_id
 */
function operation(no, order_id) {
	switch (no) {
		case 'pay'://支付
			pay(order_id);
			break;
		case 'close'://订单关闭
			orderClose(order_id);
			break;
		case 'getdelivery'://订单收货
			getDelivery(order_id);
			break;
		case 'refund'://申请退款
			orderRefund(order_id);
			break;
		case 'logistics' ://查看物流
			logistics(order_id);
			break;
		case 'delete_order'://删除订单
			deleteOrder(order_id);
			break;
		case 'pay_presell' : //预定金支付
			payPresell(order_id);
			break;
		case 'member_pickup' : // 买家自提
			memberPickup(order_id);
			break;
		default:
			break;
	}
}

/**
 * 微信支付
 * @param order_id
 */
function pay(order_id) {
	window.location.href = __URL(APPMAIN + "/order/orderpay?id=" + order_id);
}

/**
 * 预定金去支付
 */
function payPresell(order_id) {
	window.location.href = __URL(APPMAIN + "/order/orderPresellPay?id=" + order_id);
}

/**
 * 查看物流
 */
function logistics(order_id) {
	window.location.href = __URL(APPMAIN + "/order/logistics?order_id=" + order_id);
}

/**
 * 订单交易关闭
 * @param order_id
 */
function orderClose(order_id) {
	api("System.Order.orderClose", {"order_id": order_id}, function (res) {
		if (res.data > 0) {
			toast("关闭成功", location.href);
			location.reload();
		} else {
			toast("关闭失败", location.href);
		}
	});
}

/**
 * 订单收货
 * @param order_id
 */
function getDelivery(order_id) {
	api("System.Order.orderTakeDelivery", {"order_id": order_id}, function (res) {
		if (res.data > 0) {
			toast("收货成功");
			location.reload();
		} else {
			toast("收货失败", location.href);
		}
	});
}

/**
 * 删除订单
 * @param order_id
 */
function deleteOrder(order_id) {
	api("System.Order.deleteOrder", {"order_id": order_id}, function (res) {
		if (res.data > 0) {
			toast("订单删除成功", location.href);
			location.reload();
		} else {
			toast("订单删除失败", location.href);
		}
	});
}

//取消退款
function cancelRefund(order_id, order_goods_id) {
	api("System.Order.cancelOrderRefund", {"order_id": order_id, "order_goods_id": order_goods_id}, function (res) {
		if (res.data > 0) {
			toast('退款取消成功！');
			location.reload();
		}
	}, false);
}

// 买家自提
function memberPickup(order_id) {
	window.location.href = __URL(APPMAIN + "/order/pickup?order_id=" + order_id);
}