<?php
/**
 * Order.php
 *
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

namespace addons\NsPresell\data\service;

use data\model\NsOrderGoodsModel;
use data\model\NsOrderModel;
use data\model\NsOrderPaymentModel;
use data\model\NsOrderPresellModel;
use data\service\Member\MemberAccount;
use data\service\Order\OrderStatus;

/**
 * 预售订单
 */
class Orderpresell extends Order
{
	
	
	/**
	 * 获取预售订单可退金额
	 */
	public function getPresellOrderGoodsRefundMoney($param)
	{
		$ns_order = new NsOrderModel();
		$ns_presell_order = new NsOrderPresellModel();
		
		$money = 0; // 应退金额
		
		$presell_order_info = $ns_presell_order->getInfo([ "relate_id" => $param["order_id"] ], "is_full_payment,point_money,presell_pay,platform_money");
		$order_info = $ns_order->getInfo([ "order_id" => $param["order_id"] ], "order_money,point_money,user_platform_money,tax_money,shipping_money,pay_money");
		if (!empty($order_info) && !empty($presell_order_info)) {
			// 如果该订单是全款支付的
			if ($presell_order_info["is_full_payment"]) {
				$money = $presell_order_info["presell_pay"];
				if ($presell_order_info["platform_money"] < $order_info["shipping_money"]) {
					$money = $money - $order_info["tax_money"] - $order_info["shipping_money"];
				} else {
					$money = $money - $order_info["tax_money"];
				}
			} else {
				$money = $presell_order_info["presell_pay"] + $order_info["pay_money"];
				if ($order_info["user_platform_money"] < $order_info["shipping_money"]) {
					$money = $money - $order_info["tax_money"] - $order_info["shipping_money"];
				} else {
					$money = $money - $order_info["tax_money"];
				}
			}
		}
		return $money;
	}
	
	/**
	 * 获取预售订单可退余额
	 */
	public function getPresellOrderGoodsRefundBanlance($param)
	{
		$ns_order = new NsOrderModel();
		$ns_presell_order = new NsOrderPresellModel();
		
		$balance = 0; // 应退金额
		
		$presell_order_info = $ns_presell_order->getInfo([ "relate_id" => $param["order_id"] ], "is_full_payment,point_money,presell_pay,platform_money");
		$order_info = $ns_order->getInfo([ "order_id" => $param["order_id"] ], "order_money,point_money,user_platform_money,tax_money,shipping_money,pay_money");
		if (!empty($order_info) && !empty($presell_order_info)) {
			// 如果该订单是全款支付的
			if ($presell_order_info["is_full_payment"]) {
				$balance = $presell_order_info["platform_money"];
				if ($balance > $order_info["shipping_money"]) {
					$balance -= $order_info["shipping_money"];
				}
			} else {
				$balance = $presell_order_info["platform_money"] + $order_info["user_platform_money"];
				if ($order_info["user_platform_money"] > $order_info["shipping_money"]) {
					$balance -= $order_info["shipping_money"];
				}
			}
		}
		return $balance;
	}
	
	/**
	 * 预售订单详情
	 */
	public function getOrderPresellInfo($presell_order_id = 0, $condition = [])
	{
		if (!empty($presell_order_id))
			$condition['presell_order_id'] = $presell_order_id;
		$order_presell_model = new NsOrderPresellModel();
		$order_presell_info = $order_presell_model->getInfo($condition, '*');
		return $order_presell_info;
	}
	
	
	/**
	 * 预售订单重新生成交易流水号时返回之前锁定的余额
	 * @param $presell_order_id
	 */
	public function createNewOutTradeNoReturnBalancePresellOrder($presell_order_id)
	{
		$pay = new NsOrderPaymentModel();
		$orderPresell = new NsOrderPresellModel();
		$order = new NsOrderModel();
		$order_presell_info = $orderPresell->getInfo([
			'presell_order_id' => $presell_order_id,
			'order_status' => 0
		], "out_trade_no,relate_id");
		$order_info = $order->getInfo([ "order_id" => $order_presell_info['relate_id'] ], "buyer_id");
		if (!empty($order_presell_info)) {
			$pay_info = $pay->getInfo([
				'out_trade_no' => $order_presell_info['out_trade_no'],
				'pay_status' => 0
			], "balance_money,original_money");
			
			if (!empty($pay_info) && $pay_info['balance_money'] > 0) {
				
				$member_account = new MemberAccount();
				$member_account->addMemberAccountData(0, 2, $order_info['buyer_id'], 0, $pay_info['balance_money'], 1, $presell_order_id, "订单重新生成交易号，返还锁定余额");
				
				$data = array(
					"pay_money" => $pay_info['original_money'],
					"balance_money" => 0
				);
				$pay->save($data, [
					'out_trade_no' => $order_presell_info['out_trade_no']
				]);
			}
		}
	}
	
	/**
	 * 预售订单预售结束
	 */
	public function autoPresellOrder()
	{
		$presell_order_model = new NsOrderPresellModel();
		$presell_order_model->startTrans();
		
		try {
			$condition = array(
				'order_status' => 1,
				'presell_delivery_time' => array( 'elt', time() )
			);
			$presell_order_list = $presell_order_model->getQuery($condition, 'relate_id, payment_type, is_full_payment');
			$presell_order_model->save([ 'order_status' => 2 ], $condition);
			
			foreach ($presell_order_list as $item) {
				$order_model = new NsOrderModel();
				$order_condition = array(
					'order_id' => $item['relate_id'],
					'order_status' => 7
				);
				$order_model->save([ 'order_status' => 0 ], $order_condition);
				
				if ($item['is_full_payment'] == 1) {
					$order_action = new OrderAction();
					$order_action->orderOffLinePay($item['relate_id'], $item['payment_type'], 0); // 默认微信支付
				}
			}
			$presell_order_model->commit();
			return 1;
		} catch (\Exception $e) {
			$presell_order_model->rollback();
			return $e->getMessage();
		}
		
	}
	
	/**
	 * 获取订单项可退实际金额
	 * @param unknown $order_id
	 * @param unknown $order_goods_id
	 */
	public function getRefundRealMoney($order_id, $order_goods_id)
	{
		$ns_order = new NsOrderModel();
		$ns_presell_order = new NsOrderPresellModel();
		
		$presell_order_info = $ns_presell_order->getInfo([ "relate_id" => $order_id ], "is_full_payment,presell_pay,platform_money");
		$order_info = $ns_order->getInfo([ "order_id" => $order_id ], "pay_money,user_platform_money,shipping_money,pay_status");
		if ($presell_order_info['is_full_payment'] == 0 && $order_info['pay_status'] > 0) {
			$presell_order_info['presell_pay'] += $order_info['pay_money'];
			$presell_order_info['platform_money'] += $order_info['user_platform_money'];
		}
		if (empty($presell_order_info['presell_pay'])) return 0;
		
		if ($presell_order_info['platform_money'] > 0 && $order_info['shipping_money']) {
			$total_money = $presell_order_info['presell_pay'] + $presell_order_info['platform_money'];
			$presell_order_info['presell_pay'] = round(($total_money - $order_info['shipping_money']) * round(($presell_order_info['presell_pay'] / $total_money), 2), 2);
		} else {
			$presell_order_info['presell_pay'] -= $order_info['shipping_money'];
		}
		
		return $presell_order_info['presell_pay'];
	}
	
	/**
	 * 获取订单项可退余额
	 * @param unknown $order_id
	 * @param unknown $order_goods_id
	 */
	public function getRefundBalance($order_id, $order_goods_id)
	{
		$ns_order = new NsOrderModel();
		$ns_presell_order = new NsOrderPresellModel();
		
		$presell_order_info = $ns_presell_order->getInfo([ "relate_id" => $order_id ], "is_full_payment,presell_pay,platform_money");
		$order_info = $ns_order->getInfo([ "order_id" => $order_id ], "pay_money,user_platform_money,shipping_money,pay_status");
		if ($presell_order_info['is_full_payment'] == 0 && $order_info['pay_status'] > 0) {
			$presell_order_info['presell_pay'] += $order_info['pay_money'];
			$presell_order_info['platform_money'] += $order_info['user_platform_money'];
		}
		if (empty($presell_order_info['platform_money'])) return 0;
		
		if ($presell_order_info['presell_pay'] > 0 && $order_info['shipping_money']) {
			$total_money = $presell_order_info['presell_pay'] + $presell_order_info['platform_money'];
			$presell_order_info['presell_pay'] = round(($total_money - $order_info['shipping_money']) * round(($presell_order_info['presell_pay'] / $total_money), 2), 2);
			$presell_order_info['platform_money'] = $total_money - $presell_order_info['presell_pay'] - $order_info['shipping_money'];
		} else {
			$presell_order_info['platform_money'] -= $order_info['shipping_money'];
		}
		
		return $presell_order_info['platform_money'];
	}
	
	/**
	 * 获取订单项可退运费
	 * @param unknown $order_id
	 * @param unknown $order_goods_id
	 */
	public function getRefundFreight($order_id, $order_goods_id)
	{
		$ns_order = new NsOrderModel();
		$ns_presell_order = new NsOrderPresellModel();
		$ns_order_goods = new NsOrderGoodsModel();
		
		$order_info = $ns_order->getInfo([ "order_id" => $order_id ], "pay_money,user_platform_money,shipping_money,pay_status");
		if (empty($order_info) || empty($order_info['shipping_money'])) return 0.00;
		
		// 查询该订单是否有订单项已经发货 有发货则不退运费
		$shipped_num = $ns_order_goods->getCount([ "order_id" => $order_id, "shipping_status" => 1 ]);
		if ($shipped_num > 0) return 0.00;
		
		$presell_order_info = $ns_presell_order->getInfo([ "relate_id" => $order_id ], "is_full_payment,presell_pay,platform_money");
		if ($presell_order_info['is_full_payment'] == 0 && $order_info['pay_status'] > 0) {
			$presell_order_info['presell_pay'] += $order_info['pay_money'];
			$presell_order_info['platform_money'] += $order_info['user_platform_money'];
		}
		
		if ($presell_order_info['presell_pay'] > 0 || $presell_order_info['platform_money'] > 0) {
			return $order_info['shipping_money'];
		}
	}
	
	/**
	 * 获取预售订单交易号
	 * @param unknown $presell_order_id
	 */
	public function getPresellOrderOutTradeNo($presell_order_id){
	    $presell_order_model = new NsOrderPresellModel();
	    $order_info = $presell_order_model->getInfo(['presell_order_id' => $presell_order_id, 'order_status' => 0], 'out_trade_no');
	    if(!empty($order_info['out_trade_no'])){
	        return $order_info['out_trade_no'];
	    }
	}
}