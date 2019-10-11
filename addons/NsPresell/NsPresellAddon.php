<?php
// +----------------------------------------------------------------------
// | test [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.zzstudio.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Byron Sampson <xiaobo.sun@gzzstudio.net>
// +----------------------------------------------------------------------
namespace addons\NsPresell;

use addons\NsPresell\data\service\Orderpresell;
use addons\NsPresell\data\service\OrderCreate;
use addons\NsPresell\data\service\Order;

class NsPresellAddon extends \addons\Addons
{
	
	public $info = array(
		'name' => 'NsPresell', // 插件名称标识
		'title' => '预售插件', // 插件中文名
		'description' => '该系统支持商品预售', // 插件概述
		'status' => 1, // 状态 1启用 0禁用
		'author' => 'niushop', // 作者
		'version' => '1.0', // 版本号
		'has_addonslist' => 0, // 是否有下级插件 例如：第三方登录插件下有 qq登录，微信登录
		'content' => '', // 插件的详细介绍或使用方法
		'ico' => 'addons/NsPresell/ico.png'
	);
	
	// 钩子名称（需要该钩子调用的页面）
	
	/**
	 * 获取订单状态
	 * @param array $params
	 * @return array
	 */
	public function getOrderStatus($params = [])
	{
		if ($params['order_type'] == 6) {
			$order = new Order();
			$order_status = $order->getOrderStatus($params);
			return $order_status;
		}
	}
	
	/**
	 * 订单状态信息
	 * @param array $params
	 */
	public function getOrderStatusInfo($params = [])
	{
		if ($params['order_type'] == 6) {
			$order = new Order();
			$order_status = $order->getOrderStatusInfo($params);
			return $order_status;
		}
	}
	
	/**
	 * 获取订单类型
	 * @param array $params
	 * @return array
	 */
	public function getOrderType($params = [])
	{
		return [
			'id' => 6,
			'name' => '预售订单'
		];
	}
	
	/**
	 * 获取订单状态
	 * @param unknown $params
	 */
	public function getOrderTypeInfo($params)
	{
		if ($params["order_type"] == 6) {
			return [
				'id' => 6,
				'name' => '预售订单'
			];
		}
		
	}
	
	/**
	 * 订单创建
	 * @param unknown $data
	 * @return number|\addons\NsPintuan\data\service\Exception
	 */
	public function orderCreate($params)
	{
		
		if ($params['order_type'] == 6) {
			$order_create_service = new OrderCreate();
			$result = $order_create_service->orderCreate($params);
			return $result;
		}
	}
	
	/**
	 * 订单计算
	 * @param unknown $data
	 */
	public function orderCalculate($params)
	{
		if ($params["order_type"] == 6) {
			$order_create_service = new OrderCreate();
			$result = $order_create_service->orderCalculate($params);
			return $result;
		}
	}
	
	/**
	 * 订单创建成功
	 * @param unknown $params
	 */
	public function orderCreateSuccessAction($params)
	{
		if ($params["order_data"]["order_type"] == 6) {
			$order_create = new OrderCreate();
			$res = $order_create->orderCreateSuccessAction($params);
			return $res;
		}
	}
	
	/**
	 * 数据整理
	 * @param unknown $data
	 */
	public function dataCollation($params)
	{
		if ($params["order_type"] == 6) {
			$order_create_service = new OrderCreate();
			$result = $order_create_service->dataCollation($params);
			return $result;
		}
	}
	
	/**
	 * 查询可退金额
	 * @param unknown $param
	 * @return number
	 */
	public function getOrderGoodsRefundMoney($params)
	{
		if ($params["order_type"] == 6) {
			$orderpresell_service = new Orderpresell();
			$result = $orderpresell_service->getPresellOrderGoodsRefundMoney($params);
			return $result;
		}
	}
	
	/**
	 * 查询可退余额
	 * @param unknown $param
	 * @return number
	 */
	public function getOrderGoodsRefundBanlance($params)
	{
		if ($params["order_type"] == 6) {
			$orderpresell_service = new Orderpresell();
			$result = $orderpresell_service->getPresellOrderGoodsRefundBanlance($params);
			return $result;
		}
	}
	
	/**
	 * 获取预售订单退款金额
	 * @param unknown $params
	 */
	public function getRefundMoney($params){
	    if ($params["order_type"] == 6) {
	        $orderpresell_service = new Orderpresell();
	        $result = [
	            'refund_money' => $orderpresell_service->getRefundRealMoney($params['order_id'], $params['order_goods_id']),
	            'refund_balance' => $orderpresell_service->getRefundBalance($params['order_id'], $params['order_goods_id']),
	            'freight' => $orderpresell_service->getRefundFreight($params['order_id'], $params['order_goods_id'])
	        ];
            return $result;
	    }
	}
	
	/**
	 * 插件安装
	 * @see \addons\Addons::install()
	 */
	public function install()
	{
		return true;
	}
	
	/**
	 * 插件卸载
	 * @see \addons\Addons::uninstall()
	 */
	public function uninstall()
	{
		return true;
	}
}