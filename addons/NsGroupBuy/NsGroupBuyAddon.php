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
namespace addons\NsGroupBuy;

use addons\NsGroupBuy\data\service\GroupBuy;

class NsGroupBuyAddon extends \addons\Addons
{
	
	public $info = array(
		'name' => 'NsGroupBuy', // 插件名称标识
		'title' => '团购', // 插件中文名
		'description' => '该插件支持团购功能', // 插件概述
		'status' => 1, // 状态 1启用 0禁用
		'author' => 'niushop', // 作者
		'version' => '1.0', // 版本号
		'has_addonslist' => 0, // 是否有下级插件 例如：第三方登录插件下有 qq登录，微信登录
		'content' => '', // 插件的详细介绍或使用方法
		'ico' => 'addons/NsGroupBuy/ico.png'
	);
	
	
	// 钩子名称（需要该钩子调用的页面）
	
	/**
	 * 计算订单数据结构
	 * @param unknown $data
	 */
	public function getOrderGoodsSkuArray($data)
	{
		if ($data["promotion_type"] == 2) {
			$promotion_service = new GroupBuy();
			$order_data = $promotion_service->getOrderGoodsSkuArray($data);
			return $order_data;
		}
	}
	
	/**
	 * 营销活动
	 * @param unknown $param
	 */
	public function getPromotionType($param = [])
	{
		return array(
			"id" => 2,
			"name" => "团购"
		);
	}
	
	/**
	 * 得到营销活动
	 * @param unknown $param
	 * @return number[]|string[]
	 */
	public function getPromotionTypeInfo($param)
	{
		if ($param["promotion_type"] == 2) {
			return array(
				"id" => 2,
				"name" => "团购"
			);
		}
	}
	
	/**
	 * 营销活动详情
	 * @param $param
	 * @return array
	 */
	public function getPromotionDetail($param)
	{
		if (empty($param['goods_id']) || $param['promotion_type'] != $this->info['name']) {
			return [];
		}
		$bargain = new GroupBuy();
		$data = $bargain->getGoodsFirstPromotionGroupBuy($param['goods_id']);
		if (!empty($data)) {
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