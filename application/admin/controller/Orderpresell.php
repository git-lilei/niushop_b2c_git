<?php
/**
 * tuangou.php
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

namespace app\admin\controller;

use data\service\Express;
use data\service\OrderQuery;
use think\Cache;
use data\service\OrderAction;

/**
 * 预售
 */
class Orderpresell extends BaseController
{
	/**
	 * 预售列表
	 */
	public function orderPresellList()
	{
		// 获取物流公司
		$express = new Express();
		$expressList = $express->expressCompanyQuery();
		$this->assign('expressList', $expressList);
		
		$action = Cache::get("orderAction");
		if (empty($action)) {
			$action = array(
				"orderAction" => $this->fetch($this->style . "Order/orderAction"),
				"orderPrintAction" => $this->fetch($this->style . "Order/orderPrintAction"),
				"orderRefundAction" => $this->fetch($this->style . "Order/orderRefundAction")
			);
			Cache::set("orderAction", $action);
		}
		
		if (request()->isAjax()) {
			$condition = array();
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$start_date = request()->post('start_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('start_date'));
			$end_date = request()->post('end_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('end_date'));
			$user_name = request()->post('user_name', '');
			$order_no = request()->post('order_no', '');
			$order_status = request()->post('order_status', '');
			$receiver_mobile = request()->post('receiver_mobile', '');
			$payment_type = request()->post('payment_type', 1);
			
			$shipping_type = request()->post('shipping_type', 0); //配送类型
			$condition['order_type'] = 6; // 订单类型
			$condition['is_deleted'] = 0; // 未删除订单
			
			if ($start_date != 0 && $end_date != 0) {
				$condition["create_time"] = [
					[
						">",
						$start_date
					],
					[
						"<",
						$end_date
					]
				];
			} elseif ($start_date != 0 && $end_date == 0) {
				$condition["create_time"] = [
					[
						">",
						$start_date
					]
				];
			} elseif ($start_date == 0 && $end_date != 0) {
				$condition["create_time"] = [
					[
						"<",
						$end_date
					]
				];
			}
			if ($order_status != '') {
				// $order_status 1 待发货
				if ($order_status == 1) {
					// 订单状态为待发货实际为已经支付未完成还未发货的订单
					$condition['shipping_status'] = 0; // 0 待发货
					$condition['pay_status'] = 2; // 2 已支付
					$condition['order_status'] = array(
						'neq',
						4
					); // 4 已完成
					$condition['order_status'] = array(
						'neq',
						5
					); // 5 关闭订单
				} else if ($order_status == 0) {
					$condition['order_status'] = array( 'in', '0,6,7' );
				} else {
					$condition['order_status'] = $order_status;
				}
			}
			if (!empty($payment_type)) {
				$condition['payment_type'] = $payment_type;
			}
			if (!empty($user_name)) {
				$condition['receiver_name'] = $user_name;
			}
			if (!empty($order_no)) {
				$condition['order_no'] = $order_no;
			}
			if (!empty($receiver_mobile)) {
				$condition['receiver_mobile'] = $receiver_mobile;
			}
			if ($shipping_type != 0) {
				$condition['shipping_type'] = $shipping_type;
			}
			$condition['shop_id'] = $this->instance_id;
			$order_presell = new \addons\NsPresell\data\service\Orderpresell();
			$list = $order_presell->getOrderList($page_index, $page_size, $condition, 'create_time desc');
			$list['action'] = $action;
			return $list;
		} else {
			
			$status = request()->get('status', '');
			$this->assign("status", $status);
			$order_query = new OrderQuery();
			$all_status = $order_query->getOrderStatus([ "order_type" => 6 ]);
			$child_menu_list = array();
			$child_menu_list[] = array(
				'url' => "orderpresell/orderPresellList",
				'menu_name' => '全部',
				"active" => $status == '' ? 1 : 0
			);
			foreach ($all_status as $k => $v) {
				// 针对发货与提货状态名称进行特殊修改
				
				if ($v['status_id'] >= 6) continue;
				$child_menu_list[] = array(
					'url' => "orderpresell/orderPresellList?status=" . $v['status_id'],
					'menu_name' => $v['status_name'],
					"active" => $status == $v['status_id'] ? 1 : 0
				);
			}
			$this->assign('child_menu_list', $child_menu_list);
			
			return view($this->style . "Orderpresell/orderPresellList");
		}
		
	}
	
	/**
	 * 预售线下支付
	 */
	public function presellOrderOffLinePay()
	{
		if (request()->isAjax()) {
			$presell_order_id = request()->post('presell_order_id', '');
			$order_query = new OrderQuery();
			$res = $order_query->presellOrderOffLinePay($presell_order_id);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 订单详情（预售商品只能有一条商品信息）
	 *
	 * @return Ambigous <\think\response\View, \think\response\$this, \think\response\View>
	 */
	public function orderDetail()
	{
		$order_id = request()->get('order_id', 0);
		if ($order_id == 0) {
			$this->error("没有获取到订单信息");
		}
		$order_query = new OrderQuery();
		$detail = $order_query->getOrderDetail($order_id);
		
		if (empty($detail)) {
			$this->error("没有获取到订单信息");
		}
		if (!empty($detail['operation'])) {
			$operation_array = $detail['operation'];
			foreach ($operation_array as $k => $v) {
				if ($v["no"] == 'logistics') {
					unset($operation_array[ $k ]);
				}
			}
			$detail['operation'] = $operation_array;
		}
		$this->assign("order", $detail);
		
		//根据当前订单id获取小于该订单id
		$condition['order_type'] = array(
			"in",
			"1,3"
		); // 订单类型
		$condition["is_deleted"] = 0;
		$condition['order_id'] = array(
			'lt',
			$order_id
		);
		$prev_order = $order_query->getOrderList(1, 1, $condition, 'order_id desc');
		$this->assign('prev_order', $prev_order['data']);
		//根据当前订单id获取大于该订单id
		$conditions['order_type'] = array(
			"in",
			"1,3"
		); // 订单类型
		$conditions["is_deleted"] = 0;
		$conditions['order_id'] = array(
			'gt',
			$order_id
		);
		$next_order = $order_query->getOrderList(1, 1, $conditions, 'order_id desc');
		$this->assign('next_order', $next_order['data']);
		
		$presell_order = $order_query->getOrderPresellInfo(0, [ 'relate_id' => $order_id ]);
		$this->assign('presell_order', $presell_order);
		return view($this->style . "Orderpresell/orderDetail");
	}
	
	/**
	 * 订单备货完成
	 */
	public function orderStockingComplete()
	{
		if (request()->isAjax()) {
			$order_id = request()->post('order_id', 0);
			$order_action = new OrderAction();
			$result = $order_action->setOrderStockingComplete($order_id);
			return AjaxReturn($result);
		}
	}
	
}