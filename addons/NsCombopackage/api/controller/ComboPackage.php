<?php

/**
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

namespace addons\NsCombopackage\api\controller;

use addons\NsCombopackage\data\service\Promotion;
use app\api\controller\BaseApi;
use data\service\Goods as GoodsService;

/**
 * 组合套餐控制器
 */
class ComboPackage extends BaseApi
{
	
	/**
	 * 组合套餐
	 */
	public function comboPackageGoodsQuery()
	{
		$title = '商品组合套餐列表';
		$combo_package = new Promotion();
		$goods_id = isset($this->params['goods_id']) ? $this->params['goods_id'] : 0;
		if (empty($goods_id)) {
			return $this->outMessage($title, null, -50, '无法获取商品信息');
		}
		$combo_package = $combo_package->getComboPackageGoodsArray($goods_id);
		if (empty($combo_package)) {
			return $this->outMessage($title, null, -10, '未获取到套餐信息');
		}
		
		return $this->outMessage($title, $combo_package);
	}
	
	/**
	 * 根据id查询组合套餐
	 */
	public function comboPackageById()
	{
		$promotion = new Promotion();
		$combo_id = isset($this->params['combo_id']) ? $this->params['combo_id'] : 0;
		$curr_id = isset($this->params['curr_id']) ? $this->params['curr_id'] : "";
		$combo_package = $promotion->getComboPackageGoodsById($combo_id, $curr_id);
		
		$data = [
			'combo_package' => $combo_package,
			'combo_id' => $combo_id
		];
		return $this->outMessage('选择优惠套餐', $data);
	}
	
	/**
	 * 弹出组合商品sku选择框
	 */
	public function comboPackageSelectSku()
	{
		$title = '组合商品规格';
		$goods = new GoodsService();
		$goods_id = isset($this->params['goods_id']) ? $this->params['goods_id'] : "";
		if (empty($goods_id)) {
			return $this->outMessage($title, '', -50, '无法获取商品信息');
		}
		$goods_detail = $goods->getGoodsDetail($goods_id);
		if (empty($goods_detail)) {
			return $this->outMessage($title, '', -10, '未获取到套餐信息');
		}
		return $this->outMessage($title, $goods_detail);
	}
	
}