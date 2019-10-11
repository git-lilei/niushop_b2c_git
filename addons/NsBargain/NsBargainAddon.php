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
namespace addons\NsBargain;

use addons\NsBargain\data\service\Bargain;

class NsBargainAddon extends \addons\Addons
{
	
	public $info = array(
		'name' => 'NsBargain', // 插件名称标识
		'title' => '砍价', // 插件中文名
		'description' => '该插件支持砍价功能', // 插件概述
		'status' => 1, // 状态 1启用 0禁用
		'author' => 'niushop', // 作者
		'version' => '1.0', // 版本号
		'has_addonslist' => 0, // 是否有下级插件 例如：第三方登录插件下有 qq登录，微信登录
		'content' => '', // 插件的详细介绍或使用方法
		'ico' => 'addons/NsBargain/ico.png'
	);
	
	/**
	 * 计算订单数据结构
	 * @param array $data
	 */
	public function getOrderPromotionArray($data)
	{
		if ($data["promotion_type"] == 3) {
			$promotion_service = new Bargain();
			$order_data = $promotion_service->getOrderPromotionArray($data);
			return $order_data;
		}
	}
	
	/**
	 * 营销活动
	 * @param array $param
	 */
	public function getPromotionType($param = [])
	{
		return array(
			"id" => 3,
			"name" => "砍价"
		);
	}
	
	/**
	 * 得到营销活动
	 * @param array $param
	 * @return number[]|string[]
	 */
	public function getPromotionTypeInfo($param)
	{
		if ($param["promotion_type"] == 3) {
			return array(
				"id" => 3,
				"name" => "砍价"
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
		$bargain = new Bargain();
		if ((empty($param['bargain_id']) && empty($param['goods_id'])) || $param['promotion_type'] != $this->info['name']) {
			return [];
		}
		$data = $bargain->getBargainGoodsInfo($param['bargain_id'], $param['goods_id']);
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