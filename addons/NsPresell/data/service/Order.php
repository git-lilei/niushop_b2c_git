<?php
/**
 * OrderAccount.php
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

use data\service\BaseService;
use data\model\NsOrderModel;

/**
 * 订单操作类
 */
class Order extends BaseService
{
	
	public $order;
	public $status = array(
		6 => [
			'status_id' => '6',
			'status_name' => '订金待支付',
			'is_refund' => 0, // 是否可以申请退款
			'operation' => array(
				'0' => array(
					'no' => 'order_presell',
					'name' => '线下支付',
					'color' => '#FF9800'
				),
				'1' => array(
					'no' => 'close',
					'color' => '#E61D1D',
					'name' => '交易关闭'
				),
			
			),
			'member_operation' => array(
				'0' => array(
					'no' => 'pay_presell',
					'name' => '去支付',
					'color' => '#F15050',
					'class_name' => 'ns-bg-color'
				),
				
				'1' => array(
					'no' => 'close',
					'name' => '关闭订单',
					'color' => '#999999',
					'class_name' => 'ns-bg-color-gray-shade-20'
				)
			)
		],
		7 => [
			'status_id' => '7',
			'status_name' => '备货中',
			'is_refund' => 1, // 是否可以申请退款
			'operation' => array(
				'0' => array(
					'no' => 'stocking_complete',
					'name' => '备货完成',
					'color' => '#F15050'
				),
			),
			'member_operation' => array()
		],
		0 => [
			'status_id' => '0',
			'status_name' => '预售中',
			'is_refund' => 1, // 是否可以申请退款
			'operation' => array(
				'0' => array(
					'no' => 'pay',
					'name' => '线下支付',
					'color' => '#FF9800'
				),
				'1' => array(
					'no' => 'close',
					'color' => '#E61D1D',
					'name' => '交易关闭'
				),
				'2' => array(
					'no' => 'adjust_price',
					'color' => '#4CAF50',
					'name' => '修改价格'
				),
				'3' => array(
					'no' => 'seller_memo',
					'color' => '#666666',
					'name' => '备注'
				)
			),
			'member_operation' => array(
				'0' => array(
					'no' => 'pay',
					'name' => '去支付',
					'color' => '#F15050',
					'class_name' => 'ns-bg-color'
				),
				
				'1' => array(
					'no' => 'close',
					'name' => '关闭订单',
					'color' => '#999999',
					'class_name' => 'ns-bg-color-gray-shade-20'
				)
			)
		],
		1 => [
			'status_id' => '1',
			'status_name' => '待发货',
			'is_refund' => 1,
			'operation' => array(
				'0' => array(
					'no' => 'delivery',
					'color' => 'green',
					'name' => '发货'
				),
				'1' => array(
					'no' => 'seller_memo',
					'color' => '#666666',
					'name' => '备注'
				),
				'2' => array(
					'no' => 'update_address',
					'color' => '#51A351',
					'name' => '修改地址'
				)
			),
			'member_operation' => array()
		],
		2 => [
			'status_id' => '2',
			'status_name' => '已发货',
			'is_refund' => 1,
			'operation' => array(
				'0' => array(
					'no' => 'seller_memo',
					'color' => '#666666',
					'name' => '备注'
				),
				'1' => array(
					'no' => 'logistics',
					'color' => '#666666',
					'name' => '查看物流'
				),
				'2' => array(
					'no' => 'getdelivery',
					'name' => '确认收货',
					'color' => '#FF6600'
				)
			),
			
			'member_operation' => array(
				'0' => array(
					'no' => 'getdelivery',
					'name' => '确认收货',
					'color' => '#FF6600',
					'class_name' => 'ns-bg-color'
				),
				'1' => array(
					'no' => 'logistics',
					'color' => '#cccccc',
					'name' => '查看物流',
					'class_name' => 'ns-bg-color-gray-shade-20'
				)
			)
		],
		3 => [
			'status_id' => '3',
			'status_name' => '已收货',
			'is_refund' => 0,
			'operation' => array(
				'0' => array(
					'no' => 'seller_memo',
					'color' => '#666666',
					'name' => '备注'
				),
				'1' => array(
					'no' => 'logistics',
					'color' => '#666666',
					'name' => '查看物流'
				)
			),
			'member_operation' => array(
				'0' => array(
					'no' => 'logistics',
					'color' => '#cccccc',
					'name' => '查看物流',
					'class_name' => 'ns-bg-color-gray-shade-20'
				)
			)
		],
		4 => [
			'status_id' => '4',
			'status_name' => '已完成',
			'is_refund' => 0,
			'operation' => array(
				'0' => array(
					'no' => 'seller_memo',
					'color' => '#666666',
					'name' => '备注'
				),
				'1' => array(
					'no' => 'logistics',
					'color' => '#666666',
					'name' => '查看物流'
				)
			),
			'member_operation' => array(
				'0' => array(
					'no' => 'logistics',
					'color' => '#cccccc',
					'name' => '查看物流',
					'class_name' => 'ns-bg-color-gray-shade-20'
				)
			)
		],
		5 => [
			'status_id' => '5',
			'status_name' => '已关闭',
			'is_refund' => 0,
			'operation' => array(
				'0' => array(
					'no' => 'seller_memo',
					'color' => '#666666',
					'name' => '备注'
				),
				'1' => array(
					'no' => 'delete_order',
					'color' => '#ff0000',
					'name' => '删除订单'
				)
			),
			'member_operation' => array(
				'0' => array(
					'no' => 'delete_order',
					'color' => '#ff0000',
					'name' => '删除订单',
					'class_name' => 'ns-bg-color-gray-shade-20'
				)
			)
		],
		-1 => [
			'status_id' => '-1',
			'status_name' => '退款中',
			'is_refund' => 1,
			'operation' => array(
				'0' => array(
					'no' => 'seller_memo',
					'color' => '#666666',
					'name' => '备注'
				)
			),
			'member_operation' => array()
		]
	);
	
	// 订单主表
	function __construct()
	{
		parent::__construct();
		$this->order = new NsOrderModel();
	}
	
	/**
	 * 得到订单状态
	 * @param unknown $param
	 */
	public function getOrderStatus($param)
	{
		return $this->status;
	}
	
	/**
	 * 订单状态
	 * @param unknown $param
	 */
	public function getOrderStatusInfo($param)
	{
		return $this->status[ $param["order_status"] ];
	}
	
	
}