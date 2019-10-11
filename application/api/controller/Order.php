<?php
/**
 * Order.php
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 山西牛酷信息科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: http://www.niushop.com.cn
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 * @author : niuteam
 * @date : 2015.1.17
 * @version : v1.0.0.0
 */

namespace app\api\controller;


use data\extend\WchatOauth;
use data\service\Goods as GoodsService;
use data\service\Member as MemberService;
use data\service\OrderAction;
use data\service\OrderCreate;
use data\service\OrderQuery;
use data\service\OrderRefund;
use data\service\orderVertify;
use data\service\orderVertify as VerificationService;
use data\service\User;

/**
 * 订单控制器
 *
 */
class Order extends BaseApi
{
	/**
	 * 创建订单
	 */
	public function orderCreate()
	{
		$title = "创建订单";
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		$data = isset($this->params['data']) ? json_decode($this->params['data'], true) : [];//订单数据
		if (!empty($data['promotion_info'])) {
			$data['promotion_info'] = json_decode($data['promotion_info'], true);
		}
		
		//会员收货地址
		$member_service = new MemberService();
		$member_address = $member_service->getMemberDefaultAddress($this->uid);
		$data["address"] = $member_address;
		$data["buyer_id"] = $this->uid;
		$order_create = new OrderCreate();
		$res = $order_create->orderCreate($data);
		if ($res["code"] > 0) {
			$data = array(
				'out_trade_no' => $res['data']['out_trade_no']
			);
			return $this->outMessage($title, $data);
		} else {
			return $this->outMessage($title, $data, "-10", getErrorInfo($res["code"]));
		}
		
	}
	
	/**
	 * 订单的数据准备
	 */
	public function orderDataCollation()
	{
		$title = "订单准备";
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		$data = isset($this->params['data']) ? json_decode($this->params['data'], true) : [];//订单数据
        if (!empty($data['promotion_info']) && is_string($data['promotion_info'])) {
            $data['promotion_info'] = json_decode($data['promotion_info'], true);
        }
		$order_create = new OrderCreate();
		$member_service = new MemberService();
		$member_address = $member_service->getMemberDefaultAddress($this->uid);
		$data["address"] = $member_address;
		$data["buyer_id"] = $this->uid;
		$data = $order_create->dataCollation($data);//数据整理
		return $this->outMessage("查询成功", $data);
	}
	
	/**
	 * 订单计算(数据整理)
	 */
	public function orderCalculate()
	{
		$title = "订单计算";
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		$data = isset($this->params['data']) ? json_decode($this->params['data'], true) : [];//订单数据
		if (!empty($data['promotion_info'])) {
			$data['promotion_info'] = json_decode($data['promotion_info'], true);
		}
		$order_create = new OrderCreate();
		$member_service = new MemberService();
		$member_address = $member_service->getMemberDefaultAddress($this->uid);
		$data["address"] = $member_address;
		$data["buyer_id"] = $this->uid;
		$res = $order_create->orderCalculate($data);
		return $this->outMessage("查询成功", $res);
		
	}
	
	/**
	 * 订单错误消息
	 */
	public function orderErrorMessage($order_id, $message)
	{
		switch ($order_id) {
			case -4012:
				$message = '当前收货地址暂不支持配送';
				break;
			case -4005:
				$message = '订单已支付';
				break;
			case -4010:
				$message = '店铺积分功能未开启';
				break;
			case -4011:
				$message = '用户购物币不足';
				break;
			case -4004:
				$message = '用户积分不足';
				break;
			case -4003:
				$message = '库存不足';
				break;
			case -4007:
				$message = '当前用户积分不足';
				break;
			case -4008:
				$message = '当前用户余额不足';
				break;
			case -4014:
				$message = '当前地址不支持货到付款';
				break;
		}
		return $message;
	}
	
	/**
	 * 获取当前会员的订单列表
	 */
	public function order()
	{
		$title = "获取会员订单列表";
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		
		$page_index = isset($this->params['page']) ? $this->params['page'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$order_no = isset($this->params['order_no']) ? $this->params['order_no'] : '';
		$status = isset($this->params['status']) ? $this->params['status'] : "all";
		$order_type = isset($this->params['order_type']) ? $this->params['order_type'] : "all";
		
		$condition['buyer_id'] = $this->uid;
		$condition['is_deleted'] = 0;
		
		if ($order_type != 'all') {
			$condition['order_type'] = $order_type;
		}
		
		if (!empty($order_no)) {
			$condition['order_no'] = $order_no;
		}
		if ($status !== 'all') {
			switch ($status) {
				case -1:
					$condition['order_status'] = -1;
					break;
				case 0:
					$condition['order_status'] = 0;
					break;
				case 1:
					$condition['order_status'] = 1;
					break;
				case 2:
					$condition['order_status'] = 2;
					break;
				case 3:
					$condition['order_status'] = 3;
					break;
				case 4:
					$condition['order_status'] = 4;
					break;
				case 5:
					$condition['order_status'] = 5;
					$condition['is_evaluate'] = array(
						'in',
						'0,1'
					);
					break;
				case 6:
					$condition['order_status'] = 6;
					break;
			}
		}
		
		$order_query = new OrderQuery();
		$order_list = $order_query->getOrderList($page_index, $page_size, $condition, 'create_time desc');
		$order_list['statusNum'] = $order_query->getOrderStatusNum($condition);
		return $this->outMessage($title, $order_list);
	}
	
	/**
	 * 订单详情
	 */
	public function orderDetail()
	{
		$title = "获取订单详情";
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}

//		if ($this->getIsOpenVirtualGoodsConfig() == 0) {
//			return $this->outMessage($title, null, '-50', "未开启虚拟商品功能");
//		}
		
		$order_id = isset($this->params['order_id']) ? $this->params['order_id'] : 0;
		
		if (empty($order_id)) {
			return $this->outMessage($title, null, '-20', "无法获取订单信息");
		}
		$order_query = new OrderQuery();
		//防止订单越权
		$order_count = $order_query->getOrderCount([ "order_id" => $order_id, "buyer_id" => $this->uid ]);
		if ($order_count == 0) {
			return $this->outMessage($title, -50, -50, "对不起,您无权进行此操作");
		}
		
		$detail = $order_query->getOrderDetail($order_id);
		if (empty($detail)) {
			return $this->outMessage($title, null, '-20', "无法获取订单信息");
		}
		
		$count = 0; // 计算包裹数量（不包括无需物流）
		$express_count = count($detail['goods_packet_list']);
		$express_name = "";
		$express_code = "";
		if ($express_count) {
			foreach ($detail['goods_packet_list'] as $v) {
				if ($v['is_express']) {
					$count++;
					if (!$express_name) {
						$express_name = $v['express_name'];
						$express_code = $v['express_code'];
					}
				}
			}
			$data['express_name'] = $express_name;
			$data['express_code'] = $express_code;
		}
		$data['express_count'] = $express_count;
		$data['is_show_express_code'] = $count; // 是否显示运单号（无需物流不显示）
		$data["order"] = $detail;
		$detail['current_time'] = time() * 1000;
		
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 查询包裹物流信息
	 */
	public function orderExpressMessageList()
	{
		$title = "物流包裹信息";
		$express_id = isset($this->params['express_id']) ? $this->params['express_id'] : 0; // 物流包裹id
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		if (empty($express_id)) {
			return $this->outMessage($title, -9999, '-1', "无法获取物流信息");
		}
		$order_query = new OrderQuery();
		$count = $order_query->getOrderGoodsExpressCount([ "id" => $express_id, "uid" => $this->uid ]);
		if ($count <= 0) {
			return $this->outMessage($title, -9999, '-1', "无法获取物流信息");
		}
		$res = $order_query->getOrderGoodsExpressMessage($express_id);
		$res = array_reverse($res);
		
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 订单项退款详情
	 */
	public function refundDetail()
	{
		$title = "订单项退款详情";
		$order_goods_id = isset($this->params['order_goods_id']) ? $this->params['order_goods_id'] : 0;
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		if (empty($order_goods_id)) {
			return $this->outMessage($title, -50, '-10', "没有获取到退款信息");
		}
		
		$order_query = new OrderQuery();
		$count = $order_query->getOrderGoodsCount([ "buyer_id" => $this->uid, "order_goods_id" => $order_goods_id ]);
		if ($count <= 0) {
			return $this->outMessage($title, -50, '-10', "没有获取到退款信息");
		}
		$detail = $order_query->getOrderGoodsRefundInfo($order_goods_id);
		$detail['refund_type_name'] = '';
		if ($detail['refund_type'] > 0) {
			$type_info = $order_query->getPayTypeInfo([ "pay_type" => $detail['refund_type'] ]);
			if (!empty($type_info['type_name'])) {
				$detail['refund_type_name'] = str_replace('支付', '', $type_info['type_name']);
			}
		}
		
		$hook_res = hook('getRefundMoney', [
			'order_id' => $detail['order_id'],
			'order_goods_id' => $order_goods_id,
			'order_type' => $detail['order_type']
		]);
		$hook_res = arrayFilter($hook_res);
		if (!empty($hook_res[0])) {
			$refund_money = $hook_res[0]['refund_money'];
			$refund_balance = $hook_res[0]['refund_balance'];
			$freight = $hook_res[0]['freight'];
		} else {
			$refund_money = $order_query->getRefundRealMoney($detail['order_id'], $order_goods_id);
			$refund_balance = $order_query->getRefundBalance($detail['order_id'], $order_goods_id);
			$freight = $order_query->getRefundFreight($detail['order_id'], $order_goods_id);
		}
		
		// 查询订单所退运费
		$data = array(
			'refund_detail' => $detail,
			'refund_money' => sprintf("%.2f", $refund_money),
			'refund_balance' => sprintf("%.2f", $refund_balance),
			'freight' => $freight,
			'total_refund_money' => sprintf("%.2f", ($refund_money + $refund_balance + $freight))
		);
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 申请退款
	 */
	public function applyOrderRefund()
	{
		$title = "申请退款";
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		$order_id = isset($this->params['order_id']) ? $this->params['order_id'] : 0;
		if (empty($order_id)) {
			return $this->outMessage($title, -50, '-10', "无法获取订单信息");
		}
		
		$order_goods_id = isset($this->params['order_goods_id']) ? $this->params['order_goods_id'] : 0;
		if (empty($order_goods_id)) {
			return $this->outMessage($title, -50, '-10', "无法获取订单项信息");
		}
		
		$order_query = new OrderQuery();
		$count = $order_query->getOrderGoodsCount([ "buyer_id" => $this->uid, "order_goods_id" => $order_goods_id, "order_id" => $order_id ]);
		if ($count <= 0) {
			return $this->outMessage($title, -50, '-10', "没有获取到订单项信息");
		}
		
		$refund_type = isset($this->params['refund_type']) ? $this->params['refund_type'] : 1;
		$refund_require_money = isset($this->params['refund_require_money']) ? $this->params['refund_require_money'] : 0;
		$refund_reason = isset($this->params['refund_reason']) ? $this->params['refund_reason'] : "";
		$order_refund = new OrderRefund();
		$retval = $order_refund->orderGoodsRefundAskfor($order_id, $order_goods_id, $refund_type, $refund_require_money, $refund_reason);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 买家退货
	 */
	public function orderRefund()
	{
		$title = "买家退货";
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		$order_id = isset($this->params['order_id']) ? $this->params['order_id'] : 0;
		if (empty($order_id)) {
			return $this->outMessage($title, -50, '-10', "无法获取订单");
		}
		$order_goods_id = isset($this->params['order_goods_id']) ? $this->params['order_goods_id'] : 0;
		if (empty($order_goods_id)) {
			return $this->outMessage($title, -50, '-10', "无法获取订单项信息");
		}
		
		$order_query = new OrderQuery();
		$count = $order_query->getOrderGoodsCount([ "buyer_id" => $this->uid, "order_goods_id" => $order_goods_id, "order_id" => $order_id ]);
		if ($count <= 0) {
			return $this->outMessage($title, -50, '-10', "没有获取到订单项信息");
		}
		
		$refund_express_company = isset($this->params['refund_express_company']) ? $this->params['refund_express_company'] : "";
		$refund_shipping_no = isset($this->params['refund_shipping_no']) ? $this->params['refund_shipping_no'] : 0;
		$order_refund = new OrderRefund();
		$retval = $order_refund->orderGoodsReturnGoods($order_id, $order_goods_id, $refund_express_company, $refund_shipping_no);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 订单项售后详情
	 */
	public function customerDetail()
	{
		$title = "售后详情";
		$order_goods_id = isset($this->params['order_goods_id']) ? $this->params['order_goods_id'] : 0;
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		if (empty($order_goods_id)) {
			return $this->outMessage($title, -50, '-10', "无法获取订单项信息");
		}
		
		$order_query = new OrderQuery();
		$count = $order_query->getOrderGoodsCount([ "buyer_id" => $this->uid, "order_goods_id" => $order_goods_id ]);
		if ($count <= 0) {
			return $this->outMessage($title, -50, '-10', "没有获取到订单项信息");
		}
		
		$id = 0;
		$detail = $order_query->getCustomerServiceInfo($id, $order_goods_id);
		$order_goods_detail = $order_query->getOrderGoodsRefundInfo($order_goods_id);
		
		$hook_res = hook('getRefundMoney', [
			'order_id' => $order_goods_detail['order_id'],
			'order_goods_id' => $order_goods_id,
			'order_type' => $order_goods_detail['order_type']
		]);
		$hook_res = arrayFilter($hook_res);
		if (!empty($hook_res[0])) {
			$refund_money = $hook_res[0]['refund_money'];
			$refund_balance = $hook_res[0]['refund_balance'];
		} else {
			$refund_money = $order_query->getRefundRealMoney($order_goods_detail['order_id'], $order_goods_id);
			$refund_balance = $order_query->getRefundBalance($order_goods_detail['order_id'], $order_goods_id);
		}
		
		$data = array(
			'refund_detail' => $detail,
			'refund_money' => sprintf("%.2f", $refund_money),
			'refund_balance' => sprintf("%.2f", $refund_balance),
			'order_goods_detail' => $order_goods_detail,
			'total_refund_money' => round(($refund_money + $refund_balance), 2)
		);
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 申请退款 售后
	 */
	public function applyOrderCustomer()
	{
		$title = '申请退款 售后';
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		$order_goods_id = isset($this->params['order_goods_id']) ? $this->params['order_goods_id'] : 0;
		if (empty($order_goods_id)) {
			return $this->outMessage($title, -50, '-10', "无法获取订单项信息");
		}
		$order_query = new OrderQuery();
		$count = $order_query->getOrderGoodsCount([ "buyer_id" => $this->uid, "order_goods_id" => $order_goods_id ]);
		if ($count <= 0) {
			return $this->outMessage($title, -50, '-10', "没有获取到订单项信息");
		}
		
		$refund_type = isset($this->params['refund_type']) ? $this->params['refund_type'] : 1;
		$refund_require_money = isset($this->params['refund_require_money']) ? $this->params['refund_require_money'] : 0;
		$refund_reason = isset($this->params['refund_reason']) ? $this->params['refund_reason'] : "";
		$order_refund = new OrderRefund();
		$retval = $order_refund->orderGoodsCustomerServiceAskfor($order_goods_id, $refund_type, $refund_require_money, $refund_reason);
		if ($retval > 0) {
			return $this->outMessage($title, $retval);
		} else {
			return $this->outMessage($title, $retval, $retval, getErrorInfo($retval));
		}
		
	}
	
	/**
	 * 买家退货 售后
	 */
	public function orderCustomerRefund()
	{
		$title = "买家退货 售后";
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		$id = isset($this->params['id']) ? $this->params['id'] : 0;
		$order_goods_id = isset($this->params['order_goods_id']) ? $this->params['order_goods_id'] : 0;
		
		if (empty($order_goods_id)) {
			return $this->outMessage($title, -50, '-10', "无法获取订单项信息");
		}
		$order_query = new OrderQuery();
		$count = $order_query->getOrderGoodsCount([ "buyer_id" => $this->uid, "order_goods_id" => $order_goods_id ]);
		if ($count <= 0) {
			return $this->outMessage($title, -50, '-10', "没有获取到订单项信息");
		}
		
		$refund_express_company = isset($this->params['refund_express_company']) ? $this->params['refund_express_company'] : "";
		$refund_shipping_no = isset($this->params['refund_shipping_no']) ? $this->params['refund_shipping_no'] : 0;
		
		$order_refund = new OrderRefund();
		$retval = $order_refund->orderGoodsCustomerExpress($id, $order_goods_id, $refund_express_company, $refund_shipping_no);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 交易关闭
	 */
	public function orderClose()
	{
		$title = "关闭订单";
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		$order_action = new OrderAction();
		$order_id = isset($this->params['order_id']) ? $this->params['order_id'] : 0;
		if (empty($order_id)) {
			return $this->outMessage($title, -50, '-10', "无法获取订单");
		}
		
		$order_query = new OrderQuery();
		//防止订单越权
		$order_count = $order_query->getOrderCount([ "order_id" => $order_id, "buyer_id" => $this->uid ]);
		if ($order_count == 0) {
			return $this->outMessage($title, -50, -20, "对不起,您无权进行此操作");
		}
		
		$res = $order_action->orderClose($order_id);
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 收货
	 */
	public function orderTakeDelivery()
	{
		$title = "订单收货";
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		$order_action = new OrderAction();
		$order_id = isset($this->params['order_id']) ? $this->params['order_id'] : 0;
		if (empty($order_id)) {
			return $this->outMessage($title, -50, '-10', "无法获取订单");
		}
		
		$order_query = new OrderQuery();
		//防止订单越权
		$order_count = $order_query->getOrderCount([ "order_id" => $order_id, "buyer_id" => $this->uid ]);
		if ($order_count == 0) {
			return $this->outMessage($title, -50, -20, "对不起,您无权进行此操作");
		}
		
		$res = $order_action->OrderTakeDelivery($order_id);
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 删除订单
	 */
	public function deleteOrder()
	{
		$title = "删除订单";
		if (empty($this->uid)) {
			return $this->outMessage($title, -999, '-9999', "无法获取会员登录信息");
		}
		
		$order_action = new OrderAction();
		$order_id = isset($this->params['order_id']) ? $this->params['order_id'] : 0;
		if (empty($order_id)) {
			return $this->outMessage($title, -50, '-10', "无法获取订单信息");
		}
		
		$order_query = new OrderQuery();
		//防止订单越权
		$order_count = $order_query->getOrderCount([ "order_id" => $order_id, "buyer_id" => $this->uid ]);
		if ($order_count == 0) {
			return $this->outMessage($title, -50, -20, "对不起,您无权进行此操作");
		}
		
		$res = $order_action->deleteOrder($order_id, 2, $this->uid);
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 订单评价
	 */
	public function evaluationDetail()
	{
		$title = '订单评价';
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		
		$order_id = isset($this->params['order_id']) ? $this->params['order_id'] : 0;
		if (empty($order_id)) {
			return $this->outMessage($title, -50, '-10', "无法获取订单信息");
		}
		// 判断该订单是否是属于该用户的
		$order_query = new OrderQuery();;
		$condition['order_id'] = $order_id;
		$condition['buyer_id'] = $this->uid;
		$condition['review_status'] = 0;
		$condition['order_status'] = array(
			'in',
			'3,4'
		);
		$order_count = $order_query->getOrderCount($condition);
		if ($order_count == 0) {
			return $this->outMessage($title, -50, '-20', "对不起,您无权进行此操作");
		}
		
		$list = $order_query->getOrderGoods($order_id);
		$orderDetail = $order_query->getDetail($order_id);
		$data['order_no'] = $orderDetail['order_no'];
		$data['list'] = $list;
		
		if (($orderDetail['order_status'] == 3 || $orderDetail['order_status'] == 4) && $orderDetail['is_evaluate'] == 0) {
		} else {
			return $this->outMessage($title, null, -20);
		}
		
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 商品评价提交
	 */
	public function addGoodsEvaluate()
	{
		$title = "评价商品提交";
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		$order_id = isset($this->params['order_id']) ? $this->params['order_id'] : 0;
		$order_no = isset($this->params['order_no']) ? $this->params['order_no'] : "";
		$goods_evaluate = isset($this->params['goods_evaluate']) ? $this->params['goods_evaluate'] : "{}";
		
		if (empty($order_id)) {
			return $this->outMessage($title, -50, '-10', "无法获取订单信息");
		}
		
		$order_query = new OrderQuery();
		//防止订单越权
		$order_count = $order_query->getOrderCount([ "order_id" => $order_id, "order_no" => $order_no, "buyer_id" => $this->uid ]);
		if ($order_count == 0) {
			return $this->outMessage($title, -50, -20, "对不起,您无权进行此操作");
		}
		
		$order_action = new OrderAction();
		$result = $order_action->orderGoodsEvaluate([ "order_id" => $order_id, "order_no" => $order_no, "goods_evaluate" => $goods_evaluate ]);
		
		return $this->outMessage($title, $result);
	}
	
	/**
	 * 追评
	 */
	public function reviewEvaluateDetail()
	{
		$title = '追评';
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		$order_id = isset($this->params['order_id']) ? $this->params['order_id'] : 0;
		if (empty($order_id)) {
			return $this->outMessage($title, -50, '-10', "无法获取订单信息");
		}
		// 判断该订单是否是属于该用户的
		$order_query = new OrderQuery();
		$condition['order_id'] = $order_id;
		$condition['buyer_id'] = $this->uid;
		$condition['is_evaluate'] = 1;
		$order_count = $order_query->getOrderCount($condition);
		if ($order_count == 0) {
			return $this->outMessage($title, -50, -20, "对不起,您无权进行此操作");
		}
		
		$list = $order_query->getOrderGoods($order_id);
		$orderDetail = $order_query->getDetail($order_id);
		$data = array(
			'order_no' => $orderDetail['order_no'],
			'order_id' => $order_id,
			'list' => $list
		);
		if (($orderDetail['order_status'] == 3 || $orderDetail['order_status'] == 4) && $orderDetail['is_evaluate'] == 1) {
			return $this->outMessage($title, $data);
		} else {
			return $this->outMessage($title, null, -20);
		}
	}
	
	/**
	 * 商品-追加评价提交数据
	 */
	public function addGoodsReviewEvaluate()
	{
		$title = "追评商品提交";
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		$order_action = new OrderAction();
		$order_id = isset($this->params['order_id']) ? $this->params['order_id'] : 0;
		$order_no = isset($this->params['order_no']) ? $this->params['order_no'] : "";
		
		$goods = isset($this->params['goods_evaluate']) ? $this->params['goods_evaluate'] : "";
		
		if (empty($order_id)) {
			return $this->outMessage($title, -50, '-10', "无法获取订单信息");
		}
		
		$order_query = new OrderQuery();
		//防止订单越权
		$order_count = $order_query->getOrderCount([ "order_id" => $order_id, "order_no" => $order_no, "buyer_id" => $this->uid ]);
		if ($order_count == 0) {
			return $this->outMessage($title, -50, -20, "对不起,您无权进行此操作");
		}
		$goodsEvaluateArray = json_decode($goods);
		$result = 1;
		foreach ($goodsEvaluateArray as $key => $goodsEvaluate) {
			$temp_data = array(
				"again_content" => $goodsEvaluate->content,
				"again_image" => $goodsEvaluate->imgs,
				"order_goods_id" => $goodsEvaluate->order_goods_id,
			);
			$res = $order_action->addGoodsEvaluateAgain($temp_data);
			if ($res == false) {
				$result = false;
				break;
			}
		}
		if ($result == 1) {
			$data = array(
				'is_evaluate' => 2
			);
			$result = $order_action->modifyOrderInfo($data, $order_id);
		}
		
		return $this->outMessage($title, $result);
	}
	
	/**
	 * 订单统计数量
	 */
	public function orderCount()
	{
		$title = "订单数量统计";
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		$order_type = isset($this->params['order_type']) ? $this->params['order_type'] : '';
		$order_status = isset($this->params['order_status']) ? $this->params['order_status'] : '';
		$is_evaluate = isset($this->params['is_evaluate']) ? $this->params['is_evaluate'] : '';
		$is_virtual = isset($this->params['is_virtual']) ? $this->params['is_virtual'] : '';
		$condition = array(
			"buyer_id" => $this->uid
		);
		//订单类型
		if ($order_type !== "") {
			$condition["order_type"] = $order_type;
		}
		//订单状态
		if ($order_status !== "") {
			$condition["order_status"] = $order_status;
		}
		//评价
		if ($is_evaluate !== "") {
			$condition["is_evaluate"] = $is_evaluate;
		}
		//虚拟
		if ($is_virtual !== "") {
			$condition["is_virtual"] = $is_virtual;
		}
		$order_query = new OrderQuery();
		$count = $order_query->getOrderCount($condition);
		return $this->outMessage("订单数量统计", $count);
	}
	
	/**
	 * 退款/退货/维修订单列表
	 */
	public function refund()
	{
		$title = "退款/退货/维修订单列表";
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		$order_query = new OrderQuery();
		
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		
		// 查询订单状态的数量
		$condition['buyer_id'] = $this->uid;
		$condition['order_status'] = array(
			'in',
			'-1,-2'
		);
		$orderList = $order_query->getOrderList($page_index, $page_size, $condition, 'create_time desc');
		
		foreach ($orderList['data'] as $key => $item) {
			$order_item_list = $orderList['data'][ $key ]['order_item_list'];
			foreach ($order_item_list as $k => $value) {
				if ($value['refund_status'] == 0 || $value['refund_status'] == -2) {
					unset($order_item_list[ $k ]);
				}
			}
			$orderList['data'][ $key ]['order_item_list'] = $order_item_list;
		}
		return $this->outMessage($title, $orderList);
	}
	
	/**
	 * 取消退款
	 */
	public function cancelOrderRefund()
	{
		$title = "取消退款";
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		$order_id = isset($this->params['order_id']) ? $this->params['order_id'] : 0;
		if (empty($order_id)) {
			return $this->outMessage($title, -50, '-10', "无法获取订单信息");
		}
		
		$order_query = new OrderQuery();
		//防止订单越权
		$order_count = $order_query->getOrderCount([ "order_id" => $order_id, "buyer_id" => $this->uid ]);
		if ($order_count == 0) {
			return $this->outMessage($title, -50, -20, "对不起,您无权进行此操作");
		}
		
		$order_refund = new OrderRefund();
		
		$order_goods_id = isset($this->params['order_goods_id']) ? $this->params['order_goods_id'] : 0;
		$cancle_order = $order_refund->orderGoodsCancel($order_id, $order_goods_id);
		return $this->outMessage($title, $cancle_order);
	}
	
	/**
	 * 获取商品评价/晒单
	 */
	public function evaluate()
	{
		$title = "获取商品评价/晒单";
		$order_query = new OrderQuery();
		$page = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$order_no = isset($this->params['order_no']) ? $this->params['order_no'] : '';
		$condition = [];
		$condition['uid'] = $this->uid;
		if (!empty($order_no)) {
			$condition["order_no"] = $order_no;
		}
		$goodsEvaluationList = $order_query->getOrderEvaluateDataList($page, $page_size, $condition, 'addtime desc');
		foreach ($goodsEvaluationList['data'] as $k => $v) {
			$goodsEvaluationList['data'][ $k ]['evaluationImg'] = (empty($v['image'])) ? '' : explode(',', $v['image']);
			$goodsEvaluationList['data'][ $k ]['againEvaluationImg'] = (empty($v['again_image'])) ? '' : explode(',', $v['again_image']);
		}
		
		return $this->outMessage($title, $goodsEvaluationList);
		
	}
	
	/**
	 * 订单项退款详情（pc端）
	 */
	public function orderGoodsRefundDetail()
	{
		$title = "获取订单项退款详情";
		$order_goods_id = isset($this->params['order_goods_id']) ? $this->params['order_goods_id'] : '';
		$order_query = new OrderQuery();
		
		if (empty($this->uid)) {
			return $this->outMessage('', "", '-9999', "无法获取会员登录信息");
		}
		if (empty($order_goods_id)) {
			return $this->outMessage('', "", '-1', "缺少参数order_goods_id");
		}
		$count = $order_query->getOrderGoodsCount([ 'order_goods_id' => $order_goods_id, 'buyer_id' => $this->uid ]);
		if ($count == 0) {
			return $this->outMessage('', "", '-1', "未获取到退款数据");
		}
		
		$data = [];
		// 订单项退款信息
		$data['detail'] = $order_query->getOrderGoodsRefundInfo($order_goods_id);
		// 实际可退金额
		$refund_money = $order_query->orderGoodsRefundMoney($order_goods_id);
		$data['refund_money'] = sprintf("%.2f", $refund_money);
		
		// 退还余额
		$refund_balance = $order_query->orderGoodsRefundBalance($order_goods_id);
		$data['refund_balance'] = sprintf("%.2f", $refund_balance);
		// 退还运费
		$freight = $order_query->getOrderRefundFreight($order_goods_id);
		$data['freight'] = sprintf("%.2f", $freight);
		
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 查看物流
	 */
	public function logistics()
	{
		$title = "查看物流";
		if (empty($this->uid)) {
			return $this->outMessage('', "", '-9999', "无法获取会员登录信息");
		}
		$order_id = isset($this->params['order_id']) ? $this->params['order_id'] : 0;
		if (empty($order_id)) {
			return $this->outMessage($title, -50, '-10', "无法获取订单信息");
		}
		
		$order_query = new OrderQuery();
		$order_count = $order_query->getOrderCount([ "order_id" => $order_id, "buyer_id" => $this->uid ]);
		if ($order_count == 0) {
			return $this->outMessage('', "", '-10', "没有获取到订单信息");
		}
		$detail = $order_query->getOrderDetail($order_id);
		if (empty($detail)) {
			return $this->outMessage('', "", '-10', "没有获取到订单信息");
		}
		return $this->outMessage($title, $detail);
	}
	
	/**
	 * 获取订单状态
	 */
	public function orderStatus()
	{
		$title = '获取订单状态';
		
		if (empty($this->uid)) {
			return $this->outMessage('', "", '-9999', "无法获取会员登录信息");
		}
		
		$order_type = isset($this->params['order_type']) ? $this->params['order_type'] : '';
		
		$order_service = new OrderQuery();
		$data = $order_service->getOrderStatus([ 'order_type' => $order_type ]);
		
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 获取订单类型
	 */
	public function orderType()
	{
		$title = '获取订单类型';
		
		if (empty($this->uid)) {
			return $this->outMessage('', "", '-9999', "无法获取会员登录信息");
		}
		
		$order_service = new OrderQuery();
		$data = $order_service->getOrderType();
		
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 核销商品详情
	 */
	public function verificationDetail()
	{
		$title = '核销商品详情';
		$verificadition = new VerificationService();
		$vg_id = isset($this->params['vg_id']) ? $this->params['vg_id'] : "";
		
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$user = new User();
		$is_member = $user->getSessionUserIsMember();
		
		if (empty($is_member)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		
		$uid = $this->uid;
		$condition = array(
			'virtual_goods_id' => $vg_id,
			"buyer_id" => $uid
		);
		
		$verificadition_detail = $verificadition->getVirtualGoodsDetail($condition);
		if (empty($verificadition_detail)) {
			return $this->outMessage($title, null, '-10', "未获取到该虚拟码信息");
		}
		return $this->outMessage($title, $verificadition_detail);
	}
	
	
	/**
	 * 核销商品审核
	 */
	public function verificationExamine()
	{
		$title = '核销商品审核';
		$vg_id = isset($this->params['vg_id']) ? $this->params['vg_id'] : "";
		if (empty($this->uid)) {
			$_SESSION['login_pre_url'] = __URL(\think\Config::get('view_replace_str.APP_MAIN') . "/verification/goods?vg_id=" . $vg_id);
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		
		$verificadition = new VerificationService();
		
		// 判断用户是否是该店的核销员
		
		$is_verification_person = $verificadition->getShopVerificationInfo($this->uid, $this->instance_id);
		if ($is_verification_person == 0) {
			return $this->outMessage($title, null, '-20', "对不起，您没有权限核验该订单");
		}
		$condition = array(
			'virtual_goods_id' => $vg_id,
			'goods_type' => 0
		);
		
		// 虚拟码详情
		$verificadition_detail = $verificadition->getVirtualGoodsDetail($condition);
		
		if (empty($verificadition_detail)) {
			return $this->outMessage($title, null, '-10', "未获取到该虚拟码信息");
		}
		
		$time = time();
		if ($time < $verificadition_detail['start_time']) {
			return $this->outMessage($title, null, '-10', "该虚拟码未到有效期");
		}
		
		if ($verificadition_detail['end_time'] > 0) {
			if ($time > $verificadition_detail['end_time']) {
				return $this->outMessage($title, null, '-10', "该虚拟码已过期");
			}
		}
		
		if ($verificadition_detail['confine_use_number'] > 0 && ($verificadition_detail['confine_use_number'] - $verificadition_detail['use_number']) <= 0) {
			return $this->outMessage($title, null, '-10', "对不起，该虚拟码使用次数已用完");
		}
		
		return $this->outMessage($title, $verificadition_detail);
	}
	
	/**
	 * 核销人员详情
	 */
	public function getVerificationPersonnelInfo()
	{
		$verificadition = new VerificationService();
		$info = $verificadition->getShopVerificationDetail($this->uid, $this->instance_id);
		
		return $this->outMessage('核销人员详情', $info);
	}
	
	/**
	 * 核销虚拟码
	 */
	public function verificationVirtualGoods()
	{
		$title = '核销虚拟码';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$verificadition = new VerificationService();
		$virtual_goods_id = isset($this->params['virtual_goods_id']) ? $this->params['virtual_goods_id'] : "";
		$res = $verificadition->verificationVirtualGoods($this->uid, $virtual_goods_id);
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 输入虚拟码进行核销
	 *
	 */
	public function checkCode()
	{
		$title = '虚拟码核销';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$verificadition = new VerificationService();
		$virtual_code = isset($this->params['virtual_code']) ? $this->params['virtual_code'] : "";
		$condition = array(
			"virtual_code" => $virtual_code
		);
		$verificadition_detail = $verificadition->getVirtualGoodsDetail($condition);
		if (empty($verificadition_detail)) {
			return $this->outMessage($title, null, '-10', "未获取到该虚拟码信息");
		}
		$time = time();
		if ($time < $verificadition_detail['start_time']) {
			return $this->outMessage($title, null, '-10', "该虚拟码未到有效期");
		}
		if ($verificadition_detail['end_time'] > 0) {
			if ($time > $verificadition_detail['end_time']) {
				return $this->outMessage($title, null, '-10', "该虚拟码已过期");
			}
		}
		if ($verificadition_detail['confine_use_number'] > 0 && ($verificadition_detail['confine_use_number'] - $verificadition_detail['use_number']) <= 0) {
			return $this->outMessage($title, null, '-10', "对不起，该虚拟码使用次数已用完");
		}
		$res = $verificadition_detail['virtual_goods_id'];
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 我的虚拟码列表
	 */
	public function virtualCodeList()
	{
		$title = '我的虚拟码列表';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$virtualGoods = new GoodsService();
		$type = isset($this->params['type']) ? $this->params['type'] : "";
		$condition['use_status'] = $type;
		$condition['nvg.buyer_id'] = $this->uid;
		$order = "create_time desc";
		$virtual_list = $virtualGoods->getVirtualGoodsList(1, 0, $condition, $order);
		foreach ($virtual_list['data'] as $key => $item) {
			$virtual_list['data'][ $key ]['start_time'] = date("Y-m-d", $item['start_time']);
			if ($item['end_time'] > 0) {
				$virtual_list['data'][ $key ]['end_time'] = date("Y-m-d", $item['end_time']) . "之前";
			} else {
				$virtual_list['data'][ $key ]['end_time'] = "不限制有效期";
			}
		}
		
		return $this->outMessage($title, $virtual_list['data']);
	}
	
	/**
	 * 核销检测
	 */
	public function checkVerification()
	{
		$title = '核销台';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$is_need_back_page = $this->get('is_need_back_page', 0);
		// 检测当前用户是否为核销员
		$verification_service = new VerificationService();
		$is_verification = $verification_service->getShopVerificationInfo($this->uid, $this->instance_id);
		if (!$is_verification > 0) {
			$code = $is_need_back_page == 1 ? -50 : -1;
            return $this->outMessage($title, null, $code, "暂无核销资格");
		}
		return $this->outMessage($title, 1);
	}
	
	/**
	 * 获取手机端虚拟商品
	 */
	public function getWapVirtualGoodsShare()
	{
		
		$title = '虚拟商品';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		
		$vg_id = isset($this->params['vg_id']) ? $this->params['vg_id'] : "";
		
		$verificadition = new VerificationService();
		
		$condition = array( 'virtual_goods_id' => $vg_id );
		$verificadition_detail = $verificadition->getVirtualGoodsDetail($condition);
		if (empty($verificadition_detail)) {
			return $this->outMessage($title, null, '-10', "未获取到该虚拟码信息");
		}
		
        //虚拟商品二维码
        $is_applet = $this->get('is_applet', 0);
        if ($is_applet == 1) {
            $path = $this->getAppletVirtualQecode($vg_id);
            if ($path == -50) {
                return $this->outMessage($title, '', -50, '商家未配置小程序');
            } else if ($path == -10) {
               $path = -1;
            }
        } else {
            $path = $this->getVirtualQecode($vg_id);
        }
		$verificadition_detail['path'] = $path;
		
		return $this->outMessage($title, $verificadition_detail);
	}
	
	/**
	 * 核销记录
	 */
	public function virtualGoodsVerificationList()
	{
		$title = '核销记录';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$vg_id = isset($this->params['vg_id']) ? $this->params['vg_id'] : "";
		
		$verificadition = new VerificationService();
		$virtualGoodsVerificationList = $verificadition->getVirtualGoodsVerificationList(1, 0, [ 'virtual_goods_id' => $vg_id ], 'create_time desc');
		return $this->outMessage($title, $virtualGoodsVerificationList['data']);
	}
	
	/**
	 * 制作核销二维码
	 */
	function getVirtualQecode($virtual_goods_id)
	{
		$title = '制作核销二维码';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$url = __URL(__URL__ . '/wap/Verification/goods?vg_id=' . $virtual_goods_id);
		
		// 查询并生成二维码
		$upload_path = "upload/qrcode/virtual_qrcode";
		if (!file_exists($upload_path)) {
			mkdir($upload_path, 0777, true);
		}
		$path = $upload_path . '/virtual_' . $virtual_goods_id . '.png';
		getQRcode($url, $upload_path, "virtual_" . $virtual_goods_id);
		return $path;
	}

	/**
     * 制作小程序核销二维码
     */
    function getAppletVirtualQecode($virtual_goods_id)
    {
        $title = '制作核销二维码';
        if (empty($this->uid)) {
            return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
        }

        // 查询并生成二维码
        $upload_path = "upload/applet_qrcode/virtual_qrcode";
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        $wchat_oauth = new WchatOauth();
		$scene = $virtual_goods_id;
		$page = 'pagesother/pages/verification/verificationgooodstoexamine/verificationgooodstoexamine';
		$path = $wchat_oauth->getAppletQrcode($scene, $page, true);
        return $path;
    }
	
	/**
	 * 获取自提码
	 */
	public function getPickupQecode()
	{
		$title = '获取自提二维码';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$order_id = $this->get('order_id', 0);
		
		$order_query = new OrderQuery();
		$pickup_info = $order_query->getOrderPickupInfo($order_id);
		
		if (!empty($pickup_info['picked_up_code'])) {
			$url = __URL(__URL__ . '/wap/order/pickupToExamine?order_id=' . $order_id);
			$upload_path = "upload/qrcode/order_pickup_code_qrcode";
			if (!file_exists($upload_path)) {
				mkdir($upload_path, 0777, true);
			}
			$qrcode_name = 'orderPickupCode_' . $order_id;
			$path = $upload_path . '/' . $qrcode_name;
			getQRcode($url, $upload_path, $qrcode_name);
			
			return $this->outMessage($title, [ 'path' => $path . '.png' ], 1, '生成成功！');
		} else {
			return $this->outMessage($title, null, '-1', "未获取到自提信息");
		}
	}

    /**
     * 获取自提码
     */
    public function getAppletPickupQecode()
    {
        $title = '获取自提二维码';
        if (empty($this->uid)) {
            return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
        }
        $order_id = $this->get('order_id', 0);

        $order_query = new OrderQuery();
        $pickup_info = $order_query->getOrderPickupInfo($order_id);

        if (!empty($pickup_info['picked_up_code'])) {
            $upload_path = "upload/applet_qrcode/order_pickup_code_qrcode";
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            $wchat_oauth = new WchatOauth();
            $scene = $order_id;
            $page = 'pagesother/pages/order/pickuptoexamine/pickuptoexamine';
            $path = $wchat_oauth->getAppletQrcode($scene, $page, true);
            if ($path == -50) {
                return $this->outMessage($title, '', -50, '商家未配置小程序');
            } else if ($path == -10) {
                return $this->outMessage($title, '', -10, '二维码生成失败，请检查该二维码指向页面是否在小程序线上版本中存在');
            }
            return $this->outMessage($title, [ 'path' => $path ], 1, '生成成功！');
        } else {
            return $this->outMessage($title, null, '-10', "未获取到自提信息");
        }
    }

	/**
	 * 自提核销信息
	 */
	public function getPickupOrderInfo()
	{
	    $title = '自提核销信息';
        if (empty($this->uid)) {
            return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
        }
		$order_id = $this->get('order_id', 0);
		$order_query = new OrderQuery();
		$res = $order_query->getOrderPickupInfo($order_id);
		if (empty($res['picked_up_code'])) {
		    return $this->outMessage($title, null, '-10', "未获取到自提信息！");
		}
		// 判断当前用户是否是该门店的审核员
		$isPickedUpAuditor = $order_query->currUserIsPickedUpAuditor($res['picked_up_id'], $this->uid);
		if (!$isPickedUpAuditor) {
            return $this->outMessage($title, null, '-20', "您不是该门店的审核员！");
		}
		$detail = $order_query->getOrderDetail($order_id);
		if (empty($detail)) {
		     return $this->outMessage($title, null, '-10', "没有获取到订单信息！");
		}
		return $this->outMessage($title, $detail);
	}

	/**
	 * 确认自提
	 */
	public function confirmPickup()
	{
		$title = '自提点核销员确认自提';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$order_id = $this->get('order_id', '');
		$buyer_name = $this->get('buyer_name', 0);
		$buyer_phone = $this->get('buyer_phone', 0);
		
		$order_query = new OrderQuery();
		$pickup_info = $order_query->getOrderPickupInfo($order_id);
		
		if (empty($pickup_info['picked_up_code'])) {
			$this->outMessage($title, null, -10, '未获取到自提信息！');
		}
		
		$is_pickup_auditor = $order_query->currUserIsPickedUpAuditor($pickup_info['picked_up_id'], $this->uid);
		if (!$is_pickup_auditor) {
			$this->outMessage($title, null, -10, '不是该门店的审核员！');
		}
		$order_action = new OrderAction();
		$res = $order_action->pickedUpAuditorConfirmPickup($order_id, $this->uid, $buyer_name, $buyer_phone);
		
		if ($res['code'] > 0) {
			return $this->outMessage($title, null, 1, '确认成功');
		} else {
			return $this->outMessage($title, null, -1, $res['message']);
		}
	}

	/**
	 * 订单项信息
	 */
	public function orderGoodsDetail()
	{
		$title = "订单项详情";
		$order_goods_id = isset($this->params['order_goods_id']) ? $this->params['order_goods_id'] : '';
		$order_query = new OrderQuery();
		if (empty($this->uid)) {
			return $this->outMessage('', "", '-9999', "无法获取会员登录信息");
		}
		if (empty($order_goods_id)) {
			return $this->outMessage('', "", '-10', "缺少参数order_goods_id");
		}
		$count = $order_query->getOrderGoodsCount([ 'order_goods_id' => $order_goods_id, 'buyer_id' => $this->uid ]);
		if ($count == 0) {
			return $this->outMessage('', "", '-10', "未获取到退款数据");
		}
		
		$info = $order_query->getOrderGoodsInfo($order_goods_id);
		return $this->outMessage($title, $info);
	}
	
	/**
	 * 虚拟商品信息
	 */
	public function downloadVirtualGoods()
	{
		$title = '下载商品';
		if (empty($this->uid))
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		
		$virtual_code = isset($this->params['virtual_code']) ? $this->params['virtual_code'] : '';
		if (empty($virtual_code))
			return $this->outMessage($title, null, '-10', "无效的下载编号");
		
		$order_vertify = new orderVertify();
		$result = $order_vertify->downloadVirtualGoods([ "uid" => $this->uid, "virtual_code" => $virtual_code ]);
		$data = $result['data'];
		if ($result["code"] > 0) {
			return $this->outMessage($title, $result['data']);
		} else {
			return $this->outMessage($title, $data, "-10", getErrorInfo($result["code"]));
		}
	}
	
	/**
	 * 订单信息
	 */
	public function orderInfo()
	{
		$title = "获取会员订单列表";
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$out_trade_no = isset($this->params['out_trade_no']) ? $this->params['out_trade_no'] : '';
		
		if (empty($out_trade_no)) {
			return $this->outMessage($title, "", '-10', "没有获取到支付信息");
		}
		
		$order_query = new OrderQuery();
		$order_info = $order_query->getOrderInfo([ "out_trade_no" => $out_trade_no ]);
		
		return $this->outMessage($title, $order_info);
	}
}