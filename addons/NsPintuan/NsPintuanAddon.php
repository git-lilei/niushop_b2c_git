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
namespace addons\NsPintuan;

use addons\NsPintuan\data\service\Pintuan;
use addons\NsPintuan\data\service\OrderCreate;
use addons\NsPintuan\data\service\OrderAction;
use addons\NsPintuan\data\service\Order;

class NsPintuanAddon extends \addons\Addons
{
	
	public $info = array(
		'name' => 'NsPintuan', // 插件名称标识
		'title' => '拼团', // 插件中文名
		'description' => '该插件 支持拼团功能', // 插件概述
		'status' => 1, // 状态 1启用 0禁用
		'author' => 'niushop', // 作者
		'version' => '1.0', // 版本号
		'has_addonslist' => 0, // 是否有下级插件 例如：第三方登录插件下有 qq登录，微信登录
		'content' => '', // 插件的详细介绍或使用方法
		'ico' => 'addons/NsPintuan/ico.png'
	);
	
	// 钩子名称（需要该钩子调用的页面）
	
	/**
	 * 获取订单状态
	 * @param array $params
	 * @return array
	 */
	public function getOrderStatus($params = [])
	{
		if ($params['order_type'] == 4) {
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
		if ($params['order_type'] == 4) {
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
			'id' => 4,
			'name' => '拼团订单'
		];
	}
	
	/**
	 * 获取订单状态
	 * @param unknown $params
	 */
	public function getOrderTypeInfo($params)
	{
		if ($params["order_type"] == 4) {
			return [
				'id' => 4,
				'name' => '拼团订单'
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
		
		if ($params['order_type'] == 4) {
			$order_create_service = new OrderCreate();
			$result = $order_create_service->orderCreate($params);
			return $result;
		}
	}
	
	/**
	 * 订单支付核验
	 * @param unknown $data
	 */
	public function orderPayVerify($params)
	{
		if ($params['order_type'] == 4) {
			
			$order_action = new OrderAction();
			$result = $order_action->orderPayVerify($params);
			return $result;
		}
	}
	
	/**
	 * 订单创建成功
	 * @param unknown $params
	 */
	public function orderCreateSuccessAction($params)
	{
		if ($params["order_data"]["order_type"] == 4) {
			$order_create = new OrderCreate();
			$res = $order_create->orderCreateSuccessAction($params);
			return $res;
		}
	}
	
	/**
	 * 订单支付成功
	 * @param unknown $data
	 */
	public function orderPaySuccessAction($params)
	{
		//判断订单类型
		if ($params["order_type"] == 4) {
			$order_action = new OrderAction();
			$result = $order_action->orderPaySuccess($params);
			return $result;
		}
	}
	
	/**
	 * 订单计算
	 * @param unknown $data
	 */
	public function orderCalculate($params)
	{
		if ($params["order_type"] == 4) {
			$order_create_service = new OrderCreate();
			$result = $order_create_service->orderCalculate($params);
			return $result;
		}
	}
	
	/**
	 * 数据整理
	 * @param unknown $data
	 */
	public function dataCollation($params)
	{
		if ($params["order_type"] == 4) {
			$order_create_service = new OrderCreate();
			$result = $order_create_service->dataCollation($params);
			return $result;
		}
	}
	
	/**
	 * 营销活动详情
	 * @param $param
	 * @return array
	 */
	public function getPromotionDetail($param)
	{
		$promotion = new Pintuan();
		if (empty($param['goods_id']) || $param['promotion_type'] != $this->info['name']) {
			return [];
		}
		$data = $promotion->getGoodsPintuanDetail($param['goods_id']);
		
		if (!empty($data)) {
			$data['tuangou_content_json'] = json_decode($data['tuangou_content_json'], true);
			$data['tuangou_group_count'] = 0;
			if (!empty($param['group_id'])) {
				$data['tuangou_group_count'] = $promotion->getTuangouGroupCount($param['group_id'], $param['goods_id']);
			}
			return array(
				"promotion_type" => $this->info['name'],
				"promotion_name" => $this->info['title'],
				'data' => $data
			);
		} else {
			return [];
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